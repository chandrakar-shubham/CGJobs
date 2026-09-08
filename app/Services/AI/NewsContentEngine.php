<?php

namespace App\Services\AI;

use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\AiUsageLog;
use App\Models\News;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** News-only AI pipeline. Jobs continue using the existing ContentEngine. */
class NewsContentEngine
{
    public function buildNews(News $news): array
    {
        return ['id'=>$news->id,'source_type'=>'news','source'=>[
            'title'=>$news->title,'title_en'=>$news->title_en,'summary'=>$news->summary,'summary_en'=>$news->summary_en,
            'content'=>$news->content,'content_en'=>$news->content_en,'category'=>$news->category,'category_en'=>$news->category_en,
            'source'=>$news->source,'source_url'=>$news->source_url,'original_url'=>$news->original_url,'image_url'=>$news->image_url,'tags'=>$news->tags,
        ]];
    }

    public function process(array $items): array
    {
        if (!$items) return [];
        $setting=AiProviderSetting::where('enabled',true)->latest()->first();
        if (!$setting || !$setting->api_key) throw new RuntimeException('AI provider is not configured. Save an enabled API key first.');
        $requestCount=(int)AiUsageLog::where('provider_setting_id',$setting->id)->whereDate('created_at',today())->count();
        $tokenCount=(int)AiUsageLog::where('provider_setting_id',$setting->id)->whereDate('created_at',today())->sum('input_tokens')+(int)AiUsageLog::where('provider_setting_id',$setting->id)->whereDate('created_at',today())->sum('output_tokens');
        $configured=max(1,min((int)($setting->max_items_per_request?:10),100));$maxItems=strtolower((string)$setting->provider)==='gemini'?min($configured,10):min($configured,20);$saved=[];
        foreach(array_chunk($items,$maxItems) as $chunk){
            if($requestCount >= (int)$setting->daily_request_limit){foreach($chunk as $item)$this->markFailed($item,'Daily AI request limit reached.');continue;}
            if($tokenCount >= (int)$setting->daily_token_limit){foreach($chunk as $item)$this->markFailed($item,'Daily AI token limit reached.');continue;}
            $result=$this->processBatch($setting,$chunk);$saved=array_merge($saved,$result['saved']);$requestCount+=$result['requests'];$tokenCount+=$result['tokens'];
        }
        return $saved;
    }

    private function processBatch(AiProviderSetting $setting,array $items):array
    {
        try{$response=$this->callProvider($setting,$items,false);return ['saved'=>$this->saveResults($items,$response,$setting),'tokens'=>$this->responseTokens($response),'requests'=>(int)($response['_request_count']??1)];}
        catch(\Throwable $primaryError){
            $fallback=$this->fallbackProvider($setting);
            if($fallback){$this->logFailure($setting,$items,$setting,$primaryError,false);try{$response=$this->callProvider($fallback,$items,true);return ['saved'=>$this->saveResults($items,$response,$setting),'tokens'=>$this->responseTokens($response),'requests'=>(int)($response['_request_count']??1)+1];}catch(\Throwable $fallbackError){$this->logFailure($setting,$items,$fallback,$fallbackError,true);$message=$fallbackError->getMessage();}}
            else{$this->logFailure($setting,$items,$setting,$primaryError,false);$message=$primaryError->getMessage();}
            foreach($items as $item)$this->markFailed($item,'AI generation failed: '.substr($message,0,900));return ['saved'=>[],'tokens'=>0,'requests'=>1];
        }
    }

    private function saveResults(array $items,array $response,AiProviderSetting $setting):array
    {
        $results=$response['results']??null;if(!is_array($results))throw new RuntimeException('AI response has no results array.');$byId=[];
        foreach($results as $result)if(is_array($result)&&isset($result['id']))$byId[(int)$result['id']]=$result;
        if(!$byId)throw new RuntimeException('AI response contained no valid item results.');$saved=[];
        foreach($items as $item){$id=(int)$item['id'];if(!isset($byId[$id])){$this->markFailed($item,'AI response omitted this item.');continue;}
            try{$result=$byId[$id];$this->validateResult($item,$result);$record=AiContent::updateOrCreate(['source_type'=>'news','source_id'=>$id],['status'=>'generated','source_snapshot'=>$item['source'],'generated_content'=>$result,'input_tokens'=>(int)($response['usage']['input_tokens']??0),'output_tokens'=>(int)($response['usage']['output_tokens']??0),'attempts'=>1,'processed_at'=>now(),'published_at'=>null,'error_message'=>null]);$saved[]=$record;$this->applyGeneratedContent($item,$result,$setting);}
            catch(\Throwable $e){$this->markFailed($item,'Validation failed: '.substr($e->getMessage(),0,700));}
        }
        return $saved;
    }

