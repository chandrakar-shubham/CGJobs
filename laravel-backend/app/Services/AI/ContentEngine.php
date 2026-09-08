<?php

namespace App\Services\AI;

use App\Models\AiContent;
use App\Models\AiProviderSetting;
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

        $chunks = array_chunk($items, max(1, min((int)$setting->max_items_per_request, 100)));
        $saved = [];
        foreach ($chunks as $chunk) {
            $response = $this->callProvider($setting, $chunk);
            foreach ($response['results'] ?? [] as $result) {
                $id = (int)($result['id'] ?? 0);
                $item = collect($chunk)->firstWhere('id', $id);
                if (!$item) continue;
                $record = AiContent::updateOrCreate(
                    ['source_type' => $item['source_type'], 'source_id' => $id],
                    [
                        'status' => 'generated',
                        'source_snapshot' => $item['source'],
                        'generated_content' => $result,
                        'input_tokens' => (int)($response['usage']['input_tokens'] ?? 0),
                        'output_tokens' => (int)($response['usage']['output_tokens'] ?? 0),
                        'attempts' => 1,
                        'processed_at' => now(),
                        'error_message' => null,
                    ]
                );
                $saved[] = $record;
                $this->applyGeneratedContent($item, $result, $setting);
            }
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

    private function callProvider(AiProviderSetting $setting, array $items): array
    {
        $prompt = $this->prompt($items);
        $provider = strtolower($setting->provider);
        if ($provider === 'gemini') {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($setting->model ?: 'gemini-2.5-flash').':generateContent';
            $r = Http::timeout(120)->withQueryParameters(['key'=>$setting->api_key])->post($url, [
                'contents'=>[['parts'=>[['text'=>$prompt]]]],
                'generationConfig'=>['responseMimeType'=>'application/json'],
            ]);
        } elseif ($provider === 'groq') {
            $r = Http::timeout(120)->withToken($setting->api_key)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'=>$setting->model ?: 'llama-3.3-70b-versatile',
                'temperature'=>0.3,
                'response_format'=>['type'=>'json_object'],
                'messages'=>[['role'=>'system','content'=>'Return valid JSON only. Never invent factual job/news details.'],['role'=>'user','content'=>$prompt]],
            ]);
        } else {
            throw new RuntimeException('Unsupported AI provider: '.$setting->provider);
        }
        if (!$r->successful()) throw new RuntimeException('AI provider error: '.$r->status().' '.$r->body());
        $json = $r->json();
        $text = $provider === 'gemini' ? ($json['candidates'][0]['content']['parts'][0]['text'] ?? '') : ($json['choices'][0]['message']['content'] ?? '');
        $decoded = json_decode($text, true);
        if (!is_array($decoded)) throw new RuntimeException('AI provider returned invalid JSON.');
        $usage = $provider === 'groq' ? ($json['usage'] ?? []) : [];
        $decoded['usage'] = ['input_tokens'=>(int)($usage['prompt_tokens'] ?? 0),'output_tokens'=>(int)($usage['completion_tokens'] ?? 0)];
        return $decoded;
    }

    private function prompt(array $items): string
    {
        $payload = json_encode($items, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        return <<<PROMPT
You are CG AI Content Engine v1. Process multiple source records in one response.
Return ONLY JSON: {"results":[...]}. Preserve every factual field supplied for jobs/news; never invent dates, vacancies, salaries, URLs, eligibility or names.
For each record return: id, mobile, website, seo. Mobile must be short and app-friendly. Website must be a detailed original article with headings and paragraphs. SEO must include title, description, focus_keyword, keywords, slug, og_title, og_description, image_alt.
For jobs, also return faq and keep factual recruitment data unchanged. For news, return why_in_news, key_facts and exam_relevance. Write in Hindi where source is Hindi and include English variants when source has English fields.
Make content useful for competitive-exam readers, factual, readable and SEO-friendly. Do not mention that AI generated it.
SOURCE RECORDS:
$payload
PROMPT;
    }

    private function applyGeneratedContent(array $item, array $result, AiProviderSetting $setting): void
    {
        $type = $item['source_type'];
        if ($type === 'news') {
            $news = News::find($item['id']);
            if (!$news) return;
            $website = $result['website'] ?? [];
            $seo = $result['seo'] ?? [];
            $mobile = $result['mobile'] ?? [];
            $news->update([
                'summary'=>$mobile['summary'] ?? $news->summary,
                'content'=>$website['content'] ?? $news->content,
                'title'=>$website['title'] ?? $news->title,
                'tags'=>$seo['keywords'] ?? $news->tags,
                'status'=>$setting->auto_publish_news ? 'published' : $news->status,
                'published_at'=>$setting->auto_publish_news ? ($news->published_at ?: now()) : $news->published_at,
            ]);
        } elseif ($type === 'job') {
            $job = Job::find($item['id']);
            if (!$job) return;
            $website = $result['website'] ?? [];
            $seo = $result['seo'] ?? [];
            $job->update([
                'summary'=>$result['mobile']['summary'] ?? $job->summary,
                'detailed_content'=>$website['content'] ?? $job->detailed_content,
                'seo_title'=>$seo['title'] ?? $job->seo_title,
                'seo_description'=>$seo['description'] ?? $job->seo_description,
                'seo_keywords'=>is_array($seo['keywords'] ?? null) ? implode(', ', $seo['keywords']) : ($seo['keywords'] ?? $job->seo_keywords),
                'workflow_status'=>$setting->auto_publish_jobs ? 'published' : $job->workflow_status,
            ]);
        }
    }
}
