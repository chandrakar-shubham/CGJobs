<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobImport;
use App\Models\JobSource;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobSourceService
{
    public function sync(JobSource $source): array
    {
        $source->update(['last_fetched_at'=>now(),'last_error'=>null]);
        try {
            $html=$this->get($source->fetch_url); $items=$this->parseListing($html,$source); $imported=$skipped=0;
            foreach($items as $item){
                if(empty($item['url'])||empty($item['title'])){$skipped++;continue;}
                if(JobImport::where('job_source_id',$source->id)->where('external_url',$item['url'])->exists()){$skipped++;continue;}
                $detail=$this->parseDetail($this->get($item['url'])); $item=array_merge($item,$detail);
                $import=JobImport::create(['job_source_id'=>$source->id,'external_key'=>sha1($item['url']),'external_url'=>$item['url'],'title'=>Str::limit(trim($item['title']),250,''),'summary'=>$item['summary']??null,'content'=>$item['content']??$item['summary']??null,'category'=>$item['category']??$source->default_category,'image_url'=>$item['image_url']??null,'published_at'=>$item['published_at']??null,'raw_payload'=>$item,'status'=>$source->publish_mode==='auto'?'approved':'pending','fetched_at'=>now()]);
                if($source->publish_mode==='auto')$this->publish($import); $imported++;
            }
            $source->update(['last_success_at'=>now(),'last_items_fetched'=>count($items),'last_items_imported'=>$imported,'last_items_skipped'=>$skipped]);
            return ['fetched'=>count($items),'imported'=>$imported,'skipped'=>$skipped];
        }catch(\Throwable $e){$source->update(['last_error'=>$e->getMessage()]);Log::error('Job source sync failed',['source'=>$source->id,'error'=>$e->getMessage()]);throw $e;}
    }

    public function publish(JobImport $import): Job
    {
        if($import->job_id)return $import->job;
        $existing=Job::where('source_url',$import->external_url)->first();
        if($existing){$import->update(['job_id'=>$existing->id,'status'=>'published','processed_at'=>now()]);return $existing;}
        $title=trim($import->title);$summary=trim($import->summary?:$import->content?:$title);$content=$import->content?:$summary;
        $category=$import->category?:'CG Vyapam';
        $job=Job::create(['title'=>$title,'title_en'=>$title,'summary'=>$summary,'summary_en'=>$summary,'detailed_content'=>$content,'detailed_content_en'=>$content,'category'=>$category,'category_en'=>$category,'section'=>'jobs','post_type'=>'job','source'=>$import->source->name,'source_url'=>$import->external_url,'image_url'=>$import->image_url,'published_at'=>optional($import->published_at)->format('Y-m-d')?:now()->format('Y-m-d'),'relative_time'=>'हाल ही में','relative_time_en'=>'Recently','is_new'=>true,'application_start'=>'जारी','last_date'=>'शीघ्र','translation_status'=>'pending']);
        $import->update(['job_id'=>$job->id,'status'=>'published','processed_at'=>now()]);
        try {
            $t=app(TranslationService::class)->translateMany(['title'=>$title,'summary'=>$summary,'detailed'=>$content,'category'=>$category]);
            if($t['title'])$job->update(['title'=>$t['title'],'summary'=>$t['summary']?:$summary,'detailed_content'=>$t['detailed']?:$content,'category'=>$t['category']?:$category,'translation_status'=>'ready','translation_error'=>null,'translated_at'=>now()]);
        } catch(\Throwable $e){Log::warning('Imported job translation failed',['job'=>$job->id,'error'=>$e->getMessage()]);}
        return $job;
    }

    private function get(string $url): string { return Http::timeout(30)->retry(2,1000)->withHeaders(['User-Agent'=>'Mozilla/5.0 (compatible; CGJobsBot/1.0)'])->get($url)->throw()->body(); }

    private function parseListing(string $html, JobSource $source): array
    {
        $dom=new \DOMDocument();@$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new \DOMXPath($dom);$out=[];
        foreach($xpath->query('//a[@href]') as $a){$title=trim(preg_replace('/\s+/u',' ',$a->textContent));$href=trim($a->getAttribute('href'));if(!$href||str_starts_with($href,'#'))continue;if(str_starts_with($href,'/'))$href=rtrim($source->base_url,'/').$href;if(mb_strlen($title)<12||!filter_var($href,FILTER_VALIDATE_URL)||!str_starts_with($href,rtrim($source->base_url,'/')))continue;if(!preg_match('/(job|recruit|vacancy|bharti|rojgar|notification|recruitment|2026|2025)/iu',$title.' '.$href))continue;$out[$href]=['url'=>$href,'title'=>$title,'summary'=>$title,'category'=>$this->detectCategory($title)];}
        return array_slice(array_values($out),0,30);
    }

    private function parseDetail(string $html): array
    {
        $dom=new \DOMDocument();@$dom->loadHTML('<?xml encoding="UTF-8">'.$html);$xpath=new \DOMXPath($dom);$content='';
        foreach(['//article','//*[contains(@class,"post-body")]','//*[contains(@class,"entry-content")]','//*[contains(@class,"post-content")]','//main'] as $sel){$nodes=$xpath->query($sel);if($nodes&&$nodes->length){$content=trim($nodes->item(0)->textContent);if(mb_strlen($content)>100)break;}}
        $title='';$nodes=$xpath->query('//meta[@property="og:title"]/@content | //h1 | //title');if($nodes&&$nodes->length)$title=trim($nodes->item(0)->nodeValue?:$nodes->item(0)->textContent);
        $image=null;$nodes=$xpath->query('//meta[@property="og:image"]/@content');if($nodes&&$nodes->length)$image=trim($nodes->item(0)->nodeValue);
        $date=null;$nodes=$xpath->query('//time[@datetime]/@datetime | //meta[@property="article:published_time"]/@content');if($nodes&&$nodes->length)$date=$nodes->item(0)->nodeValue;
        return ['title'=>$title?:null,'content'=>$content?:null,'summary'=>mb_substr(preg_replace('/\s+/u',' ',$content),0,500),'image_url'=>$image,'published_at'=>$date];
    }

    private function detectCategory(string $title): string { $t=mb_strtolower($title);if(str_contains($t,'police'))return'CG Police';if(str_contains($t,'psc'))return'CGPSC';if(preg_match('/teacher|school|education|shikshak|vyakhyata/u',$t))return'CG Education';if(preg_match('/health|doctor|nurse|hospital|nhm/u',$t))return'CG Health';if(preg_match('/central|ssc|railway/u',$t))return'Central Jobs';return'CG Vyapam'; }
}
