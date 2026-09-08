<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Support\Str;

class NewsIngestionService
{
    public function ingestAndProcess(?string $query = null, int $limit = 20, bool $process = true, array $filters = []): array
    {
        $limit = min(max($limit, 1), 100);
        $filters = array_merge(['topic'=>null,'geography'=>'chhattisgarh','source'=>'all','from'=>null,'to'=>null], $filters);
        $articles = app(NewsProviderEngine::class)->fetch(trim((string)$query), $limit, $filters);
        $created = [];
        foreach ($articles as $article) {
            $title = trim((string)($article['title'] ?? ''));
            $description = trim((string)($article['description'] ?? ''));
            $url = trim((string)($article['url'] ?? ''));
            if ($title === '' || $description === '' || $url === '') continue;
            $duplicate = News::query()->where(function($q) use ($title,$url) {
                $q->whereRaw('LOWER(title) = ?', [Str::lower($title)]);
                $q->orWhere('original_url',$url)->orWhere('source_url',$url);
            })->exists();
            if ($duplicate) continue;
            $text = $title.' '.$description;
            $category = $this->category($text);
            $created[] = News::create([
                'title'=>$title,'title_en'=>$title,'summary'=>$description,'summary_en'=>$description,
                'content'=>$article['content'] ?: $description,'content_en'=>$article['content'] ?: $description,
                'source'=>$article['source'] ?? 'Unknown','source_url'=>$url,'original_url'=>$url,
                'image_url'=>$article['image'] ?? null,'category'=>$category,'category_en'=>$category,
                'tags'=>$this->tags($text),'language'=>'en','published_at'=>$article['published_at'] ?? now(),
                'exam_relevance'=>$this->relevance($text),'status'=>'draft',
            ]);
        }
        $processed = 0;
        if ($process && $created) {
            $engine = app(\App\Services\AI\NewsContentEngine::class);
            $processed = count($engine->process(collect($created)->map(fn($news)=>$engine->buildNews($news))->all()));
        }
        return ['fetched'=>count($articles),'created'=>count($created),'processed'=>$processed,'ids'=>collect($created)->pluck('id')->all()];
    }

    private function category(string $text): string
    {
        $t=Str::lower($text);$map=['Environment & Ecology'=>['environment','climate','forest','wildlife','pollution'],'Economy & Banking'=>['economy','bank','rbi','inflation','budget','gdp','finance','market'],'Science & Technology'=>['science','technology','artificial intelligence','space','isro','research'],'Defence'=>['defence','army','navy','air force','missile','military'],'Sports'=>['sport','cricket','football','hockey','olympic','medal'],'Awards & Appointments'=>['award','appointed','appointment','honour'],'Reports & Indexes'=>['report','index','ranking','survey'],'Polity & Governance'=>['government','governance','parliament','minister','cabinet','policy','scheme'],'International'=>['international','united nations','usa','china','russia','global','world'],'CGPSC'=>['cgpsc','state service'],'CG Education'=>['education','school','teacher','university','college'],'CG Health'=>['health','hospital','doctor','nurse','medical'],'CG Police'=>['police','constable','sub inspector'],'Chhattisgarh'=>['chhattisgarh','raipur','bilaspur','durg','bastar','korba'],'India'=>['india','indian','new delhi']];foreach($map as $name=>$words)if(Str::contains($t,$words))return $name;return 'Current Affairs';
    }
    private function relevance(string $text): int { return Str::contains(Str::lower($text),['cgpsc','vyapam','chhattisgarh','recruitment','exam','teacher','police','scheme'])?5:3; }
    private function tags(string $text): array { $tags=['Current Affairs'];$t=Str::lower($text);foreach(['CGPSC','CG Vyapam','Chhattisgarh','India','International','Economy','Environment','Science & Technology','Defence','Education','Police','Health','Government Schemes'] as $tag)if(Str::contains($t,Str::lower($tag)))$tags[]=$tag;return array_values(array_unique($tags)); }
}
