<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsRssFallback
{
    public function fetch(string $query,int $limit,array $filters=[]):array
    {
        $source=$filters['source']??'google_news';$geo=$filters['geography']??'chhattisgarh';$topic=trim((string)($filters['topic']??''));$from=$filters['from']??null;$to=$filters['to']??null;$strictGeo=(bool)($filters['strict_geo']??false);$location=['chhattisgarh'=>'Chhattisgarh','india'=>'India','world'=>'international world'][$geo]??'Chhattisgarh';$articles=[];
        if($source==='google_trending'){$geoCode=$geo==='world'?'US':'IN';$feedUrls=['https://trends.google.com/trending/rss?geo='.$geoCode];}
        else{$queries=[];if($topic){$topicQueries=['national'=>'India latest national news','international'=>'international world latest news','economy'=>'India economy banking finance','environment'=>'India environment climate ecology','science'=>'India science technology ISRO','defence'=>'India defence military','polity'=>'India government polity governance','education'=>'India education schools universities','sports'=>'India sports latest','awards'=>'India awards appointments','reports'=>'India reports indexes rankings','important_days'=>'important days India','chhattisgarh'=>'Chhattisgarh latest news government','cgpsc'=>'CGPSC latest','cg_vyapam'=>'CG Vyapam latest'];$normalizedTopic=strtolower(str_replace([' & ',' '],['_','_'],$topic));$queries[]=$topicQueries[$normalizedTopic]??($location.' '.$topic.' latest news');}else{$queries[]=$query;if(strtolower($query)===strtolower('Chhattisgarh latest news OR CGPSC OR CG Vyapam OR Chhattisgarh government'))$queries=['Chhattisgarh latest news','Chhattisgarh government schemes','CGPSC latest','CG Vyapam latest','Chhattisgarh economy development'];$queries[]=$location.' latest current affairs';}$feedUrls=[];foreach(array_unique($queries)as $q){$datePart=' when:1d';if($from)$datePart.=' after:'.substr($from,0,10);if($to)$datePart.=' before:'.date('Y-m-d',strtotime($to.' +1 day'));$feedUrls[]='https://news.google.com/rss/search?q='.rawurlencode($q.$datePart).'&hl=en-IN&gl=IN&ceid=IN:en';}}
        foreach($feedUrls as $feedUrl){if(count($articles)>=$limit)break;try{$response=Http::timeout(15)->get($feedUrl);if(!$response->successful())continue;$xml=@simplexml_load_string($response->body());if(!$xml||!isset($xml->channel->item))continue;foreach($xml->channel->item as $item){if(count($articles)>=$limit)break;$title=trim(html_entity_decode(strip_tags((string)($item->title??'')),ENT_QUOTES|ENT_HTML5,'UTF-8'));$url=trim((string)($item->link??''));if($title==='')continue;$sourceName=$source==='google_trending'?'Google Trending':'Google News';$description=trim(html_entity_decode(strip_tags((string)($item->description??'')),ENT_QUOTES|ENT_HTML5,'UTF-8'));$description=preg_replace('/\s+/u',' ',str_replace(["\xC2\xA0",'&nbsp;'],' ',$description));$publishedAt=trim((string)($item->pubDate??''))?:null;if(str_contains($title,' - ')){[$cleanTitle,$publisher]=array_pad(explode(' - ',$title,2),2,'');if(trim($publisher)!==''){$title=trim($cleanTitle);$sourceName=trim($publisher);}}if($strictGeo&&!$this->passesGeographyFilter($title.' '.$description,$geo,$topic))continue;$image=isset($item->enclosure['url'])?(string)$item->enclosure['url']:null;
                    $enriched=$this->enrichArticle($url,$title,$description,$image);
                    $articles[]=['title'=>$enriched['title']?:$title,'description'=>$enriched['description']?:$description?:$title,'source'=>$sourceName,'url'=>$enriched['url']?:$url,'image'=>$enriched['image']?:$image,'published_at'=>$publishedAt];
                }}catch(\Throwable){}}
        return $articles;
    }

    /** Fetch the publisher page behind Google News/Trending RSS and recover metadata that RSS often omits. */
    private function enrichArticle(string $url,string $fallbackTitle,string $fallbackDescription,?string $fallbackImage):array
    {
        if($url===''||!Str::startsWith($url,['http://','https://']))return ['url'=>$url,'title'=>$fallbackTitle,'description'=>$fallbackDescription,'image'=>$fallbackImage];
        try{
            $response=Http::timeout(12)->withHeaders(['User-Agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/140 Safari/537.36','Accept'=>'text/html,application/xhtml+xml'])->withOptions(['allow_redirects'=>['max'=>5,'strict'=>false]])->get($url);
            if(!$response->successful())return ['url'=>$url,'title'=>$fallbackTitle,'description'=>$fallbackDescription,'image'=>$fallbackImage];
            $html=(string)$response->body();if($html==='')return ['url'=>$url,'title'=>$fallbackTitle,'description'=>$fallbackDescription,'image'=>$fallbackImage];
            $title=$this->metaValue($html,'property','og:title')?:$this->metaValue($html,'name','twitter:title')?:$fallbackTitle;
            $description=$this->metaValue($html,'property','og:description')?:$this->metaValue($html,'name','description')?:$this->metaValue($html,'name','twitter:description')?:$fallbackDescription;
            $image=$this->metaValue($html,'property','og:image')?:$this->metaValue($html,'name','twitter:image')?:$this->metaValue($html,'itemprop','image')?:$fallbackImage;
            $canonical=$this->linkValue($html,'canonical')?:$url;
            $jsonLd=$this->jsonLdArticle($html);
            if(($description===''||$description===$fallbackDescription)&&!empty($jsonLd['description']))$description=(string)$jsonLd['description'];
            if(($image===''||$image===$fallbackImage)&&!empty($jsonLd['image']))$image=is_array($jsonLd['image'])?(string)($jsonLd['image'][0]??''):(string)$jsonLd['image'];
            if(($title===''||$title===$fallbackTitle)&&!empty($jsonLd['headline']))$title=(string)$jsonLd['headline'];
            $description=$this->cleanText($description);$title=$this->cleanText($title);$image=$this->absoluteUrl($image,$canonical);
            return ['url'=>$canonical,'title'=>$title,'description'=>$description,'image'=>$image];
        }catch(\Throwable){return ['url'=>$url,'title'=>$fallbackTitle,'description'=>$fallbackDescription,'image'=>$fallbackImage];}
    }

    private function metaValue(string $html,string $attribute,string $value):string
    {
        $pattern='/<meta[^>]+'.preg_quote($attribute,'/').'=["\\\']'.preg_quote($value,'/').'["\\\'][^>]+content=["\\\']([^"\\\']*)["\\\'][^>]*>/i';
        if(preg_match($pattern,$html,$m))return trim(html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8'));
        $pattern='/<meta[^>]+content=["\\\']([^"\\\']*)["\\\'][^>]+'.preg_quote($attribute,'/').'=["\\\']'.preg_quote($value,'/').'["\\\'][^>]*>/i';
        return preg_match($pattern,$html,$m)?trim(html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8')):'';
    }

    private function linkValue(string $html,string $rel):string
    {
        $pattern='/<link[^>]+rel=["\\\']'.preg_quote($rel,'/').'["\\\'][^>]+href=["\\\']([^"\\\']+)["\\\'][^>]*>/i';
        if(preg_match($pattern,$html,$m))return trim(html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8'));
        $pattern='/<link[^>]+href=["\\\']([^"\\\']+)["\\\'][^>]+rel=["\\\']'.preg_quote($rel,'/').'["\\\'][^>]*>/i';
        return preg_match($pattern,$html,$m)?trim(html_entity_decode($m[1],ENT_QUOTES|ENT_HTML5,'UTF-8')):'';
    }

    private function jsonLdArticle(string $html):array
    {
        if(!preg_match_all('/<script[^>]+type=["\\\']application\\/ld\\+json["\\\'][^>]*>(.*?)<\\/script>/is',$html,$matches))return [];
        foreach($matches[1] as $raw){$decoded=json_decode(html_entity_decode(trim($raw),ENT_QUOTES|ENT_HTML5,'UTF-8'),true);if(!is_array($decoded))continue;$nodes=isset($decoded[0])?$decoded:[$decoded];foreach($nodes as $node){if(!is_array($node))continue;$type=$node['@type']??'';if(is_array($type))$type=implode(',',array_map('strval',$type));if(Str::contains(Str::lower((string)$type),['newsarticle','article','reportagenewsarticle']))return $node;}}
        return [];
    }

    private function cleanText(?string $value):string
    {
        $value=trim(html_entity_decode(strip_tags((string)$value),ENT_QUOTES|ENT_HTML5,'UTF-8'));return preg_replace('/\s+/u',' ',$value)?:'';
    }

    private function absoluteUrl(?string $url,string $base):?string
    {
        $url=trim((string)$url);if($url==='')return null;if(Str::startsWith($url,['http://','https://']))return $url;if(Str::startsWith($url,'//'))return 'https:'.$url;if(!Str::startsWith($base,['http://','https://']))return $url;$parts=parse_url($base);$origin=($parts['scheme']??'https').'://'.($parts['host']??'');if(Str::startsWith($url,'/'))return $origin.$url;return $origin.rtrim(dirname($parts['path']??'/'),'/').'/'.$url;
    }

    private function passesGeographyFilter(string $text,string $geo,string $topic):bool
    {if($topic!=='')return true;$text=Str::lower($text);if($geo==='chhattisgarh')return Str::contains($text,['chhattisgarh','raipur','bilaspur','durg','bastar','korba','jagdalpur','bhilai','ambikapur','rajnandgaon','surguja','kondagaon','kanker','dhamtari','mahasamund','balod','baloda bazar','janjgir','mungeli','gariaband','sukma','dantewada','narayanpur']);if($geo==='india')return Str::contains($text,['india','indian','new delhi','mumbai','delhi','government of india','rbi','isro','supreme court']);return Str::contains($text,['international','global','world','united nations','usa','china','russia','ukraine','europe','middle east']);}
}
