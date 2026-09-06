<?php

namespace App\Services;

use App\Models\JobImport;
use Illuminate\Support\Facades\Http;

class JobImportNormalizer
{
    public function normalize(JobImport $import): JobImport
    {
        if (!$import->external_url) return $import;
        try {
            $html = Http::timeout(20)->retry(2, 500)->withHeaders(['User-Agent'=>'Mozilla/5.0 (compatible; CGJobsBot/1.0)','Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'])->get($import->external_url)->throw()->body();
            [$title, $publisher] = $this->extractTitleAndPublisher($html);
            $content = $this->removeSourceBranding($import->content ?: $import->summary ?: '');
            $summary = $this->removeSourceBranding($import->summary ?: $content);
            $updates=[];
            if($title)$updates['title']=$title;
            if($summary)$updates['summary']=$summary;
            if($content)$updates['content']=$content;
            $updates['published_by']=$import->job_category ?: 'CGSSB';
            if($updates)$import->update($updates);
            if($import->status==='published'&&$import->job){
                $job=$import->job;
                $job->update([
                    'title'=>$updates['title']??$job->title,'title_en'=>$updates['title']??$job->title_en,
                    'summary'=>$updates['summary']??$job->summary,'summary_en'=>$updates['summary']??$job->summary_en,
                    'detailed_content'=>$updates['content']??$job->detailed_content,'detailed_content_en'=>$updates['content']??$job->detailed_content_en,
                    'published_by'=>$updates['published_by']??($job->published_by?:$job->job_category),'source'=>'CGJobs',
                ]);
            }
        } catch(\Throwable $e){report($e);}
        return $import->fresh(['job','source']);
    }

    private function extractTitleAndPublisher(string $html): array
    {
        $dom=new \DOMDocument();libxml_use_internal_errors(true);@$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NOWARNING|LIBXML_NOERROR);libxml_clear_errors();$xpath=new \DOMXPath($dom);$titleCandidates=[];
        foreach($xpath->query('//article//h1 | //main//h1 | //h1') as $node)$titleCandidates[]=$this->clean($node->textContent);
        foreach($xpath->query('//script[@type="application/ld+json"]') as $script){$json=json_decode($script->textContent,true);$this->collectJsonLdHeadlines($json,$titleCandidates);}
        foreach($xpath->query('//meta[@property="og:title"] | //meta[@name="twitter:title"] | //title') as $node)$titleCandidates[]=$this->clean($node->getAttribute('content')?:$node->textContent);
        $title=null;foreach($titleCandidates as $candidate){$candidate=$this->cleanTitle($candidate);if($this->isUsefulTitle($candidate)){$title=$candidate;break;}}
        $body=$this->clean($dom->textContent);$publisher=null;
        if(preg_match('/(?:विभाग\s*का\s*नाम|department\s*name)\s*[:\-]+\s*(.*?)\s*(?:रिक्रूटमेंट\s*बोर्ड|recruitment\s*board|employment\s*type|वेतनमान|official\s*website|आधिकारिक\s*वेबसाइट)/iu',$body,$m))$publisher=$this->clean($m[1]);
        return[$title,$publisher];
    }
    private function collectJsonLdHeadlines($value,array &$out):void{if(!is_array($value))return;if(!empty($value['headline'])&&is_string($value['headline']))$out[]=$this->clean($value['headline']);foreach($value as $child)$this->collectJsonLdHeadlines($child,$out);}
    private function cleanTitle(?string $title):?string{$title=$this->clean((string)$title);if($title==='')return null;$title=preg_replace('/^Jobskind(?:\.com)?\s*[:\-|]\s*/iu','',$title);$title=preg_replace('/\s*[\-|:]\s*Jobskind(?:\.com)?\s*$/iu','',$title);return trim($title);}
    private function isUsefulTitle(?string $title):bool{if(!$title||mb_strlen($title)<12)return false;if(preg_match('/^(jobskind(?:\.com)?|jobs?kind\.com|jobs?kind)$/iu',trim($title)))return false;if(preg_match('/^(home|latest jobs|jobs|recruitment|employment news)$/iu',trim($title)))return false;return(bool)preg_match('/(recruit|bharti|भर्ती|vacancy|पद|notification|नोटिफिकेशन|assistant|teacher|officer|staff|constable|admit|result|internship)/iu',$title);}
    private function removeSourceBranding(string $text):string{$text=preg_replace('/[^.!?\n]*(?:jobskind(?:\.com)?|jobs\s*kind(?:\.com)?)[^.!?\n]*[.!?]?/iu',' ',$text);$text=preg_replace('/©\s*\d{4}[^\n]*/u',' ',$text);$text=preg_replace('/\s{2,}/u',' ',$text);return trim($text);}
    private function clean(string $text):string{$text=html_entity_decode(strip_tags($text),ENT_QUOTES|ENT_HTML5,'UTF-8');return trim(preg_replace('/\s+/u',' ',$text));}
}
