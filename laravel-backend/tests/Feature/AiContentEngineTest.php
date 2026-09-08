<?php

namespace Tests\Feature;

use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\Job;
use App\Models\News;
use App\Models\AiUsageLog;
use App\Services\AI\ContentEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiContentEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_news_is_saved_from_structured_response(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates'=>[['content'=>['parts'=>[['text'=>json_encode(['results'=>[[
                'id'=>1,'mobile'=>['summary'=>'Short summary'],'website'=>['title'=>'Generated title','content'=>'<p>Detailed article</p>'],'seo'=>['title'=>'SEO title','description'=>'SEO description','focus_keyword'=>'news','keywords'=>['news'],'slug'=>'news','og_title'=>'OG','og_description'=>'OG','image_alt'=>'Image']
            ]])]]]],
            'usageMetadata'=>['promptTokenCount'=>100,'candidatesTokenCount'=>200],
        ], 200)]);

        $news=News::create(['title'=>'Source title','summary'=>'Source summary','content'=>'Source content','status'=>'draft']);
        AiProviderSetting::create(['provider'=>'gemini','api_key'=>'primary-secret','model'=>'gemini-2.5-flash','enabled'=>true,'daily_request_limit'=>10,'daily_token_limit'=>10000,'max_items_per_request'=>20]);

        $saved=app(ContentEngine::class)->process([app(ContentEngine::class)->buildNews($news)]);

        $this->assertCount(1,$saved);
        $this->assertDatabaseHas('ai_contents',['source_type'=>'news','source_id'=>$news->id,'status'=>'generated','input_tokens'=>100,'output_tokens'=>200]);
        $this->assertSame('Generated title',$news->fresh()->title);
    }

    public function test_primary_failure_uses_fallback_provider(): void
    {
        Http::fakeSequence()->pushStatus(503)->push([
            'choices'=>[['message'=>['content'=>json_encode(['results'=>[[
                'id'=>1,'mobile'=>['summary'=>'Fallback summary'],'website'=>['title'=>'Fallback title','content'=>'<p>Fallback article</p>'],'seo'=>['title'=>'SEO','description'=>'Description','focus_keyword'=>'fallback','keywords'=>['fallback'],'slug'=>'fallback','og_title'=>'OG','og_description'=>'OG','image_alt'=>'Image']
            ]]])]],
            'usage'=>['prompt_tokens'=>50,'completion_tokens'=>100],
        ],200);

        $news=News::create(['title'=>'Source title','status'=>'draft']);
        AiProviderSetting::create(['provider'=>'gemini','api_key'=>'primary-secret','model'=>'gemini-2.5-flash','fallback_provider'=>'groq','fallback_api_key'=>'fallback-secret','fallback_model'=>'llama-3.3-70b-versatile','enabled'=>true,'daily_request_limit'=>10,'daily_token_limit'=>10000,'max_items_per_request'=>20]);

        $saved=app(ContentEngine::class)->process([app(ContentEngine::class)->buildNews($news)]);

        $this->assertCount(1,$saved);
        $this->assertSame('Fallback title',$news->fresh()->title);
        $this->assertCount(2,AiUsageLog::all());
        $this->assertTrue(AiUsageLog::where('success',false)->exists());
    }

    public function test_malformed_item_does_not_block_valid_items(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates'=>[['content'=>['parts'=>[['text'=>json_encode(['results'=>[
                ['id'=>1,'mobile'=>['summary'=>'Good'],'website'=>['title'=>'Good','content'=>'<p>Good</p>'],'seo'=>['title'=>'SEO','description'=>'Description']],
                ['id'=>2,'mobile'=>[],'website'=>[],'seo'=>[]]
            ]])]]]],
        ],200)]);

        $one=News::create(['title'=>'One','status'=>'draft']);
        $two=News::create(['title'=>'Two','status'=>'draft']);
        AiProviderSetting::create(['provider'=>'gemini','api_key'=>'primary-secret','enabled'=>true,'daily_request_limit'=>10,'daily_token_limit'=>10000,'max_items_per_request'=>20]);
        $engine=app(ContentEngine::class);

        $saved=$engine->process([$engine->buildNews($one),$engine->buildNews($two)]);

        $this->assertCount(1,$saved);
        $this->assertDatabaseHas('ai_contents',['source_id'=>$one->id,'status'=>'generated']);
        $this->assertDatabaseHas('ai_contents',['source_id'=>$two->id,'status'=>'failed']);
    }

    public function test_job_generation_payload_contains_facts_for_prompt_but_engine_does_not_update_them(): void
    {
        $job=new Job(['title'=>'Recruitment','vacancies'=>25,'salary'=>'₹30,000','eligibility'=>'Graduate','official_notification_url'=>'https://example.com/notice']);
        $payload=app(ContentEngine::class)->buildJob($job);
        $this->assertSame(25,$payload['source']['vacancies']);
        $this->assertSame('₹30,000',$payload['source']['salary']);
        $this->assertSame('Graduate',$payload['source']['eligibility']);
        $this->assertSame('https://example.com/notice',$payload['source']['official_notification_url']);
    }
}
