<?php

namespace App\Services\AI;

use App\Models\AiContent;
use App\Models\AiProviderSetting;
use App\Models\AiUsageLog;
use App\Models\News;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Production News-only AI pipeline.
 *
 * Jobs keep using the existing ContentEngine. This service is intentionally
 * isolated so News/Gemini changes cannot alter the Jobs model/API pipeline.
 */
class NewsContentEngine
{
    public function buildNews(News $news): array
    {
        return [
            'id' => $news->id,
            'source_type' => 'news',
            'source' => [
                'title' => $news->title,
                'title_en' => $news->title_en,
                'summary' => $news->summary,
                'summary_en' => $news->summary_en,
                'content' => $news->content,
                'content_en' => $news->content_en,
                'category' => $news->category,
                'category_en' => $news->category_en,
                'source' => $news->source,
                'source_url' => $news->source_url,
                'original_url' => $news->original_url,
                'image_url' => $news->image_url,
                'tags' => $news->tags,
            ],
        ];
    }

    public function process(array $items): array
    {
        if (!$items) return [];

        $setting = AiProviderSetting::where('enabled', true)->latest()->first();
        if (!$setting || !$setting->api_key) {
            throw new RuntimeException('AI provider is not configured.');
        }

        $requestCount = (int) AiUsageLog::where('provider_setting_id', $setting->id)->whereDate('created_at', today())->count();
        $tokenCount = (int) AiUsageLog::where('provider_setting_id', $setting->id)->whereDate('created_at', today())->sum('input_tokens')
            + (int) AiUsageLog::where('provider_setting_id', $setting->id)->whereDate('created_at', today())->sum('output_tokens');

        $configured = max(1, min((int) $setting->max_items_per_request, 100));
        $maxItems = strtolower((string) $setting->provider) === 'gemini' ? min($configured, 10) : min($configured, 20);
        $saved = [];

        foreach (array_chunk($items, $maxItems) as $chunk) {
            if ($requestCount >= (int) $setting->daily_request_limit) {
                foreach ($chunk as $item) $this->markFailed($item, 'Daily AI request limit reached.');
                continue;
            }

            $result = $this->processBatch($setting, $chunk);
            $saved = array_merge($saved, $result['saved']);
            $requestCount += $result['requests'];
            $tokenCount += $result['tokens'];

            if ($tokenCount >= (int) $setting->daily_token_limit) {
                // Remaining chunks are deliberately marked rather than sent to the provider.
                foreach (array_slice($items, array_search($chunk, array_chunk($items, $maxItems), true) * $maxItems + count($chunk)) as $unused) {
                    $this->markFailed($unused, 'Daily AI token limit reached.');
                }
                break;
            }
        }

        return $saved;
    }

    private function processBatch(AiProviderSetting $setting, array $items): array
    {
        try {
            $response = $this->callProvider($setting, $items, false);
            return ['saved' => $this->saveResults($items, $response, $setting), 'tokens' => $this->responseTokens($response), 'requests' => 1];
        } catch (\Throwable $primaryError) {
            $fallback = $this->fallbackProvider($setting);
            if ($fallback) {
                $this->logFailure($setting, $items, $setting, $primaryError, false);
                try {
                    $response = $this->callProvider($fallback, $items, true);
                    return ['saved' => $this->saveResults($items, $response, $setting), 'tokens' => $this->responseTokens($response), 'requests' => 2];
                } catch (\Throwable $fallbackError) {
                    $this->logFailure($setting, $items, $fallback, $fallbackError, true);
                    $message = $fallbackError->getMessage();
                }
            } else {
                $this->logFailure($setting, $items, $setting, $primaryError, false);
                $message = $primaryError->getMessage();
            }

            foreach ($items as $item) $this->markFailed($item, 'AI generation failed: ' . substr($message, 0, 700));
            return ['saved' => [], 'tokens' => 0, 'requests' => $fallback ? 2 : 1];
        }
    }

