<?php

namespace Tests\Feature;

use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\AiUsageLog;
use App\Models\Job;
use App\Models\News;
use App\Services\AI\ContentEngine;
use App\Services\NewsIngestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiContentEngineTest extends TestCase
{
    use RefreshDatabase;

    private function generatedResult(int $id, string $title = 'Generated title'): array
    {
        return [
            'id' => $id,
            'mobile' => ['summary' => 'Short summary'],
            'website' => ['title' => $title, 'content' => '<p>Detailed article</p>'],
            'seo' => [
                'title' => 'SEO title', 'description' => 'SEO description',
                'focus_keyword' => 'news', 'keywords' => ['news'], 'slug' => 'news',
                'og_title' => 'OG', 'og_description' => 'OG', 'image_alt' => 'Image',
            ],
        ];
    }

    private function provider(array $overrides = []): AiProviderSetting
    {
        return AiProviderSetting::create(array_merge([
            'provider' => 'gemini', 'api_key' => 'primary-secret', 'model' => 'gemini-2.5-flash',
            'enabled' => true, 'daily_request_limit' => 100, 'daily_token_limit' => 100000,
            'max_items_per_request' => 20,
        ], $overrides));
    }

    public function test_generated_news_is_saved_from_structured_response(): void
    {
        $body = json_encode(['results' => [$this->generatedResult(1)]]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => $body]]]]],
            'usageMetadata' => ['promptTokenCount' => 100, 'candidatesTokenCount' => 200],
        ], 200)]);

        $news = News::create(['title' => 'Source title', 'summary' => 'Source summary', 'content' => 'Source content', 'status' => 'draft']);
        $this->provider();

        $engine = app(ContentEngine::class);
        $saved = $engine->process([$engine->buildNews($news)]);

        $this->assertCount(1, $saved);
        $this->assertDatabaseHas('ai_contents', ['source_type' => 'news', 'source_id' => $news->id, 'status' => 'generated', 'input_tokens' => 100, 'output_tokens' => 200]);
        $this->assertSame('Generated title', $news->fresh()->title);
    }

    public function test_primary_failure_uses_fallback_provider(): void
    {
        $body = json_encode(['results' => [$this->generatedResult(1, 'Fallback title')]]);
        Http::fakeSequence()->pushStatus(503)->push([
            'choices' => [['message' => ['content' => $body]]],
            'usage' => ['prompt_tokens' => 50, 'completion_tokens' => 100],
        ], 200);

        $news = News::create(['title' => 'Source title', 'status' => 'draft']);
        $this->provider(['fallback_provider' => 'groq', 'fallback_api_key' => 'fallback-secret', 'fallback_model' => 'llama-3.3-70b-versatile']);

        $engine = app(ContentEngine::class);
        $saved = $engine->process([$engine->buildNews($news)]);

        $this->assertCount(1, $saved);
        $this->assertSame('Fallback title', $news->fresh()->title);
        $this->assertCount(2, AiUsageLog::all());
        $this->assertTrue(AiUsageLog::where('success', false)->exists());
        $this->assertTrue(AiUsageLog::where('success', true)->where('provider', 'groq')->exists());
    }

    public function test_malformed_item_does_not_block_valid_items(): void
    {
        $body = json_encode(['results' => [$this->generatedResult(1, 'Good'), ['id' => 2, 'mobile' => [], 'website' => [], 'seo' => []]]]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => $body]]]]],
        ], 200)]);

        $one = News::create(['title' => 'One', 'status' => 'draft']);
        $two = News::create(['title' => 'Two', 'status' => 'draft']);
        $this->provider();

        $engine = app(ContentEngine::class);
        $saved = $engine->process([$engine->buildNews($one), $engine->buildNews($two)]);

        $this->assertCount(1, $saved);
        $this->assertDatabaseHas('ai_contents', ['source_id' => $one->id, 'status' => 'generated']);
        $this->assertDatabaseHas('ai_contents', ['source_id' => $two->id, 'status' => 'failed']);
    }

    public function test_job_generation_payload_contains_facts_for_prompt_but_engine_does_not_update_them(): void
    {
        $job = new Job(['title' => 'Recruitment', 'vacancies' => 25, 'salary' => '₹30,000', 'eligibility' => 'Graduate', 'official_notification_url' => 'https://example.com/notice']);
        $payload = app(ContentEngine::class)->buildJob($job);
        $this->assertSame(25, $payload['source']['vacancies']);
        $this->assertSame('₹30,000', $payload['source']['salary']);
        $this->assertSame('Graduate', $payload['source']['eligibility']);
        $this->assertSame('https://example.com/notice', $payload['source']['official_notification_url']);
    }

    public function test_news_ingestion_uses_both_providers_deduplicates_and_sends_created_items_to_ai(): void
    {
        config()->set('services.newsdata.key', 'newsdata-secret');
        config()->set('services.newsapi.key', 'newsapi-secret');
        $this->provider(['max_items_per_request' => 20]);

        $aiBody = json_encode(['results' => [$this->generatedResult(1, 'AI News One'), $this->generatedResult(2, 'AI News Two')]]);
        Http::fake([
            'newsdata.io/*' => Http::response(['results' => [
                ['title' => 'CGPSC Exam Update', 'description' => 'First', 'source_id' => 'NewsData', 'link' => 'https://example.com/one', 'pubDate' => '2026-09-08 10:00:00'],
                ['title' => 'Shared Recruitment Update', 'description' => 'Shared', 'source_id' => 'NewsData', 'link' => 'https://example.com/shared'],
            ]], 200),
            'newsapi.org/*' => Http::response(['articles' => [
                ['title' => 'Shared Recruitment Update', 'description' => 'Duplicate', 'source' => ['name' => 'NewsAPI'], 'url' => 'https://example.com/shared'],
                ['title' => 'New Education Update', 'description' => 'Second', 'source' => ['name' => 'NewsAPI'], 'url' => 'https://example.com/two'],
            ]], 200),
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => $aiBody]]]]],
                'usageMetadata' => ['promptTokenCount' => 300, 'candidatesTokenCount' => 500],
            ], 200),
        ]);

        $result = app(NewsIngestionService::class)->ingestAndProcess('CG recruitment', 3, true);

        $this->assertSame(3, $result['fetched']);
        $this->assertSame(3, $result['created']);
        $this->assertSame(2, $result['processed']);
        $this->assertSame(3, News::count());
        $this->assertSame(1, News::where('original_url', 'https://example.com/shared')->count());
        $this->assertDatabaseHas('ai_contents', ['source_type' => 'news', 'source_id' => 1, 'status' => 'generated']);
        $this->assertDatabaseHas('ai_contents', ['source_type' => 'news', 'source_id' => 2, 'status' => 'generated']);
    }

    public function test_ingestion_does_not_create_existing_url_or_case_insensitive_title_duplicates(): void
    {
        config()->set('services.newsdata.key', 'newsdata-secret');
        News::create(['title' => 'Existing CGPSC Update', 'original_url' => 'https://example.com/existing', 'status' => 'draft']);

        Http::fake(['newsdata.io/*' => Http::response(['results' => [
            ['title' => 'Existing CGPSC Update', 'link' => 'https://other.example/title'],
            ['title' => 'Completely New Story', 'link' => 'https://example.com/new'],
            ['title' => 'Another New Story', 'link' => 'https://example.com/existing'],
        ]], 200)]);

        $result = app(NewsIngestionService::class)->ingestAndProcess(null, 10, false);

        $this->assertSame(3, $result['fetched']);
        $this->assertSame(1, $result['created']);
        $this->assertDatabaseCount('news', 2);
        $this->assertDatabaseHas('news', ['original_url' => 'https://example.com/new']);
    }

    public function test_news_auto_publish_can_be_enabled(): void
    {
        $this->provider(['auto_publish_news' => true]);
        $news = News::create(['title' => 'Source title', 'status' => 'draft']);
        $body = json_encode(['results' => [$this->generatedResult(1)]]);
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => $body]]]]]], 200)]);

        app(ContentEngine::class)->process([app(ContentEngine::class)->buildNews($news)]);

        $this->assertSame('published', $news->fresh()->status);
        $this->assertNotNull($news->fresh()->published_at);
    }
}
