<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsIngestionService
{
    private const NEWS_API_BASE = 'https://saurav.tech/NewsAPI';
    private const DEFAULT_QUERY = 'Chhattisgarh latest news';

    public function ingestAndProcess(?string $query = null, int $limit = 20, bool $process = true, array $filters = []): array
    {
        $query = trim((string) $query);
        $limit = min(max($limit, 1), 100);
        $filters = array_merge([
            'topic' => null,
            'geography' => 'chhattisgarh',
            'source' => 'all',
            'from' => null,
            'to' => null,
        ], $filters);

        $articles = $this->fetch($query ?: self::DEFAULT_QUERY, $limit, $filters);
        $created = [];
        foreach ($articles as $article) {
            $title = trim((string) ($article['title'] ?? ''));
            $url = trim((string) ($article['url'] ?? ''));
            $description = trim((string) ($article['description'] ?? ''));
            if ($title === '' || $description === '') continue;

            $duplicate = News::query()->where(function ($q) use ($url, $title) {
                $q->whereRaw('LOWER(title) = ?', [Str::lower($title)]);
                if ($url !== '') $q->orWhere('original_url', $url)->orWhere('source_url', $url);
            })->exists();
            if ($duplicate) continue;

            $news = News::create([
                'title' => $title,
                'title_en' => $title,
                'summary' => $description,
                'summary_en' => $description,
                'content' => $article['content'] ?: $description,
                'content_en' => $article['content'] ?: $description,
                'source' => $article['source'] ?? 'Unknown',
                'source_url' => $url ?: null,
                'original_url' => $url ?: null,
                'image_url' => $article['image'] ?? null,
                'category' => $this->category($title . ' ' . $description),
                'category_en' => $this->category($title . ' ' . $description),
                'tags' => $this->tags($title . ' ' . $description),
                'language' => 'en',
                'published_at' => $article['published_at'] ?? now(),
                'exam_relevance' => $this->relevance($title . ' ' . $description),
                'status' => 'draft',
            ]);
            $created[] = $news;
        }

        $processed = 0;
        if ($process && $created) {
            $engine = app(\App\Services\AI\NewsContentEngine::class);
            $processed = count($engine->process(collect($created)->map(fn ($news) => $engine->buildNews($news))->all()));
        }

        return [
            'fetched' => count($articles),
            'created' => count($created),
            'processed' => $processed,
            'ids' => collect($created)->pluck('id')->all(),
        ];
    }

    private function fetch(string $query, int $limit, array $filters = []): array
    {
        $source = $filters['source'] ?? 'all';
        $geography = $filters['geography'] ?? 'chhattisgarh';
        $topic = trim((string) ($filters['topic'] ?? ''));
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;
        $results = [];

        // SauravKanchan/NewsAPI is a static, no-key mirror of NewsAPI.org data.
        // The documented interface exposes:
        //   /top-headlines/category/{category}/{country}.json
        //   /everything/{source_id}.json
        // and returns NewsAPI-shaped article objects including description,
        // url, urlToImage, publishedAt and content.
        if (in_array($source, ['all', 'newsapi'], true)) {
            $results = array_merge($results, $this->fetchDocumentedNewsApi($limit, $geography, $topic, $from, $to));
        }

        // Google Trending remains an optional independent source. Google News
        // RSS is deliberately not part of the primary NewsAPI path.
        if ($source === 'google_trending') {
            $results = $this->fetchGoogleTrending($limit, $geography);
        }

        if (in_array($source, ['all', 'google_news'], true) && count($results) < $limit) {
            // Only use the RSS fallback for the remainder. Never let it remove
            // structured NewsAPI results already obtained.
            $rssFilters = $filters;
            $rssFilters['query'] = $query;
            $rssFilters['strict_geo'] = false;
            $rss = app(NewsRssFallback::class)->fetch($query, $limit - count($results), $rssFilters);
            $results = array_merge($results, $rss);
        }

        return $this->uniqueArticles($results, $limit);
    }

    private function fetchDocumentedNewsApi(int $limit, string $geography, string $topic, $from, $to): array
    {
        $country = match ($geography) {
            'india', 'chhattisgarh' => 'in',
            'world' => 'us',
            default => 'in',
        };
        $categories = $this->categoriesForTopic($topic);
        $articles = [];

        foreach ($categories as $category) {
            if (count($articles) >= $limit) break;
            $url = self::NEWS_API_BASE . '/top-headlines/category/' . $category . '/' . $country . '.json';
            try {
                $response = Http::timeout(12)->get($url);
                if (!$response->successful()) continue;
                foreach (($response->json('articles') ?: []) as $item) {
                    $article = $this->normalizeNewsApiArticle($item);
                    if (!$article) continue;
                    if (!$this->passesDateFilter($article['published_at'] ?? null, $from, $to)) continue;
                    if (!$this->passesTopic($article, $topic)) continue;
                    if ($geography === 'chhattisgarh' && !$this->passesChhattisgarh($article)) continue;
                    $articles[] = $article;
                    if (count($articles) >= $limit) break;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        // The documented mirror exposes source-specific /everything endpoints.
        // Use Google News as the default source for a broader all-category fill,
        // but only after category endpoints have been exhausted.
        if (count($articles) < $limit && $topic === '') {
            try {
                $url = self::NEWS_API_BASE . '/everything/google-news.json';
                $response = Http::timeout(12)->get($url);
                if ($response->successful()) {
                    foreach (($response->json('articles') ?: []) as $item) {
                        $article = $this->normalizeNewsApiArticle($item);
                        if (!$article || !$this->passesDateFilter($article['published_at'] ?? null, $from, $to)) continue;
                        if ($geography === 'chhattisgarh' && !$this->passesChhattisgarh($article)) continue;
                        $articles[] = $article;
                        if (count($articles) >= $limit) break;
                    }
                }
            } catch (\Throwable) {
            }
        }

        return $articles;
    }

    private function normalizeNewsApiArticle(array $item): ?array
    {
        $title = trim((string) ($item['title'] ?? ''));
        $description = trim((string) ($item['description'] ?? ''));
        $url = trim((string) ($item['url'] ?? ''));
        if ($title === '' || $description === '' || $url === '') return null;

        // NewsAPI's own provider data is authoritative. Do not overwrite a
        // supplied urlToImage with Google/RSS placeholders.
        $image = trim((string) ($item['urlToImage'] ?? '')) ?: null;
        if ($this->isBadImage($image)) $image = null;

        $source = $item['source']['name'] ?? null;
        if (!$source) $source = parse_url($url, PHP_URL_HOST) ?: 'NewsAPI';

        return [
            'title' => preg_replace('/\s+-\s+(?:[A-Za-z0-9.-]+\.[A-Za-z]{2,}|[^-]{2,40})$/u', '', $title) ?: $title,
            'description' => $description,
            'content' => trim((string) ($item['content'] ?? '')) ?: null,
            'source' => $source,
            'url' => $url,
            'image' => $image,
            'published_at' => $item['publishedAt'] ?? null,
        ];
    }

    private function categoriesForTopic(string $topic): array
    {
        return match (strtolower($topic)) {
            'economy', 'economy & banking' => ['business'],
            'science', 'science & technology' => ['science', 'technology'],
            'sports' => ['sports'],
            'education' => ['general'],
            'defence', 'polity', 'polity & governance', 'national', 'chhattisgarh', 'cgpsc', 'cg_vyapam', '' => ['general'],
            'environment' => ['science', 'general'],
            'health' => ['health'],
            'entertainment' => ['entertainment'],
            default => ['general'],
        };
    }

    private function passesTopic(array $article, string $topic): bool
    {
        $topic = strtolower(trim($topic));
        if ($topic === '' || in_array($topic, ['national', 'chhattisgarh'], true)) return true;
        $text = Str::lower(($article['title'] ?? '') . ' ' . ($article['description'] ?? ''));
        $keywords = match ($topic) {
            'cgpsc' => ['cgpsc', 'psc', 'state service'],
            'cg_vyapam' => ['vyapam', 'cg vyapam', 'teacher eligibility'],
            'economy', 'economy & banking' => ['economy', 'bank', 'rbi', 'inflation', 'budget', 'gdp', 'finance', 'market'],
            'environment' => ['environment', 'climate', 'forest', 'wildlife', 'pollution', 'biodiversity'],
            'science', 'science & technology' => ['science', 'technology', 'ai', 'isro', 'space', 'research'],
            'defence' => ['defence', 'army', 'navy', 'air force', 'military', 'missile'],
            'polity', 'polity & governance' => ['government', 'governance', 'parliament', 'minister', 'cabinet', 'policy', 'scheme'],
            'education' => ['education', 'school', 'teacher', 'university', 'college', 'exam'],
            'sports' => ['sports', 'cricket', 'football', 'hockey', 'olympic', 'medal'],
            'awards' => ['award', 'appointed', 'appointment', 'honour'],
            'reports' => ['report', 'index', 'ranking', 'survey'],
            'international' => ['international', 'global', 'world', 'united nations', 'china', 'usa', 'russia'],
            default => [],
        };
        return $keywords === [] || Str::contains($text, $keywords);
    }

    private function passesChhattisgarh(array $article): bool
    {
        $text = Str::lower(($article['title'] ?? '') . ' ' . ($article['description'] ?? ''));
        return Str::contains($text, ['chhattisgarh', 'raipur', 'bilaspur', 'durg', 'bastar', 'korba', 'jagdalpur', 'bhilai', 'ambikapur', 'rajnandgaon', 'surguja', 'kondagaon', 'kanker', 'dhamtari', 'mahasamund', 'balod', 'janjgir', 'mungeli', 'gariaband', 'sukma', 'dantewada', 'narayanpur']);
    }

    private function passesDateFilter(?string $publishedAt, $from, $to): bool
    {
        if (!$from && !$to) return true;
        if (!$publishedAt) return false;
        $timestamp = strtotime($publishedAt);
        if ($timestamp === false) return false;
        if ($from && $timestamp < strtotime(substr((string) $from, 0, 10) . ' 00:00:00')) return false;
        if ($to && $timestamp > strtotime(substr((string) $to, 0, 10) . ' 23:59:59')) return false;
        return true;
    }

    private function fetchGoogleTrending(int $limit, string $geography): array
    {
        return app(NewsRssFallback::class)->fetch('', $limit, [
            'source' => 'google_trending',
            'geography' => $geography,
            'strict_geo' => false,
        ]);
    }

    private function uniqueArticles(array $articles, int $limit): array
    {
        $seenUrls = $seenTitles = [];
        $unique = [];
        foreach ($articles as $article) {
            $title = Str::lower(trim((string) ($article['title'] ?? '')));
            $url = Str::lower(trim((string) ($article['url'] ?? '')));
            if ($title === '' || isset($seenTitles[$title]) || ($url !== '' && isset($seenUrls[$url]))) continue;
            $seenTitles[$title] = true;
            if ($url !== '') $seenUrls[$url] = true;
            $unique[] = $article;
            if (count($unique) >= $limit) break;
        }
        return $unique;
    }

    private function isBadImage(?string $url): bool
    {
        $url = trim((string) $url);
        if ($url === '') return true;
        $host = Str::lower((string) parse_url($url, PHP_URL_HOST));
        foreach (['googleusercontent.com', 'gstatic.com', 'news.google.com', 'google.com', 'google.co.in', 'google.co.uk'] as $blocked) {
            if ($host === $blocked || Str::endsWith($host, '.' . $blocked)) return true;
        }
        return false;
    }

    private function category(string $text): string
    {
        $t = Str::lower($text);
        $map = [
            'Environment & Ecology' => ['environment', 'climate', 'forest', 'wildlife', 'pollution', 'biodiversity'],
            'Economy & Banking' => ['economy', 'bank', 'rbi', 'inflation', 'budget', 'gdp', 'market', 'finance', 'rupee', 'tax', 'investment'],
            'Science & Technology' => ['science', 'technology', 'ai ', 'artificial intelligence', 'space', 'isro', 'research', 'digital'],
            'Defence' => ['defence', 'army', 'navy', 'air force', 'missile', 'military'],
            'Sports' => ['sport', 'cricket', 'football', 'hockey', 'olympic', 'medal'],
            'Awards & Appointments' => ['award', 'appointed', 'appointment', 'chairman', 'president', 'director', 'honour'],
            'Reports & Indexes' => ['report', 'index', 'ranking', 'ranked', 'survey'],
            'Polity & Governance' => ['government', 'governance', 'parliament', 'minister', 'cabinet', 'election', 'policy', 'scheme'],
            'International' => ['international', 'united nations', 'usa', 'china', 'russia', 'ukraine', 'global', 'world'],
            'CGPSC' => ['cgpsc', 'state service'],
            'CG Education' => ['education', 'school', 'teacher', 'university', 'college', 'tet'],
            'CG Health' => ['health', 'hospital', 'doctor', 'nurse', 'medical'],
            'CG Police' => ['police', 'constable', 'sub inspector'],
            'Chhattisgarh' => ['chhattisgarh', 'raipur', 'bilaspur', 'durg', 'bastar', 'korba'],
            'India' => ['india', 'indian', 'new delhi'],
        ];
        foreach ($map as $category => $keywords) if (Str::contains($t, $keywords)) return $category;
        return 'Current Affairs';
    }

    private function relevance(string $text): int
    {
        return Str::contains(Str::lower($text), ['cgpsc', 'vyapam', 'chhattisgarh', 'government job', 'recruitment', 'exam', 'teacher', 'police', 'scheme']) ? 5 : 3;
    }

    private function tags(string $text): array
    {
        $tags = ['Current Affairs'];
        $t = Str::lower($text);
        foreach (['CGPSC', 'CG Vyapam', 'Chhattisgarh', 'India', 'International', 'Economy', 'Environment', 'Science & Technology', 'Defence', 'Education', 'Police', 'Health', 'Government Schemes'] as $tag) if (Str::contains($t, Str::lower($tag))) $tags[] = $tag;
        return array_values(array_unique($tags));
    }
}
