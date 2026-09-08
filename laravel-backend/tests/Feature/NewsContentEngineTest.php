<?php

namespace Tests\Feature;

use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\News;
use App\Services\AI\NewsContentEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NewsContentEngineTest extends TestCase
{
    use RefreshDatabase;

    private function generatedPayload(int $id): array
    {
        return ['id'=>$id,'mobile'=>['summary'=>'Short mobile summary','summary_en'=>'Short mobile summary'],'website'=>['title'=>'AI News Title','title_en'=>'AI News Title','content'=>'<p>Detailed article.</p>','content_en'=>'<p>Detailed article.</p>'],'seo'=>['title'=>'SEO title','description'=>'SEO description','focus_keyword'=>'news','keywords'=>['news'],'slug'=>'ai-news-title','og_title'=>'OG','og_description'=>'OG','image_alt'=>'News image']];
    }

    private function provider(array $overrides=[]): AiProviderSetting
    {
        return AiProviderSetting::create(array_merge(['provider'=>'gemini','api_key'=>'primary-secret','model'=>'gemini-3.8-flash','enabled'=>true,'daily_request_limit'=>100,'daily_token_limit'=>100000,'max_items_per_request'=>10],$overrides));
    }

    public function test_gemini_3_8_uses_current_json_request_and_saves_news(): void
    {
        $news=News::create(['title'=>'Source title','summary'=>'Source summary','content'=>'Source content','status'=>'draft']);$this->provider();$body=json_encode(['results'=>[$this->generatedPayload($news->id)]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        Http::fake(['generativelanguage.googleapis.com/*'=>function($request)use($body){$this->assertSame('primary-secret',$request->header('x-goog-api-key')[0]??null);$this->assertStringContainsString('gemini-3.8-flash',$request->url());$config=$request->data()['generationConfig']??[];$this->assertSame('application/json',$config['responseFormat']['text']['mimeType']??null);$this->assertSame('low',$config['thinkingConfig']['thinkingLevel']??null);$this->assertArrayNotHasKey('schema',$config['responseFormat']['text']??[]);$this->assertArrayNotHasKey('temperature',$config);$this->assertArrayNotHasKey('topP',$config);$this->assertArrayNotHasKey('topK',$config);return Http::response(['candidates'=>[['content'=>['parts'=>[['text'=>$body]]]]],'usageMetadata'=>['promptTokenCount'=>100,'candidatesTokenCount'=>200]],200);}]);
        $engine=app(NewsContentEngine::class);$saved=$engine->process([$engine->buildNews($news)]);$this->assertCount(1,$saved);$this->assertDatabaseHas('ai_contents',['source_type'=>'news','source_id'=>$news->id,'status'=>'generated','input_tokens'=>100,'output_tokens'=>200]);$this->assertSame('AI News Title',$news->fresh()->title);
    }

    public function test_gemini_model_resource_prefix_is_normalized(): void
    {
        $news=News::create(['title'=>'Source title','status'=>'draft']);$this->provider(['model'=>'models/gemini-3.8-flash']);$body=json_encode(['results'=>[$this->generatedPayload($news->id)]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        Http::fake(['generativelanguage.googleapis.com/*'=>function($request)use($body){$this->assertStringContainsString('/v1beta/models/gemini-3.8-flash:generateContent',$request->url());$this->assertStringNotContainsString('models%2F',$request->url());return Http::response(['candidates'=>[['content'=>['parts'=>[['text'=>$body]]]]]],200);}]);
        $engine=app(NewsContentEngine::class);$this->assertCount(1,$engine->process([$engine->buildNews($news)]));
    }

    public function test_gemini_400_retries_without_response_format(): void
    {
        $news=News::create(['title'=>'Source title','status'=>'draft']);$this->provider();$body=json_encode(['results'=>[$this->generatedPayload($news->id)]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        Http::fakeSequence()->push(['error'=>['message'=>'Invalid response format']],400)->push(['candidates'=>[['content'=>['parts'=>[['thought'=>true,'text'=>'internal'],['text'=>$body]]]]]],200);
        $engine=app(NewsContentEngine::class);$this->assertCount(1,$engine->process([$engine->buildNews($news)]));Http::assertSentCount(2);$this->assertTrue(AiContent::where('source_id',$news->id)->where('status','generated')->exists());
    }

    public function test_gemini_failure_falls_back_to_groq(): void
    {
        $news=News::create(['title'=>'Source title','status'=>'draft']);$this->provider(['fallback_provider'=>'groq','fallback_api_key'=>'fallback-secret','fallback_model'=>'llama-3.3-70b-versatile']);$body=json_encode(['results'=>[$this->generatedPayload($news->id)]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        Http::fakeSequence()->push(['error'=>['message'=>'Bad request']],400)->push(['error'=>['message'=>'Still bad']],400)->push(['choices'=>[['message'=>['content'=>$body]]],'usage'=>['prompt_tokens'=>20,'completion_tokens'=>40]],200);
        $engine=app(NewsContentEngine::class);$this->assertCount(1,$engine->process([$engine->buildNews($news)]));$this->assertTrue(AiContent::where('source_id',$news->id)->where('status','generated')->exists());
    }
}