    private function validateResult(array $item,array $result):void
    {
        if((int)($result['id']??0)!==(int)$item['id'])throw new RuntimeException('AI result id mismatch.');
        foreach(['mobile','website','seo'] as $section)if(!isset($result[$section])||!is_array($result[$section]))throw new RuntimeException('AI result missing '.$section.' section.');
        if(!is_string($result['mobile']['summary']??null)||trim($result['mobile']['summary'])==='')throw new RuntimeException('AI result missing mobile summary.');
        if(!is_string($result['website']['content']??null)||trim($result['website']['content'])==='')throw new RuntimeException('AI result missing website content.');
        if(!is_string($result['seo']['title']??null)||!is_string($result['seo']['description']??null))throw new RuntimeException('AI result missing SEO fields.');
    }

    private function applyGeneratedContent(array $item,array $result,AiProviderSetting $setting):void
    {
        $news=News::find($item['id']);if(!$news)return;$mobile=$result['mobile']??[];$website=$result['website']??[];$seo=$result['seo']??[];$auto=(bool)$setting->auto_publish_news;
        $news->update(['summary'=>$mobile['summary']??$news->summary,'summary_en'=>$mobile['summary_en']??$mobile['summary']??$news->summary_en,'content'=>$website['content']??$news->content,'content_en'=>$website['content_en']??$website['content']??$news->content_en,'title'=>$website['title']??$news->title,'title_en'=>$website['title_en']??$website['title']??$news->title_en,'tags'=>is_array($seo['keywords']??null)?$seo['keywords']:($news->tags?:[]),'status'=>$auto?'published':$news->status,'published_at'=>$auto?($news->published_at?:now()):$news->published_at,'published_web'=>$auto?true:$news->published_web,'published_mobile'=>$auto?true:$news->published_mobile]);
        if($auto)AiContent::where('source_type','news')->where('source_id',$news->id)->update(['status'=>'published','published_at'=>now()]);
    }

    private function markFailed(array $item,string $message):void
    {$previous=AiContent::where('source_type','news')->where('source_id',$item['id'])->first();AiContent::updateOrCreate(['source_type'=>'news','source_id'=>$item['id']],['status'=>'failed','source_snapshot'=>$item['source'],'attempts'=>((int)($previous?->attempts??0))+1,'error_message'=>$message]);}

    private function logFailure(AiProviderSetting $setting,array $items,AiProviderSetting $provider,\Throwable $e,bool $fallback):void
    {AiUsageLog::create(['provider_setting_id'=>$setting->id,'provider'=>$provider->provider,'model'=>$provider->model,'source_type'=>'news','item_count'=>count($items),'fallback_used'=>$fallback,'success'=>false,'error_message'=>substr($e->getMessage(),0,1200)]);}
    private function fallbackProvider(AiProviderSetting $setting):?AiProviderSetting
    {if(!$setting->fallback_provider||!$setting->fallback_api_key)return null;$fallback=clone $setting;$fallback->provider=$setting->fallback_provider;$fallback->api_key=$setting->fallback_api_key;$fallback->model=$setting->fallback_model?:null;return $fallback;}