    private function saveResults(array $items, array $response, AiProviderSetting $setting): array
    {
        $results = $response['results'] ?? null;
        if (!is_array($results)) throw new RuntimeException('AI response has no results array.');

        $byId = [];
        foreach ($results as $result) if (is_array($result) && isset($result['id'])) $byId[(int) $result['id']] = $result;
        if (!$byId) throw new RuntimeException('AI response contained no valid item results.');

        $saved = [];
        foreach ($items as $item) {
            $id = (int) $item['id'];
            if (!isset($byId[$id])) {
                $this->markFailed($item, 'AI response omitted this item.');
                continue;
            }
            try {
                $result = $byId[$id];
                $this->validateResult($item, $result);
                $record = AiContent::updateOrCreate(
                    ['source_type' => 'news', 'source_id' => $id],
                    [
                        'status' => 'generated',
                        'source_snapshot' => $item['source'],
                        'generated_content' => $result,
                        'input_tokens' => (int) ($response['usage']['input_tokens'] ?? 0),
                        'output_tokens' => (int) ($response['usage']['output_tokens'] ?? 0),
                        'attempts' => 1,
                        'processed_at' => now(),
                        'error_message' => null,
                    ]
                );
                $saved[] = $record;
                $this->applyGeneratedContent($item, $result, $setting);
            } catch (\Throwable $e) {
                $this->markFailed($item, 'Validation failed: ' . substr($e->getMessage(), 0, 500));
            }
        }
        return $saved;
    }

    private function validateResult(array $item, array $result): void
    {
        if ((int) ($result['id'] ?? 0) !== (int) $item['id']) throw new RuntimeException('AI result id mismatch.');
        foreach (['mobile', 'website', 'seo'] as $section) {
            if (!isset($result[$section]) || !is_array($result[$section])) throw new RuntimeException('AI result missing ' . $section . ' section.');
        }
        if (!is_string($result['mobile']['summary'] ?? null) || trim($result['mobile']['summary']) === '') throw new RuntimeException('AI result missing mobile summary.');
        if (!is_string($result['website']['content'] ?? null) || trim($result['website']['content']) === '') throw new RuntimeException('AI result missing website content.');
        if (!is_string($result['seo']['title'] ?? null) || !is_string($result['seo']['description'] ?? null)) throw new RuntimeException('AI result missing SEO fields.');
    }

    private function applyGeneratedContent(array $item, array $result, AiProviderSetting $setting): void
    {
        $news = News::find($item['id']);
        if (!$news) return;

        $mobile = $result['mobile'] ?? [];
        $website = $result['website'] ?? [];
        $seo = $result['seo'] ?? [];
        $auto = (bool) $setting->auto_publish_news;

        $news->update([
            'summary' => $mobile['summary'] ?? $news->summary,
            'summary_en' => $mobile['summary_en'] ?? $mobile['summary'] ?? $news->summary_en,
            'content' => $website['content'] ?? $news->content,
            'content_en' => $website['content_en'] ?? $website['content'] ?? $news->content_en,
            'title' => $website['title'] ?? $news->title,
            'title_en' => $website['title_en'] ?? $website['title'] ?? $news->title_en,
            'tags' => is_array($seo['keywords'] ?? null) ? $seo['keywords'] : ($news->tags ?: []),
            'status' => $auto ? 'published' : $news->status,
            'published_at' => $auto ? ($news->published_at ?: now()) : $news->published_at,
            'published_web' => $auto ? true : $news->published_web,
            'published_mobile' => $auto ? true : $news->published_mobile,
        ]);

        if ($auto) {
            AiContent::where('source_type', 'news')->where('source_id', $news->id)->update(['status' => 'published', 'published_at' => now()]);
        }
    }

    private function markFailed(array $item, string $message): void
    {
        AiContent::updateOrCreate(
            ['source_type' => 'news', 'source_id' => $item['id']],
            ['status' => 'failed', 'source_snapshot' => $item['source'], 'attempts' => 1, 'error_message' => $message]
        );
    }

