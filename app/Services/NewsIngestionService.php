<?php

namespace App\Services;

use App\Models\News;
use App\Services\AI\ContentEngine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsIngestionService
{
    private const DEFAULT_QUERY = 'Chhattisgarh OR CGPSC OR CG Vyapam OR Chhattisgarh government';

    public function ingestAndProcess(?string $query = null, int $limit = 20, bool $process = true): array
    {
        $query = trim((string) $query) ?: self::DEFAULT_QUERY;
        $limit = min(max($limit, 1), 100);
        $articles = $this->fetch($query, $limit);
        $created = [];

        foreach ($articles as $article) {
            $title = trim((string) ($article['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $url = trim((string) ($article['url'] ?? ''));
            $duplicateQuery = News::query()->where(function ($q) use ($url, $title) {
                $q->whereRaw('LOWER(title) = ?', [Str::lower($title)]);
                if ($url !== '') {
                    $q->orWhere('original_url', $url)->orWhere('source_url', $url);
                }
            });

            if ($duplicateQuery->exists()) {
                continue;
            }

            $description = trim((string) ($article['description'] ?? '')) ?: $title;
            $news = News::create([
                'title' => $title,
                'title_en' => $title,
                'summary' => $description,
                'summary_en' => $description,
                'content' => $description,
                'content_en' => $description,
                'source' => $article['source'] ?? 'Unknown',
                'source_url' => $url ?: null,
                'original_url' => $url ?: null,
                'image_url' => $article['image'] ?? null,
                'category' => $this->category($title),
                'category_en' => $this->category($title),
                'tags' => $this->tags($title),
                'language' => 'en',
                'published_at' => $article['published_at'] ?? now(),
                'exam_relevance' => $this->relevance($title),
                'status' => 'draft',
            ]);
            $created[] = $news;
        }

        $processed = 0;
        if ($process && $created) {
            $engine = app(ContentEngine::class);
            $processed = count($engine->process(
                collect($created)->map(fn ($news) => $engine->buildNews($news))->all()
            ));
        }

        return [
            'fetched' => count($articles),
            'created' => count($created),
            'processed' => $processed,
            'ids' => collect($created)->pluck('id')->all(),
        ];
    }

    private function fetch(string $query, int $limit): array
    {
        $out = [];
        $newsDataKey = config('services.newsdata.key');

        if ($newsDataKey) {
            try {
                $r = Http::timeout(20)->get('https://newsdata.io/api/1/news', [
                    'apikey' => $newsDataKey,
                    'q' => $query,
                    'country' => 'in',
                    'language' => 'en',
                    'size' => min($limit, 10),
                ]);
                if ($r->successful()) {
                    foreach (($r->json('results') ?: []) as $item) {
                        $out[] = [
                            'title' => $item['title'] ?? '',
                            'description' => $item['description'] ?? '',
                            'source' => $item['source_id'] ?? 'NewsData',
                            'url' => $item['link'] ?? '',
                            'image' => $item['image_url'] ?? null,
                            'published_at' => $item['pubDate'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable) {
                // Continue to the next source.
            }
        }

        if (count($out) < $limit && ($newsApiKey = config('services.newsapi.key'))) {
            try {
                $r = Http::timeout(20)->get('https://newsapi.org/v2/everything', [
                    'apiKey' => $newsApiKey,
                    'q' => $query,
                    'language' => 'en',
                    'sortBy' => 'publishedAt',
                    'pageSize' => min($limit, 100),
                ]);
                if ($r->successful()) {
                    foreach (($r->json('articles') ?: []) as $item) {
                        $out[] = [
                            'title' => $item['title'] ?? '',
                            'description' => $item['description'] ?? '',
                            'source' => $item['source']['name'] ?? 'NewsAPI',
                            'url' => $item['url'] ?? '',
                            'image' => $item['urlToImage'] ?? null,
                            'published_at' => $item['publishedAt'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable) {
                // Continue to RSS fallback.
            }
        }

        // RSS is used to fill the requested limit when paid/API sources return too few
        // results. The fallback service searches several focused feeds for the default
        // Chhattisgarh query, so a successful API response of only 2-3 stories does not
        // unnecessarily leave the admin feed almost empty.
        if (count($out) < $limit) {
            $rss = app(NewsRssFallback::class)->fetch($query, $limit - count($out));
            $out = array_merge($out, $rss);
        }

        $seenUrls = [];
        $seenTitles = [];
        $unique = [];
        foreach ($out as $article) {
            $title = Str::lower(trim((string) ($article['title'] ?? '')));
            $url = Str::lower(trim((string) ($article['url'] ?? '')));
            if ($title === '') {
                continue;
            }
            if (isset($seenTitles[$title])) {
                continue;
            }
            if ($url !== '' && isset($seenUrls[$url])) {
                continue;
            }
            if ($url !== '') {
                $seenUrls[$url] = true;
            }
            $seenTitles[$title] = true;
            $unique[] = $article;
        }

        return array_slice($unique, 0, $limit);
    }

    private function category(string $title): string
    {
        $t = Str::lower($title);
        if (Str::contains($t, ['police', 'constable', 'sub inspector'])) return 'CG Police';
        if (Str::contains($t, ['psc', 'state service'])) return 'CGPSC';
        if (Str::contains($t, ['teacher', 'school', 'education', 'tet'])) return 'CG Education';
        if (Str::contains($t, ['health', 'doctor', 'nurse', 'medical'])) return 'CG Health';
        return 'Current Affairs';
    }

    private function relevance(string $title): int
    {
        $t = Str::lower($title);
        return Str::contains($t, ['cgpsc', 'vyapam', 'chhattisgarh', 'government job', 'recruitment', 'exam', 'teacher', 'police']) ? 5 : 3;
    }

    private function tags(string $title): array
    {
        $tags = ['Current Affairs'];
        $t = Str::lower($title);
        foreach (['CGPSC', 'CG Vyapam', 'Chhattisgarh', 'Recruitment', 'Government Jobs', 'Education', 'Police', 'Health'] as $tag) {
            if (Str::contains($t, Str::lower($tag))) $tags[] = $tag;
        }
        return array_values(array_unique($tags));
    }
}