    private function callProvider(AiProviderSetting $setting,array $items,bool $fallbackUsed):array
    {
        $prompt=$this->prompt($items);$provider=strtolower((string)$setting->provider);
        if($provider==='gemini'){
            // The admin setting may contain either "gemini-3.8-flash" or the REST resource form
            // "models/gemini-3.8-flash". Normalize both before constructing the endpoint. Passing
            // the resource prefix through rawurlencode produces models%2F... and Gemini rejects it
            // with "GenerateContentRequest.model: unexpected model name format".
            $model=trim((string)($setting->model?:'gemini-3.8-flash'));
            $model=preg_replace('#^https?://generativelanguage\.googleapis\.com/v1beta/models/#i','',$model);
            $model=preg_replace('#^models/#i','',$model);
            $model=trim((string)$model,"/ \t\r\n");
            if($model==='')$model='gemini-3.8-flash';
            $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent';$headers=['x-goog-api-key'=>$setting->api_key,'Content-Type'=>'application/json'];$requestCount=1;
            $body=['contents'=>[['role'=>'user','parts'=>[['text'=>$prompt]]]],'generationConfig'=>['responseFormat'=>['text'=>['mimeType'=>'application/json']],'thinkingConfig'=>['thinkingLevel'=>'low']]];
            $r=Http::timeout(120)->withHeaders($headers)->post($url,$body);
            if($r->status()===400){$requestCount++;$r=Http::timeout(120)->withHeaders($headers)->post($url,['contents'=>[['role'=>'user','parts'=>[['text'=>$prompt]]]],'generationConfig'=>['thinkingConfig'=>['thinkingLevel'=>'low']]]);}
            if(!$r->successful()){throw new RuntimeException('AI provider request failed with HTTP '.$r->status().'. Model: '.$model.($this->providerError($r)!==''?'. '.substr($this->providerError($r),0,900):'.'));}
            $json=$r->json();$decoded=$this->decodeJsonResponse($this->extractGeminiText($json));$usage=$json['usageMetadata']??[];$decoded['usage']=['input_tokens'=>(int)($usage['promptTokenCount']??0),'output_tokens'=>(int)($usage['candidatesTokenCount']??0)];$decoded['_request_count']=$requestCount;$this->logSuccess($setting,$provider,$model,$items,$decoded,$fallbackUsed);return $decoded;
        }
        if($provider==='groq'){
            $model=$setting->model?:'llama-3.3-70b-versatile';$r=Http::timeout(120)->withToken($setting->api_key)->post('https://api.groq.com/openai/v1/chat/completions',['model'=>$model,'temperature'=>0.2,'response_format'=>['type'=>'json_object'],'messages'=>[['role'=>'system','content'=>'Return valid JSON only. Never invent or modify factual news details.'],['role'=>'user','content'=>$prompt]]]);
            if(!$r->successful()){throw new RuntimeException('AI provider request failed with HTTP '.$r->status().($this->providerError($r)!==''?'. '.substr($this->providerError($r),0,900):'.'));}
            $json=$r->json();$decoded=$this->decodeJsonResponse($json['choices'][0]['message']['content']??'');$usage=$json['usage']??[];$decoded['usage']=['input_tokens'=>(int)($usage['prompt_tokens']??0),'output_tokens'=>(int)($usage['completion_tokens']??0)];$decoded['_request_count']=1;$this->logSuccess($setting,$provider,$model,$items,$decoded,$fallbackUsed);return $decoded;
        }
        throw new RuntimeException('Unsupported AI provider: '.$provider);
    }

    private function extractGeminiText(array $json):string
    {$parts=$json['candidates'][0]['content']['parts']??[];$texts=[];foreach($parts as $part)if(is_array($part)&&isset($part['text'])&&!($part['thought']??false))$texts[]=(string)$part['text'];$text=trim(implode("\n",$texts));if($text===''&&isset($parts[0]['text']))$text=trim((string)$parts[0]['text']);if($text==='')throw new RuntimeException('AI provider returned an empty response.');return $text;}
    private function decodeJsonResponse(string $text):array
    {$text=trim($text);if(str_starts_with($text,'```')){$text=preg_replace('/^```(?:json)?\s*/i','',$text);$text=preg_replace('/\s*```$/','',$text);}$decoded=json_decode(trim($text),true);if(!is_array($decoded))throw new RuntimeException('AI provider returned invalid JSON.');return $decoded;}
    private function providerError($response):string
    {$error=$response->json('error');if(is_array($error))return trim((string)($error['message']??json_encode($error,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)));return trim((string)$response->body());}
    private function logSuccess(AiProviderSetting $setting,string $provider,string $model,array $items,array $decoded,bool $fallbackUsed):void
    {AiUsageLog::create(['provider_setting_id'=>$setting->id,'provider'=>$provider,'model'=>$model,'source_type'=>'news','item_count'=>count($items),'input_tokens'=>(int)($decoded['usage']['input_tokens']??0),'output_tokens'=>(int)($decoded['usage']['output_tokens']??0),'fallback_used'=>$fallbackUsed,'success'=>true]);}
    private function responseTokens(array $response):int{return (int)($response['usage']['input_tokens']??0)+(int)($response['usage']['output_tokens']??0);}

    private function prompt(array $items):string
    {
        $payload=json_encode($items,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return <<<PROMPT
You are the CGSSB News Content Engine. Process every supplied news item and return ONE JSON object with this exact top-level shape:
{"results":[{"id":123,"mobile":{"summary":"","summary_en":""},"website":{"title":"","title_en":"","content":"","content_en":""},"seo":{"title":"","description":"","focus_keyword":"","keywords":[],"slug":"","og_title":"","og_description":"","image_alt":""}}]}
Rules:
1. Return one result for every input item and preserve each input id exactly.
2. Never invent facts, people, numbers, dates, locations, quotations, government decisions or claims.
3. Rewrite and organize only the supplied factual material.
4. Mobile summary must be short, clear and useful for competitive-exam/current-affairs readers.
5. Website content must be a detailed readable HTML article based only on the source.
6. SEO must accurately describe the same article; keywords must be an array of short strings.
7. Never alter source URLs or factual source information.
8. Return JSON only. No markdown fences or commentary.
9. Process every item in the batch.
INPUT NEWS:
{$payload}
PROMPT;
    }
}
