<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsScraperService
{
    public function syncRecruitmentNews(?string $query = 'Chhattisgarh recruitment'): array
    {
        $newsDataKey = config('services.newsdata.key') ?: env('NEWSDATA_KEY');
        $newsApiKey = config('services.newsapi.key') ?: env('NEWSAPI_KEY');
        $importedCount = 0;
        $articles = [];

        if ($newsDataKey) {
            try {
                $response = Http::timeout(15)->get('https://newsdata.io/api/1/news', [
                    'apikey' => $newsDataKey, 'q' => $query, 'country' => 'in', 'language' => 'en',
                ]);
                if ($response->successful() && isset($response['results'])) {
                    foreach ($response['results'] as $item) {
                        $articles[] = [
                            'title' => $item['title'] ?? '', 'summary' => $item['description'] ?? ($item['title'] ?? ''),
                            'source' => $item['source_id'] ?? 'NewsData', 'source_url' => $item['link'] ?? null,
                            'image_url' => $item['image_url'] ?? null, 'category' => $this->detectCategory($item['title'] ?? ''),
                        ];
                    }
                }
            } catch (\Throwable $e) { Log::warning('NewsData.io sync failed: '.$e->getMessage()); }
        }

        if (!$articles && $newsApiKey) {
            try {
                $response = Http::timeout(15)->get('https://newsapi.org/v2/everything', [
                    'apiKey' => $newsApiKey, 'q' => $query, 'language' => 'en', 'sortBy' => 'publishedAt', 'pageSize' => 15,
                ]);
                if ($response->successful() && isset($response['articles'])) {
                    foreach ($response['articles'] as $item) {
                        $articles[] = [
                            'title' => $item['title'] ?? '', 'summary' => $item['description'] ?? ($item['title'] ?? ''),
                            'source' => $item['source']['name'] ?? 'NewsAPI', 'source_url' => $item['url'] ?? null,
                            'image_url' => $item['urlToImage'] ?? null, 'category' => $this->detectCategory($item['title'] ?? ''),
                        ];
                    }
                }
            } catch (\Throwable $e) { Log::warning('NewsAPI sync failed: '.$e->getMessage()); }
        }

        $translator = app(TranslationService::class);
        foreach ($articles as $art) {
            if (!$art['title']) continue;
            if (Job::where('title_en', $art['title'])->orWhere('title', $art['title'])->exists()) continue;

            $job = Job::create([
                'title' => $art['title'], 'title_en' => $art['title'],
                'summary' => $art['summary'], 'summary_en' => $art['summary'],
                'detailed_content' => $art['summary'], 'detailed_content_en' => $art['summary'],
                'category' => $art['category'], 'category_en' => $art['category'],
                'source' => $art['source'], 'source_url' => $art['source_url'], 'image_url' => $art['image_url'],
                'published_at' => date('Y-m-d'), 'relative_time' => 'हाल ही में', 'relative_time_en' => 'Recently',
                'is_breaking' => false, 'is_new' => true, 'application_start' => 'जारी', 'last_date' => 'शीघ्र',
                'translation_status' => 'pending',
            ]);

            try {
                $t = $translator->translateMany([
                    'title' => $job->title_en, 'summary' => $job->summary_en,
                    'detailed' => $job->detailed_content_en, 'category' => $job->category_en,
                ]);
                $job->update([
                    'title' => $t['title'] ?: $job->title_en, 'summary' => $t['summary'] ?: $job->summary_en,
                    'detailed_content' => $t['detailed'] ?: $job->detailed_content_en, 'category' => $t['category'] ?: $job->category_en,
                    'translation_status' => $t['title'] ? 'ready' : 'pending', 'translation_error' => $t['title'] ? null : 'Translation API unavailable',
                    'translated_at' => $t['title'] ? now() : null,
                ]);
            } catch (\Throwable $e) { Log::warning('Auto translation failed: '.$e->getMessage()); }
            $importedCount++;
        }

        return [
            'success' => true, 'imported' => $importedCount, 'total_fetched' => count($articles),
            'message' => $importedCount > 0 ? "{$importedCount} नए रोजगार व भर्ती समाचार आयात किए गए और Hindi translation queue/processing शुरू हुआ।" : 'कोई नया लेख नहीं मिला या डेटाबेस पहले से अपडेटेड है।'
        ];
    }

    private function detectCategory(string $title): string
    {
        $t = mb_strtolower($title);
        if (str_contains($t, 'police') || str_contains($t, 'sub inspector')) return 'CG Police';
        if (str_contains($t, 'vyapam') || str_contains($t, 'peon') || str_contains($t, 'tet')) return 'CG Vyapam';
        if (str_contains($t, 'psc') || str_contains($t, 'state service')) return 'CGPSC';
        if (str_contains($t, 'teacher') || str_contains($t, 'school') || str_contains($t, 'education')) return 'CG Education';
        if (str_contains($t, 'health') || str_contains($t, 'doctor') || str_contains($t, 'nurse')) return 'CG Health';
        return 'CG Vyapam';
    }
}
