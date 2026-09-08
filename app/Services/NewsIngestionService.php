<?php

namespace App\Services;

use App\Models\News;
use App\Models\AiContent;
use App\Services\AI\ContentEngine;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsIngestionService
{
    public function ingestAndProcess(?string $query = null, int $limit = 20, bool $process = true): array
    {
        $query = $query ?: 'Chhattisgarh recruitment OR CGPSC OR CG Vyapam OR government jobs';
        $articles = $this->fetch($query, min(max($limit, 1), 100));
        $created = [];

        foreach ($articles as $article) {
            $title = trim((string)($article['title'] ?? ''));
            if ($title === '') continue;
            $url = trim((string)($article['url'] ?? ''));
            $exists = News::query()
                ->when($url !== '', fn($q) => $q->where('original_url', $url)->orWhere('source_url', $url))
                ->orWhereRaw('LOWER(title) = ?', [Str::lower($title)])
                ->exists();
            if ($exists) continue;

            $news = News::create([
                'title' => $title,
                'title_en' => $title,
                'summary' => $article['description'] ?: $title,
                'summary_en' => $article['description'] ?: $title,
                'content' => $article['description'] ?: $title,
                'content_en' => $article['description'] ?: $title,
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
            $processed = count($engine->process(collect($created)->map(fn($news) => $engine->buildNews($news))->all()));
        }

        return ['fetched' => count($articles), 'created' => count($created), 'processed' => $processed, 'ids' => collect($created)->pluck('id')->all()];
    }

    private function fetch(string $query, int $limit): array
    {
        $out = [];
        $newsDataKey = config('services.newsdata.key');
        if ($newsDataKey) {
            try {
                $r = Http::timeout(20)->get('https://newsdata.io/api/1/news', ['apikey'=>$newsDataKey,'q'=>$query,'country'=>'in','language'=>'en']);
                if ($r->successful()) foreach (($r->json('results') ?: []) as $item) $out[] = ['title'=>$item['title'] ?? '','description'=>$item['description'] ?? '','source'=>$item['source_id'] ?? 'NewsData','url'=>$item['link'] ?? '','image'=>$item['image_url'] ?? null,'published_at'=>$item['pubDate'] ?? null];
            } catch (\Throwable) {}
        }
        if (count($out) < $limit && ($newsApiKey = config('services.newsapi.key'))) {
            try {
                $r = Http::timeout(20)->get('https://newsapi.org/v2/everything', ['apiKey'=>$newsApiKey,'q'=>$query,'language'=>'en','sortBy'=>'publishedAt','pageSize'=>$limit]);
                if ($r->successful()) foreach (($r->json('articles') ?: []) as $item) $out[] = ['title'=>$item['title'] ?? '','description'=>$item['description'] ?? '','source'=>$item['source']['name'] ?? 'NewsAPI','url'=>$item['url'] ?? '','image'=>$item['urlToImage'] ?? null,'published_at'=>$item['publishedAt'] ?? null];
            } catch (\Throwable) {}
        }
        return collect($out)->filter(fn($a) => !empty($a['title']))->unique(fn($a) => Str::lower($a['url'] ?: $a['title']))->take($limit)->values()->all();
    }

    private function category(string $title): string
    {
        $t=Str::lower($title);
        if(Str::contains($t,['police','constable','sub inspector'])) return 'CG Police';
        if(Str::contains($t,['psc','state service'])) return 'CGPSC';
        if(Str::contains($t,['teacher','school','education','tet'])) return 'CG Education';
        if(Str::contains($t,['health','doctor','nurse','medical'])) return 'CG Health';
        return 'Current Affairs';
    }

    private function relevance(string $title): int
    {
        $t=Str::lower($title);
        return Str::contains($t,['cgpsc','vyapam','chhattisgarh','government job','recruitment','exam','teacher','police']) ? 5 : 3;
    }

    private function tags(string $title): array
    {
        $tags=['Current Affairs']; $t=Str::lower($title);
        foreach(['CGPSC','CG Vyapam','Chhattisgarh','Recruitment','Government Jobs','Education','Police','Health'] as $tag) if(Str::contains($t,Str::lower($tag))) $tags[]=$tag;
        return array_values(array_unique($tags));
    }
}