    private function logFailure(AiProviderSetting $setting, array $items, AiProviderSetting $provider, \Throwable $e, bool $fallback): void
    {
        AiUsageLog::create([
            'provider_setting_id' => $setting->id,
            'provider' => $provider->provider,
            'model' => $provider->model,
            'source_type' => 'news',
            'item_count' => count($items),
            'fallback_used' => $fallback,
            'success' => false,
            'error_message' => substr($e->getMessage(), 0, 1000),
        ]);
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

    private function callProvider(AiProviderSetting $setting, array $items, bool $fallbackUsed): array
    {
        $prompt = $this->prompt($items);
        $provider = strtolower((string) $setting->provider);

        if ($provider === 'gemini') {
            $model = $setting->model ?: 'gemini-3.8-flash';
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent';
            $r = Http::timeout(120)->withHeaders([
                'x-goog-api-key' => $setting->api_key,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $prompt]],
                ]],
                'generationConfig' => [
                    'responseFormat' => [
                        'text' => [
                            'mimeType' => 'application/json',
                            'schema' => $this->responseSchema(),
                        ],
                    ],
                    'thinkingConfig' => ['thinkingLevel' => 'low'],
                ],
            ]);
        } elseif ($provider === 'groq') {
            $model = $setting->model ?: 'llama-3.3-70b-versatile';
            $r = Http::timeout(120)->withToken($setting->api_key)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => 'Return valid JSON only. Never invent or modify factual news details.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
        } else {
            throw new RuntimeException('Unsupported AI provider.');
        }

        if (!$r->successful()) {
            $error = $r->json('error');
            $detail = is_array($error) ? trim((string) ($error['message'] ?? json_encode($error, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))) : trim($r->body());
            throw new RuntimeException('AI provider request failed with HTTP ' . $r->status() . ($detail !== '' ? '. ' . substr($detail, 0, 700) : '.'));
        }

        $json = $r->json();
        $text = $provider === 'gemini' ? ($json['candidates'][0]['content']['parts'][0]['text'] ?? '') : ($json['choices'][0]['message']['content'] ?? '');
        if ($text === '') throw new RuntimeException('AI provider returned an empty response.');
        $decoded = json_decode($text, true);
        if (!is_array($decoded)) throw new RuntimeException('AI provider returned invalid JSON.');

        $usage = $provider === 'groq' ? ($json['usage'] ?? []) : ($json['usageMetadata'] ?? []);
        $decoded['usage'] = [
            'input_tokens' => (int) ($usage['prompt_tokens'] ?? $usage['promptTokenCount'] ?? 0),
            'output_tokens' => (int) ($usage['completion_tokens'] ?? $usage['candidatesTokenCount'] ?? 0),
        ];

        AiUsageLog::create([
            'provider_setting_id' => $setting->id,
            'provider' => $provider,
            'model' => $model,
            'source_type' => 'news',
            'item_count' => count($items),
            'input_tokens' => $decoded['usage']['input_tokens'],
            'output_tokens' => $decoded['usage']['output_tokens'],
            'fallback_used' => $fallbackUsed,
            'success' => true,
        ]);
        return $decoded;
    }

    private function responseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'results' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'mobile' => ['type' => 'object', 'properties' => ['summary' => ['type' => 'string'], 'summary_en' => ['type' => 'string']], 'required' => ['summary']],
                            'website' => ['type' => 'object', 'properties' => ['title' => ['type' => 'string'], 'title_en' => ['type' => 'string'], 'content' => ['type' => 'string'], 'content_en' => ['type' => 'string']], 'required' => ['content']],
                            'seo' => ['type' => 'object', 'properties' => ['title' => ['type' => 'string'], 'description' => ['type' => 'string'], 'focus_keyword' => ['type' => 'string'], 'keywords' => ['type' => 'array', 'items' => ['type' => 'string']], 'slug' => ['type' => 'string'], 'og_title' => ['type' => 'string'], 'og_description' => ['type' => 'string'], 'image_alt' => ['type' => 'string']], 'required' => ['title', 'description']],
                        ],
                        'required' => ['id', 'mobile', 'website', 'seo'],
                    ],
                ],
            ],
            'required' => ['results'],
        ];
    }

    private function prompt(array $items): string
    {
        $payload = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return "You are the CGSSB News Content Engine. Rewrite supplied source records into original, factual content for a mobile/app feed and a detailed website article.\n\nReturn ONLY JSON: {\"results\":[...]}. For every source record return exactly one result with the same id.\nNever invent, infer, alter, or contradict factual source data. Preserve names, dates, numbers, URLs, organizations and claims from the supplied source.\nFor each record return id, mobile, website, seo. Mobile is short and app-friendly. Website is a detailed original article with headings and paragraphs. SEO includes title, description, focus_keyword, keywords, slug, og_title, og_description, image_alt.\n\nSOURCE RECORDS:\n" . $payload;
    }

    private function responseTokens(array $response): int
    {
        return (int) ($response['usage']['input_tokens'] ?? 0) + (int) ($response['usage']['output_tokens'] ?? 0);
    }
}
