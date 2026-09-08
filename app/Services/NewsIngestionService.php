<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsIngestionService
{
    private const DEFAULT_QUERY = 'Chhattisgarh latest news OR CGPSC OR CG Vyapam OR Chhattisgarh government';

    public function ingestAndProcess(?string $query = null, int $limit = 20, bool $process = true, array $filters = []): array
    {
        $query = trim((string) $query);
        $limit = min(max($limit, 1), 100);
        $articles = $this->fetch($query ?: self::DEFAULT_QUERY, $limit, $filters);
        $created = [];

        foreach ($articles as $article) {
            $title = trim((string) ($article['title'] ?? ''));
            if ($title === '') continue;

            $url = trim((string) ($article['url'] ?? ''));
            $duplicateQuery = News::query()->where(function ($q) use ($url, $title) {
                $q->whereRaw('LOWER(title) = ?', [Str::lower($title)]);
                if ($url !== '') $q->orWhere('original_url', $url)->orWhere('source_url', $url);
            });
            if ($duplicateQuery->exists()) continue;

            $description = trim((string) ($article['description'] ?? '')) ?: $title;
            $category = $this->category($title . ' ' . $description);
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
                'category' => $category,
                'category_en' => $category,
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
            $engine = app(\App\Services\AI\ContentEngine::class);
            $processed = count($engine->process(collect($created)->map(fn ($news) => $engine->buildNews($news))->all()));
        }

        return ['fetched' => count($articles), 'created' => count($created), 'processed' => $processed, 'ids' => collect($created)->pluck('id')->all()];
    }

    private function fetch(string $query, int $limit, array $filters = []): array
    {
        $out = [];
        $source = $filters['source'] ?? 'all';
        $geography = $filters['geography'] ?? 'chhattisgarh';
        $topic = trim((string) ($filters['topic'] ?? ''));
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $locationTerms = [
            'chhattisgarh' => 'Chhattisgarh',
            'india' => 'India',
            'world' => 'world international',
        ];
        $location = $locationTerms[$geography] ?? 'Chhattisgarh';
        $searchQuery = trim($query . ' ' . $location . ' ' . $topic);
        if ($searchQuery === '') $searchQuery = $location . ' ' . ($topic ?: 'latest news');

        if (in_array($source, ['all', 'newsdata'], true) && ($key = config('services.newsdata.key'))) {
            try {
                $params = ['apikey' => $key, 'q' => $searchQuery, 'language' => 'en', 'size' => min($limit, 10)];
                if ($geography === 'india') $params['country'] = 'in';
                if ($from) $params['from_date'] = $from;
                if ($to) $params['to_date'] = $to;
                $r = Http::timeout(20)->get('https://newsdata.io/api/1/news', $params);
                if ($r->successful()) foreach (($r->json('results') ?: []) as $item) {
                    $out[] = ['title'=>$item['title']??'', 'description'=>$item['description']??'', 'source'=>$item['source_id']??'NewsData', 'url'=>$item['link']??'', 'image'=>$item['image_url']??null, 'published_at'=>$item['pubDate']??null];
                }
            } catch (\Throwable) {}
        }

        if (count($out) < $limit && in_array($source, ['all', 'newsapi'], true) && ($key = config('services.newsapi.key'))) {
            try {
                $params = ['apiKey'=>$key, 'q'=>$searchQuery, 'language'=>'en', 'sortBy'=>'publishedAt', 'pageSize'=>min($limit,100)];
                if ($from) $params['from'] = $from;
                if ($to) $params['to'] = $to;
                $r = Http::timeout(20)->get('https://newsapi.org/v2/everything', $params);
                if ($r->successful()) foreach (($r->json('articles') ?: []) as $item) {
                    $out[] = ['title'=>$item['title']??'', 'description'=>$item['description']??'', 'source'=>$item['source']['name']??'NewsAPI', 'url'=>$item['url']??'', 'image'=>$item['urlToImage']??null, 'published_at'=>$item['publishedAt']??null];
                }
            } catch (\Throwable) {}
        }

        if (count($out) < $limit && in_array($source, ['all', 'google_trending', 'google_news'], true)) {
            $rssFilters = $filters;
            $rssFilters['query'] = $searchQuery;
            $rssFilters['from'] = $from;
            $rssFilters['to'] = $to;
            $rss = app(NewsRssFallback::class)->fetch($searchQuery, $limit - count($out), $rssFilters);
            $out = array_merge($out, $rss);
        }

        $seenUrls = $seenTitles = []; $unique = [];
        foreach ($out as $article) {
            $title = Str::lower(trim((string) ($article['title'] ?? '')));
            $url = Str::lower(trim((string) ($article['url'] ?? '')));
            if ($title === '' || isset($seenTitles[$title]) || ($url !== '' && isset($seenUrls[$url]))) continue;
            if ($url !== '') $seenUrls[$url] = true;
            $seenTitles[$title] = true; $unique[] = $article;
        }
        return array_slice($unique, 0, $limit);
    }

    private function category(string $text): string
    {
        $t = Str::lower($text);
        $map = [
            'Environment & Ecology'=>['environment','climate','forest','wildlife','pollution','river','biodiversity','tiger','elephant'],
            'Economy & Banking'=>['economy','bank','rbi','inflation','budget','gdp','market','finance','rupee','tax','investment'],
            'Science & Technology'=>['science','technology','ai ','artificial intelligence','space','isro','research','digital'],
            'Defence'=>['defence','army','navy','air force','missile','military'],
            'Sports'=>['sport','cricket','football','hockey','olympic','medal'],
            'Awards & Appointments'=>['award','appointed','appointment','chairman','president','director','honour'],
            'Reports & Indexes'=>['report','index','ranking','ranked','survey'],
            'Polity & Governance'=>['government','governance','parliament','minister','cabinet','election','policy','scheme'],
            'International'=>['international','united nations','un','usa','china','russia','ukraine','global','world'],
            'CGPSC'=>['cgpsc','state service'],
            'CG Education'=>['education','school','teacher','university','college','tet'],
            'CG Health'=>['health','hospital','doctor','nurse','medical'],
            'CG Police'=>['police','constable','sub inspector'],
            'Chhattisgarh'=>['chhattisgarh','raipur','bilaspur','durg','bastar','korba'],
            'India'=>['india','indian','new delhi'],
        ];
        foreach ($map as $category => $keywords) if (Str::contains($t, $keywords)) return $category;
        return 'Current Affairs';
    }

    private function relevance(string $text): int
    {
        $t = Str::lower($text);
        return Str::contains($t, ['cgpsc','vyapam','chhattisgarh','government job','recruitment','exam','teacher','police','scheme']) ? 5 : 3;
    }

    private function tags(string $text): array
    {
        $tags = ['Current Affairs']; $t = Str::lower($text);
        foreach (['CGPSC','CG Vyapam','Chhattisgarh','India','International','Economy','Environment','Science & Technology','Defence','Education','Police','Health','Government Schemes'] as $tag) if (Str::contains($t, Str::lower($tag))) $tags[] = $tag;
        return array_values(array_unique($tags));
    }
}
