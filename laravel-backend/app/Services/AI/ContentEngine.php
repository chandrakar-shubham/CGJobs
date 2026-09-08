<?php

namespace App\Services\AI;

use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\AiUsageLog;
use App\Models\Job;
use App\Models\News;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ContentEngine
{
    public function process(array $items): array
    {
        if (!$items) return [];
        $setting = AiProviderSetting::where('enabled', true)->latest()->first();
        if (!$setting || !$setting->api_key) throw new RuntimeException('AI provider is not configured.');

        $requestCount = (int) AiUsageLog::where('provider_setting_id', $setting->id)->whereDate('created_at', today())->count();
        $tokenCount = (int) AiUsageLog::where('provider_setting_id', $setting->id)->whereDate('created_at', today())->sum('input_tokens')
            + (int) AiUsageLog::where('provider_setting_id', $setting->id)->whereDate('created_at', today())->sum('output_tokens');
        $maxItems = max(1, min((int)$setting->max_items_per_request, 100));
        $saved = [];

        foreach (array_chunk($items, $maxItems) as $chunk) {
            if ($requestCount >= (int)$setting->daily_request_limit) {
                foreach ($chunk as $item) $this->markFailed($item, 'Daily AI request limit reached.');
                continue;
            }
            $estimated = $this->estimateTokens($chunk);
            if ($tokenCount + $estimated > (int)$setting->daily_token_limit) {
                if (count($chunk) > 1) {
                    foreach ($this->splitByTokenBudget($chunk, max(1, (int)$setting->daily_token_limit - $tokenCount)) as $smaller) {
                        if ($requestCount >= (int)$setting->daily_request_limit) break;
                        if ($tokenCount + $this->estimateTokens($smaller) > (int)$setting->daily_token_limit) {
                            foreach ($smaller as $item) $this->markFailed($item, 'Daily AI token limit reached.');
                            continue;
                        }
                        $result = $this->processBatch($setting, $smaller);
                        $saved = array_merge($saved, $result['saved']);
                        $requestCount++;
                        $tokenCount += $result['tokens'];
                    }
                } else $this->markFailed($chunk[0], 'Daily AI token limit reached.');
                continue;
            }
            $result = $this->processBatch($setting, $chunk);
            $saved = array_merge($saved, $result['saved']);
            $requestCount++;
            $tokenCount += $result['tokens'];
        }
        return $saved;
    }

    public function buildNews(News $news): array
    {
        return ['id'=>$news->id,'source_type'=>'news','source'=>[
            'title'=>$news->title,'title_en'=>$news->title_en,'summary'=>$news->summary,'summary_en'=>$news->summary_en,
            'content'=>$news->content,'content_en'=>$news->content_en,'category'=>$news->category,'category_en'=>$news->category_en,
            'source'=>$news->source,'source_url'=>$news->source_url,'original_url'=>$news->original_url,'tags'=>$news->tags,
        ]];
    }

    public function buildJob(Job $job): array
    {
        return ['id'=>$job->id,'source_type'=>'job','source'=>[
            'title'=>$job->title,'title_en'=>$job->title_en,'summary'=>$job->summary,'summary_en'=>$job->summary_en,
            'detailed_content'=>$job->detailed_content,'detailed_content_en'=>$job->detailed_content_en,
            'category'=>$job->category,'department'=>$job->department,'vacancies'=>$job->vacancies,'salary'=>$job->salary,
            'eligibility'=>$job->eligibility,'eligibility_en'=>$job->eligibility_en,'age_limit'=>$job->age_limit,
            'selection_process'=>$job->selection_process,'selection_process_en'=>$job->selection_process_en,
            'official_notification_url'=>$job->official_notification_url,'apply_url'=>$job->apply_url,
            'application_start'=>$job->application_start,'last_date'=>$job->last_date,'exam_date'=>$job->exam_date,
        ]];
    }

    private function processBatch(AiProviderSetting $setting, array $items): array
    {
        try {
            $response = $this->callProvider($setting, $items);
            return ['saved'=>$this->saveResults($items, $response, $setting), 'tokens'=>$this->responseTokens($response)];
        } catch (\Throwable $primaryError) {
            $fallback = $this->fallbackProvider($setting);
            if ($fallback) {
                try {
                    $response = $this->callProvider($fallback, $items);
                    $response['_fallback_used'] = true;
                    return ['saved'=>$this->saveResults($items, $response, $setting), 'tokens'=>$this->responseTokens($response)];
                } catch (\Throwable $fallbackError) {
                    $this->logFailure($setting, $items, $fallback, $fallbackError, true);
                }
            } else $this->logFailure($setting, $items, $setting, $primaryError, false);
            foreach ($items as $item) $this->markFailed($item, 'AI generation failed after provider retry.');
            return ['saved'=>[], 'tokens'=>0];
        }
    }

    private function saveResults(array $items, array $response, AiProviderSetting $setting): array
    {
        $results = $response['results'] ?? null;
        if (!is_array($results)) throw new RuntimeException('AI response has no results array.');
        $byId = [];
        foreach ($results as $result) if (is_array($result) && isset($result['id'])) $byId[(int)$result['id']] = $result;
        if (!$byId) throw new RuntimeException('AI response contained no valid item results.');

        $saved = [];
        foreach ($items as $item) {
            $id = (int)$item['id'];
            if (!isset($byId[$id])) { $this->markFailed($item, 'AI response omitted this item.'); continue; }
            try {
                $result = $byId[$id];
                $this->validateResult($item, $result);
                $record = AiContent::updateOrCreate(
                    ['source_type'=>$item['source_type'], 'source_id'=>$id],
                    ['status'=>'generated','source_snapshot'=>$item['source'],'generated_content'=>$result,'input_tokens'=>(int)($response['usage']['input_tokens']??0),'output_tokens'=>(int)($response['usage']['output_tokens']??0),'attempts'=>1,'processed_at'=>now(),'error_message'=>null]
                );
                $saved[] = $record;
                $this->applyGeneratedContent($item, $result, $setting);
            } catch (\Throwable $e) { $this->markFailed($item, 'Validation failed: '.substr($e->getMessage(),0,500)); }
        }
        return $saved;
    }

    private function validateResult(array $item, array $result): void
    {
        if ((int)($result['id']??0) !== (int)$item['id']) throw new RuntimeException('AI result id mismatch.');
        foreach (['mobile','website','seo'] as $section) if (!isset($result[$section]) || !is_array($result[$section])) throw new RuntimeException('AI result missing '.$section.' section.');
        if (!is_string($result['mobile']['summary']??null) || trim($result['mobile']['summary'])==='') throw new RuntimeException('AI result missing mobile summary.');
        if (!is_string($result['website']['content']??null) || trim($result['website']['content'])==='') throw new RuntimeException('AI result missing website content.');
        if (!is_string($result['seo']['title']??null) || !is_string($result['seo']['description']??null)) throw new RuntimeException('AI result missing SEO fields.');
    }

    private function markFailed(array $item, string $message): void
    {
        AiContent::updateOrCreate(['source_type'=>$item['source_type'],'source_id'=>$item['id']],['status'=>'failed','source_snapshot'=>$item['source'],'attempts'=>1,'error_message'=>$message]);
    }

    private function logFailure(AiProviderSetting $setting, array $items, AiProviderSetting $provider, \Throwable $e, bool $fallback): void
    {
        AiUsageLog::create(['provider_setting_id'=>$setting->id,'provider'=>$provider->provider,'model'=>$provider->model,'source_type'=>$items[0]['source_type']??null,'item_count'=>count($items),'fallback_used'=>$fallback,'success'=>false,'error_message'=>substr($e->getMessage(),0,1000)]);
    }

    private function fallbackProvider(AiProviderSetting $setting): ?AiProviderSetting
    {
        if (!$setting->fallback_provider || !$setting->fallback_api_key) return null;
        $fallback = clone $setting;
        $fallback->provider = $setting->fallback_provider;
        $fallback->api_key = $setting->fallback_api_key;
        $fallback->model = $setting->fallback_model ?: null;
        return $fallback;
    }

    private function callProvider(AiProviderSetting $setting, array $items): array
    {
        $prompt=$this->prompt($items); $provider=strtolower($setting->provider);
        if ($provider==='gemini') {
            $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($setting->model?:'gemini-2.5-flash').':generateContent';
            $r=Http::timeout(120)->withQueryParameters(['key'=>$setting->api_key])->post($url,['contents'=>[['parts'=>[['text'=>$prompt]]]],'generationConfig'=>['responseMimeType'=>'application/json']]);
        } elseif ($provider==='groq') {
            $r=Http::timeout(120)->withToken($setting->api_key)->post('https://api.groq.com/openai/v1/chat/completions',['model'=>$setting->model?:'llama-3.3-70b-versatile','temperature'=>0.3,'response_format'=>['type'=>'json_object'],'messages'=>[['role'=>'system','content'=>'Return valid JSON only. Never invent or modify factual job/news details.'],['role'=>'user','content'=>$prompt]]]);
        } else throw new RuntimeException('Unsupported AI provider.');
        if (!$r->successful()) throw new RuntimeException('AI provider request failed with HTTP '.$r->status().'.');
        $json=$r->json();
        $text=$provider==='gemini'?($json['candidates'][0]['content']['parts'][0]['text']??''):($json['choices'][0]['message']['content']??'');
        $decoded=json_decode($text,true); if (!is_array($decoded)) throw new RuntimeException('AI provider returned invalid JSON.');
        $usage=$provider==='groq'?($json['usage']??[]):($json['usageMetadata']??[]);
        $decoded['usage']=['input_tokens'=>(int)($usage['prompt_tokens']??$usage['promptTokenCount']??0),'output_tokens'=>(int)($usage['completion_tokens']??$usage['candidatesTokenCount']??0)];
        AiUsageLog::create(['provider_setting_id'=>$setting->id,'provider'=>$provider,'model'=>$setting->model,'source_type'=>$items[0]['source_type']??null,'item_count'=>count($items),'input_tokens'=>$decoded['usage']['input_tokens'],'output_tokens'=>$decoded['usage']['output_tokens'],'fallback_used'=>false,'success'=>true]);
        return $decoded;
    }

    private function responseTokens(array $response): int { return (int)($response['usage']['input_tokens']??0)+(int)($response['usage']['output_tokens']??0); }
    private function estimateTokens(array $items): int { return max(1,(int)ceil(strlen($this->prompt($items))/4))+(count($items)*700); }
    private function splitByTokenBudget(array $items,int $budget): array { $chunks=[];$current=[];foreach($items as $item){$candidate=array_merge($current,[$item]);if($current&&$this->estimateTokens($candidate)>$budget){$chunks[]=$current;$current=[$item];}else$current=$candidate;}if($current)$chunks[]=$current;return $chunks; }

    private function prompt(array $items): string
    {
        $payload=json_encode($items,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return <<<PROMPT
You are the CG AI Content Engine. Process all source records in one response.
Return ONLY JSON: {"results":[...]}. For every source record return exactly one result with the same id.
Never invent, infer, alter, or contradict factual source data. For jobs, recruitment facts are backend-controlled; do not replace vacancies, salary, eligibility, age limits, selection process, URLs, application dates, exam dates, department, or names. Generated content must describe supplied facts only.
For each record return id, mobile, website, seo. Mobile is short and app-friendly. Website is a detailed original article with headings and paragraphs. SEO includes title, description, focus_keyword, keywords, slug, og_title, og_description, image_alt.
For jobs also return faq. For news return why_in_news, key_facts and exam_relevance. Use Hindi when the source is Hindi and provide English variants when English source fields exist. Make content useful for competitive-exam readers. Do not mention AI generation.
SOURCE RECORDS:
$payload
PROMPT;
    }

    private function applyGeneratedContent(array $item,array $result,AiProviderSetting $setting): void
    {
        if($item['source_type']==='news'){$news=News::find($item['id']);if(!$news)return;$website=$result['website']??[];$seo=$result['seo']??[];$mobile=$result['mobile']??[];$news->update(['summary'=>$mobile['summary']??$news->summary,'content'=>$website['content']??$news->content,'title'=>$website['title']??$news->title,'tags'=>$seo['keywords']??$news->tags,'status'=>$setting->auto_publish_news?'published':$news->status,'published_at'=>$setting->auto_publish_news?($news->published_at?:now()):$news->published_at]);}
        else{$job=Job::find($item['id']);if(!$job)return;$website=$result['website']??[];$seo=$result['seo']??[];$mobile=$result['mobile']??[];$job->update(['summary'=>$mobile['summary']??$job->summary,'detailed_content'=>$website['content']??$job->detailed_content,'seo_title'=>$seo['title']??$job->seo_title,'seo_description'=>$seo['description']??$job->seo_description,'seo_keywords'=>is_array($seo['keywords']??null)?implode(', ',$seo['keywords']):($seo['keywords']??$job->seo_keywords),'workflow_status'=>$setting->auto_publish_jobs?'published':$job->workflow_status]);}
    }
}
