<?php

namespace Tests\Feature;

use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\AiUsageLog;
use App\Models\Job;
use App\Models\News;
use App\Services\AI\ContentEngine;
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
            'website' => [
                'title' => $title,
                'content' => '<p>Detailed article</p>',
            ],
            'seo' => [
                'title' => 'SEO title',
                'description' => 'SEO description',
                'focus_keyword' => 'news',
                'keywords' => ['news'],
                'slug' => 'news',
                'og_title' => 'OG',
                'og_description' => 'OG',
                'image_alt' => 'Image',
            ],
        ];
    }

    public function test_generated_news_is_saved_from_structured_response(): void
    {
        $result = $this->generatedResult(1);
        $body = json_encode(['results' => [$result]]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [['text' => $body]],
                        ],
                    ],
                ],
                'usageMetadata' => [
                    'promptTokenCount' => 100,
                    'candidatesTokenCount' => 200,
                ],
            ], 200),
        ]);

        $news = News::create([
            'title' => 'Source title',
            'summary' => 'Source summary',
            'content' => 'Source content',
            'status' => 'draft',
        ]);

        AiProviderSetting::create([
            'provider' => 'gemini',
            'api_key' => 'primary-secret',
            'model' => 'gemini-2.5-flash',
            'enabled' => true,
            'daily_request_limit' => 10,
            'daily_token_limit' => 10000,
            'max_items_per_request' => 20,
        ]);

        $engine = app(ContentEngine::class);
        $saved = $engine->process([$engine->buildNews($news)]);

        $this->assertCount(1, $saved);
        $this->assertDatabaseHas('ai_contents', [
            'source_type' => 'news',
            'source_id' => $news->id,
            'status' => 'generated',
            'input_tokens' => 100,
            'output_tokens' => 200,
        ]);
        $this->assertSame('Generated title', $news->fresh()->title);
    }

    public function test_primary_failure_uses_fallback_provider(): void
    {
        $result = $this->generatedResult(1, 'Fallback title');
        $body = json_encode(['results' => [$result]]);

        Http::fakeSequence()
            ->pushStatus(503)
            ->push([
                'choices' => [
                    [
                        'message' => ['content' => $body],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 50,
                    'completion_tokens' => 100,
                ],
            ], 200);

        $news = News::create([
            'title' => 'Source title',
            'status' => 'draft',
        ]);

        AiProviderSetting::create([
            'provider' => 'gemini',
            'api_key' => 'primary-secret',
            'model' => 'gemini-2.5-flash',
            'fallback_provider' => 'groq',
            'fallback_api_key' => 'fallback-secret',
            'fallback_model' => 'llama-3.3-70b-versatile',
            'enabled' => true,
            'daily_request_limit' => 10,
            'daily_token_limit' => 10000,
            'max_items_per_request' => 20,
        ]);

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
        $valid = $this->generatedResult(1, 'Good');
        $invalid = [
            'id' => 2,
            'mobile' => [],
            'website' => [],
            'seo' => [],
        ];
        $body = json_encode(['results' => [$valid, $invalid]]);

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [['text' => $body]],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $one = News::create(['title' => 'One', 'status' => 'draft']);
        $two = News::create(['title' => 'Two', 'status' => 'draft']);

        AiProviderSetting::create([
            'provider' => 'gemini',
            'api_key' => 'primary-secret',
            'enabled' => true,
            'daily_request_limit' => 10,
            'daily_token_limit' => 10000,
            'max_items_per_request' => 20,
        ]);

        $engine = app(ContentEngine::class);
        $saved = $engine->process([
            $engine->buildNews($one),
            $engine->buildNews($two),
        ]);

        $this->assertCount(1, $saved);
        $this->assertDatabaseHas('ai_contents', [
            'source_id' => $one->id,
            'status' => 'generated',
        ]);
        $this->assertDatabaseHas('ai_contents', [
            'source_id' => $two->id,
            'status' => 'failed',
        ]);
    }

    public function test_job_generation_payload_contains_facts_for_prompt_but_engine_does_not_update_them(): void
    {
        $job = new Job([
            'title' => 'Recruitment',
            'vacancies' => 25,
            'salary' => '₹30,000',
            'eligibility' => 'Graduate',
            'official_notification_url' => 'https://example.com/notice',
        ]);

        $payload = app(ContentEngine::class)->buildJob($job);

        $this->assertSame(25, $payload['source']['vacancies']);
        $this->assertSame('₹30,000', $payload['source']['salary']);
        $this->assertSame('Graduate', $payload['source']['eligibility']);
        $this->assertSame('https://example.com/notice', $payload['source']['official_notification_url']);
    }
}
