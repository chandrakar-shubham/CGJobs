<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsProviderEngine
{
    private const SAURAV_BASE = 'https://saurav.tech/NewsAPI';

    public function fetch(string $query, int $limit, array $filters): array
    {
        $limit = min(max($limit, 1), 100);
        $source = strtolower((string)($filters['source'] ?? 'all'));
        $results = [];

        if ($source === 'all' || $source === 'newsdata') {
            $results = array_merge($results, $this->newsData($query, $limit, $filters));
        }
        if (($source === 'all' || $source === 'newsapi') && count($results) < $limit) {
            $results = array_merge($results, $this->officialNewsApi($query, $limit - count($results), $filters));
        }
        if (($source === 'saurav_newsapi' || ($source === 'newsapi' && !$results) || ($source === 'all' && count($results) < $limit)) && count($results) < $limit) {
            $results = array_merge($results, $this->sauravMirror($limit - count($results), $filters));
        }
        if ($source === 'google_news' || ($source === 'all' && count($results) < $limit)) {
            $results = array_merge($results, app(NewsRssFallback::class)->fetch($query, $limit - count($results), array_merge($filters, ['strict_geo'=>false])));
        }
        if ($source === 'google_trending') {
            $results = app(NewsRssFallback::class)->fetch('', $limit, ['source'=>'google_trending','geography'=>$filters['geography'] ?? 'india','strict_geo'=>false]);
        }
        return $this->unique($results, $limit);
    }

    private function newsData(string $query, int $limit, array $filters): array
    {
        $key = (string) config('services.newsdata.key');
        if ($key === '') return [];
        $geo = $filters['geography'] ?? 'chhattisgarh';
        $params = ['apikey'=>$key,'language'=>'en','size'=>min($limit,50),'country'=>in_array($geo,['india','chhattisgarh'],true)?'in':'us'];
        if ($query !== '') $params['q'] = $query;
        if ($category = $this->newsDataCategory($filters['topic'] ?? null)) $params['category'] = $category;
        if (!empty($filters['from'])) $params['from_date'] = substr((string)$filters['from'],0,10);
        if (!empty($filters['to'])) $params['to_date'] = substr((string)$filters['to'],0,10);
        try { $r=Http::timeout(15)->get('https://newsdata.io/api/1/latest',$params); return $r->successful()?$this->normalize($r->json('results')?:[],$filters,true):[]; } catch(\Throwable){ return []; }
    }

    private function officialNewsApi(string $query, int $limit, array $filters): array
    {
        $key=(string)config('services.newsapi.key'); if($key==='') return [];
        $geo=$filters['geography']??'chhattisgarh'; $country=in_array($geo,['india','chhattisgarh'],true)?'in':'us';
        $params=['country'=>$country,'pageSize'=>min($limit,100)];
        if($category=$this->newsApiCategory($filters['topic']??null)) $params['category']=$category;
        if($query!=='' && $geo!=='chhattisgarh') $params['q']=$query;
        try {
            $r=Http::timeout(15)->withHeaders(['X-Api-Key'=>$key])->get('https://newsapi.org/v2/top-headlines',$params);
            if($r->successful()) return $this->normalize($r->json('articles')?:[],$filters,true);
            $search=$query!==''?$query:($geo==='chhattisgarh'?'Chhattisgarh':null); if(!$search) return [];
            $p=['q'=>$search,'language'=>'en','sortBy'=>'publishedAt','pageSize'=>min($limit,100)];
            if(!empty($filters['from'])) $p['from']=substr((string)$filters['from'],0,10);
            if(!empty($filters['to'])) $p['to']=substr((string)$filters['to'],0,10);
            $r=Http::timeout(15)->withHeaders(['X-Api-Key'=>$key])->get('https://newsapi.org/v2/everything',$p);
            return $r->successful()?$this->normalize($r->json('articles')?:[],$filters,true):[];
        } catch(\Throwable){ return []; }
    }

    private function sauravMirror(int $limit,array $filters): array
    {
        $geo=$filters['geography']??'india';
        $country=match($geo){'india','chhattisgarh'=>'in','world'=>'us','uk'=>'gb','australia'=>'au','russia'=>'ru','france'=>'fr',default=>'in'};
        $categories=$this->sauravCategories($filters['topic']??null); $out=[];
        foreach($categories as $category){
            if(count($out)>=$limit) break;
            try { $r=Http::timeout(12)->get(self::SAURAV_BASE.'/top-headlines/category/'.$category.'/'.$country.'.json'); if(!$r->successful()) continue; foreach($this->normalize($r->json('articles')?:[],$filters,false) as $a){$out[]=$a;if(count($out)>=$limit)break;} } catch(\Throwable){continue;}
        }
        return $out;
    }

    private function normalize(array $items,array $filters,bool $regional): array
    {
        $out=[];
        foreach($items as $item){
            $title=trim((string)($item['title']??'')); $description=trim((string)($item['description']??'')); $url=trim((string)($item['url']??''));
            if($title===''||$description===''||$url==='') continue;
            $a=['title'=>$this->cleanTitle($title),'description'=>$description,'content'=>trim((string)($item['content']??''))?:null,'source'=>$item['source']['name']??($item['source_id']??(parse_url($url,PHP_URL_HOST)?:'Unknown')),'url'=>$url,'image'=>$this->image($item['urlToImage']??($item['image_url']??($item['image']??null))),'published_at'=>$item['publishedAt']??($item['pubDate']??null)];
            if(!$this->dateOk($a['published_at'],$filters['from']??null,$filters['to']??null)) continue;
            if(!$this->topicOk($a,$filters['topic']??null)) continue;
            if($regional && ($filters['geography']??'')==='chhattisgarh' && !$this->isChhattisgarh($a)) continue;
            $out[]=$a;
        }
        return $out;
    }

    private function topicOk(array $a,?string $topic): bool
    {
        $topic=strtolower(trim((string)$topic)); if($topic===''||in_array($topic,['national','chhattisgarh'],true)) return true;
        $text=Str::lower(($a['title']??'').' '.($a['description']??''));
        $map=['cgpsc'=>['cgpsc','psc','state service'],'cg_vyapam'=>['vyapam','cg vyapam'],'economy'=>['economy','bank','rbi','inflation','budget','gdp','finance','market'],'environment'=>['environment','climate','forest','wildlife','pollution','biodiversity'],'science'=>['science','technology','ai','isro','space','research'],'defence'=>['defence','army','navy','air force','military','missile'],'polity'=>['government','governance','parliament','minister','cabinet','policy','scheme'],'education'=>['education','school','teacher','university','college','exam'],'sports'=>['sports','cricket','football','hockey','olympic','medal'],'awards'=>['award','appointed','appointment','honour'],'reports'=>['report','index','ranking','survey'],'international'=>['international','global','world','united nations','china','usa','russia']];
        return empty($map[$topic])||Str::contains($text,$map[$topic]);
    }

    private function isChhattisgarh(array $a): bool { return Str::contains(Str::lower(($a['title']??'').' '.($a['description']??'')),['chhattisgarh','raipur','bilaspur','durg','bastar','korba','jagdalpur','bhilai','ambikapur','rajnandgaon','surguja','kondagaon','kanker','dhamtari','mahasamund','balod','janjgir','mungeli','gariaband','sukma','dantewada','narayanpur']); }
    private function dateOk(?string $date,$from,$to): bool { if(!$from&&!$to)return true;if(!$date||strtotime($date)===false)return false;$t=strtotime($date);return(!$from||$t>=strtotime(substr((string)$from,0,10).' 00:00:00'))&&(!$to||$t<=strtotime(substr((string)$to,0,10).' 23:59:59')); }
    private function newsApiCategory(?string $topic): ?string { return match(strtolower(trim((string)$topic))){'economy','economy & banking'=>'business','science','science & technology'=>'science','sports'=>'sports','health'=>'health','entertainment'=>'entertainment',default=>'general'}; }
    private function newsDataCategory(?string $topic): ?string { return match(strtolower(trim((string)$topic))){'economy','economy & banking'=>'business','science','science & technology'=>'technology','sports'=>'sports','health'=>'health','entertainment'=>'entertainment','environment'=>'environment','defence'=>'politics',default=>null}; }
    private function sauravCategories(?string $topic): array { return match(strtolower(trim((string)$topic))){'economy','economy & banking'=>['business'],'science','science & technology'=>['science','technology'],'sports'=>['sports'],'health'=>['health'],'entertainment'=>['entertainment'],default=>['general','business','science','technology','sports']}; }
    private function cleanTitle(string $title): string { return preg_replace('/\s+-\s+(?:[A-Za-z0-9.-]+\.[A-Za-z]{2,}|[^-]{2,40})$/u','',$title)?:$title; }
    private function image($url): ?string { $url=trim((string)$url);if($url===''||!Str::startsWith($url,['http://','https://']))return null;$host=Str::lower((string)parse_url($url,PHP_URL_HOST));foreach(['googleusercontent.com','gstatic.com','news.google.com','google.com','google.co.in','google.co.uk'] as $blocked)if($host===$blocked||Str::endsWith($host,'.'.$blocked))return null;return $url; }
    private function unique(array $articles,int $limit): array {$ut=$uu=[];$out=[];foreach($articles as $a){$t=Str::lower(trim((string)($a['title']??'')));$u=Str::lower(trim((string)($a['url']??'')));if($t===''||isset($ut[$t])||($u&&isset($uu[$u])))continue;$ut[$t]=true;if($u)$uu[$u]=true;$out[]=$a;if(count($out)>=$limit)break;}return $out;}
}
