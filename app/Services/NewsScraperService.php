<?php

namespace App\Services;

use App\Models\Job;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsScraperService
{
    /**
     * Fetch news from NewsData.io or NewsAPI.org
     */
    public function syncRecruitmentNews(?string $query = 'Chhattisgarh recruitment'): array
    {
        $newsDataKey = config('services.newsdata.key') ?: env('NEWSDATA_KEY');
        $newsApiKey = config('services.newsapi.key') ?: env('NEWSAPI_KEY');

        $importedCount = 0;
        $articles = [];

        // 1. Try NewsData.io
        if (!empty($newsDataKey)) {
            try {
                $response = Http::timeout(15)->get('https://newsdata.io/api/1/news', [
                    'apikey' => $newsDataKey,
                    'q' => $query,
                    'country' => 'in',
                    'language' => 'hi,en',
                ]);

                if ($response->successful() && isset($response['results'])) {
                    foreach ($response['results'] as $item) {
                        $articles[] = [
                            'title' => $item['title'] ?? '',
                            'summary' => $item['description'] ?? ($item['title'] ?? ''),
                            'source' => $item['source_id'] ?? 'NewsData',
                            'source_url' => $item['link'] ?? null,
                            'image_url' => $item['image_url'] ?? null,
                            'category' => $this->detectCategory($item['title'] ?? ''),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("NewsData.io sync failed: " . $e->getMessage());
            }
        }

        // 2. Try NewsAPI.org
        if (empty($articles) && !empty($newsApiKey)) {
            try {
                $response = Http::timeout(15)->get('https://newsapi.org/v2/everything', [
                    'apiKey' => $newsApiKey,
                    'q' => $query,
                    'language' => 'hi',
                    'sortBy' => 'publishedAt',
                    'pageSize' => 15,
                ]);

                if ($response->successful() && isset($response['articles'])) {
                    foreach ($response['articles'] as $item) {
                        $articles[] = [
                            'title' => $item['title'] ?? '',
                            'summary' => $item['description'] ?? ($item['title'] ?? ''),
                            'source' => $item['source']['name'] ?? 'NewsAPI',
                            'source_url' => $item['url'] ?? null,
                            'image_url' => $item['urlToImage'] ?? null,
                            'category' => $this->detectCategory($item['title'] ?? ''),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("NewsAPI sync failed: " . $e->getMessage());
            }
        }

        // Save new articles
        foreach ($articles as $art) {
            if (empty($art['title'])) continue;

            $exists = Job::where('title', $art['title'])->exists();
            if (!$exists) {
                Job::create([
                    'title' => $art['title'],
                    'summary' => $art['summary'],
                    'detailed_content' => $art['summary'],
                    'category' => $art['category'],
                    'source' => $art['source'],
                    'source_url' => $art['source_url'],
                    'image_url' => $art['image_url'],
                    'published_at' => date('Y-m-d'),
                    'relative_time' => 'हाल ही में',
                    'is_breaking' => false,
                    'is_new' => true,
                    'application_start' => 'जारी',
                    'last_date' => 'शीघ्र',
                ]);
                $importedCount++;
            }
        }

        return [
            'success' => true,
            'imported' => $importedCount,
            'total_fetched' => count($articles),
            'message' => $importedCount > 0 
                ? "{$importedCount} नए रोजगार व भर्ती समाचार सफलतापूर्वक आयात किए गए।" 
                : "कोई नया लेख नहीं मिला या डेटाबेस पहले से अपडेटेड है।"
        ];
    }

    private function detectCategory(string $title): string
    {
        $t = mb_strtolower($title);
        if (str_contains($t, 'police') || str_contains($t, 'आरक्षक') || str_contains($t, 'पुलिस') || str_contains($t, 'sub inspector')) {
            return 'CG Police';
        }
        if (str_contains($t, 'vyapam') || str_contains($t, 'व्यापम') || str_contains($t, 'peon') || str_contains($t, 'tet')) {
            return 'CG Vyapam';
        }
        if (str_contains($t, 'psc') || str_contains($t, 'लोक सेवा') || str_contains($t, 'state service')) {
            return 'CGPSC';
        }
        if (str_contains($t, 'teacher') || str_contains($t, 'शिक्षक') || str_contains($t, 'school') || str_contains($t, 'शिक्षा')) {
            return 'CG Education';
        }
        if (str_contains($t, 'health') || str_contains($t, 'स्वास्थ्य') || str_contains($t, 'doctor') || str_contains($t, 'nurse')) {
            return 'CG Health';
        }
        return 'CG Vyapam';
    }
}
