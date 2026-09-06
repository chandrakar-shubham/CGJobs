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
    private const MAIN_CATEGORIES = ['CGSSB', 'CGPSC', 'Central Govt', 'Contractual'];
    private const DEPARTMENTS = [
        'Education' => ['education','teacher','shikshak','school','vyakhyata','lecturer','professor','samagra shiksha'],
        'Police' => ['police','constable','sub inspector','si ','home guard','nagar sena'],
        'Revenue' => ['revenue','rajasva','patwari','tehsildar','naib tehsildar'],
        'PHE' => ['phe','public health engineering','water supply','jal sansadhan'],
        'PWD' => ['pwd','public works','sub engineer','civil engineer','works department'],
        'Health' => ['health','doctor','nurse','nhm','hospital','medical','pharmacist','lab assistant','lab technician'],
        'Women & Child Development' => ['women','child development','anganwadi','wcd','supervisor'],
        'Forest' => ['forest','van vibhag','wildlife','ranger','forest guard'],
        'Agriculture' => ['agriculture','krishi','agricultural','horticulture'],
        'Panchayat' => ['panchayat','rural development','gram panchayat'],
        'Transport' => ['transport','motor vehicle','rto'],
    ];

    public function sync(JobSource $source): array
    {
        $source->update(['last_fetched_at' => now(), 'last_error' => null]);
        try {
            $raw = $this->get($source->fetch_url);
            $items = match ($source->source_type) {
                'rss', 'blogger' => $this->parseRss($raw, $source),
                'json_api', 'rest_api', 'wordpress' => $this->parseJson($raw, $source),
                default => $this->parseListing($raw, $source),
            };
            $imported = 0;
            $skipped = 0;
            foreach ($items as $item) {
                if (empty($item['url']) || empty($item['title'])) { $skipped++; continue; }
                $url = $this->absoluteUrl($item['url'], $source->base_url);
                if (!$url || !$this->sameHost($url, $source->base_url)) { $skipped++; continue; }
                if (JobImport::where('job_source_id', $source->id)->where('external_url', $url)->exists() || Job::where('source_url', $url)->exists()) { $skipped++; continue; }

                if ($source->source_type === 'html') {
                    try {
                        $detail = $this->parseDetail($this->get($url), $url);
                        $item = array_merge($item, array_filter($detail, fn ($v) => $v !== null && $v !== ''));
                    } catch (\Throwable $e) {
                        Log::warning('Job detail fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
                    }
                }

                $title = $this->cleanText($item['title'] ?? '');
                $content = $this->cleanText($item['content'] ?? '');
                $summary = $this->makeShortSummary($item['summary'] ?? ($content ?: $title));
                $combined = mb_strtolower($title.' '.$summary.' '.$content);
                $jobCategory = $this->detectMainCategory($combined, $source->default_category);
                $department = $this->detectDepartment($combined, $item['category'] ?? null);
                $facts = $this->extractFacts($combined);
                $facts['apply_url'] = $item['apply_url'] ?? null;
                $facts['notification_url'] = $item['notification_url'] ?? null;

                $import = JobImport::create([
                    'job_source_id' => $source->id,
                    'external_key' => sha1($url),
                    'external_url' => $url,
                    'title' => Str::limit($title, 250, ''),
                    'summary' => $summary,
                    'content' => $content ?: $summary,
                    'category' => $department,
                    'job_category' => $jobCategory,
                    'department' => $department,
                    'image_url' => null,
                    'published_at' => $item['published_at'] ?? null,
                    'raw_payload' => array_merge($item, ['facts' => $facts]),
                    'status' => $source->publish_mode === 'auto' ? 'approved' : 'pending',
                    'fetched_at' => now(),
                ]);

                if ($source->publish_mode === 'auto') $this->publish($import);
                $imported++;
            }

            $source->update(['last_success_at' => now(), 'last_items_fetched' => count($items), 'last_items_imported' => $imported, 'last_items_skipped' => $skipped]);
            return ['fetched' => count($items), 'imported' => $imported, 'skipped' => $skipped];
        } catch (\Throwable $e) {
            $source->update(['last_error' => Str::limit($e->getMessage(), 1000, '')]);
            Log::error('Job source sync failed', ['source' => $source->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function publish(JobImport $import): Job
    {
        if ($import->job_id) return $import->job;
        $existing = Job::where('source_url', $import->external_url)->first();
        if ($existing) {
            $import->update(['job_id' => $existing->id, 'status' => 'published', 'processed_at' => now()]);
            return $existing;
        }

        $title = trim($import->title);
        $content = trim($import->content ?: $import->summary ?: $title);
        $summary = $this->makeShortSummary($import->summary ?: $content);
        $jobCategory = in_array($import->job_category, self::MAIN_CATEGORIES, true) ? $import->job_category : $this->detectMainCategory($title.' '.$content, null);
        $department = $import->department ?: $this->detectDepartment($title.' '.$content, $import->category);
        $facts = is_array($import->raw_payload['facts'] ?? null) ? $import->raw_payload['facts'] : $this->extractFacts($title.' '.$content);

        $job = Job::create([
            'title' => $title, 'title_en' => $title,
            'summary' => $summary, 'summary_en' => $summary,
            'detailed_content' => $content, 'detailed_content_en' => $content,
            'category' => $department, 'category_en' => $department,
            'job_category' => $jobCategory, 'department' => $department,
            'section' => 'jobs', 'post_type' => 'job',
            'source' => $import->source?->name ?: 'Source Website',
            'source_url' => $import->external_url, 'image_url' => null,
            'published_at' => optional($import->published_at)->format('Y-m-d') ?: now()->format('Y-m-d'),
            'relative_time' => 'हाल ही में', 'relative_time_en' => 'Recently', 'is_new' => true,
            'vacancies' => $facts['vacancies'] ?? null,
            'application_start' => $facts['application_start'] ?? 'जारी',
            'last_date' => $facts['last_date'] ?? 'शीघ्र',
            'apply_url' => $facts['apply_url'] ?? null,
            'official_notification_url' => $facts['notification_url'] ?? null,
            'translation_status' => 'pending',
        ]);

        $import->update(['job_id' => $job->id, 'status' => 'published', 'processed_at' => now()]);
        try {
            $t = app(TranslationService::class)->translateMany(['title'=>$job->title_en,'summary'=>$job->summary_en,'detailed'=>$job->detailed_content_en,'category'=>$job->category_en]);
            $ready = !empty($t['title']);
            $job->update(['title'=>$t['title'] ?: $job->title,'summary'=>$t['summary'] ?: $job->summary,'detailed_content'=>$t['detailed'] ?: $job->detailed_content,'category'=>$t['category'] ?: $job->category,'translation_status'=>$ready?'ready':'pending','translation_error'=>$ready?null:'Automatic Hindi translation temporarily unavailable.','translated_at'=>$ready?now():null]);
        } catch (\Throwable $e) {
            Log::warning('Imported job translation failed', ['job'=>$job->id,'error'=>$e->getMessage()]);
        }
        return $job->fresh();
    }

    private function detectMainCategory(string $text, ?string $default): string
    {
        $text = mb_strtolower($text);
        if (in_array(trim((string)$default), self::MAIN_CATEGORIES, true)) return trim((string)$default);
        if (preg_match('/\b(cgpsc|chhattisgarh public service commission|public service commission)\b/iu', $text)) return 'CGPSC';
        if (preg_match('/\b(cgssb|staff selection board|vyapam|vyavsayik pariksha|chhattisgarh professional examination)\b/iu', $text)) return 'CGSSB';
        if (preg_match('/\b(contractual|contract|samvida|anubandh|outsourcing|walk[- ]?in)\b/iu', $text)) return 'Contractual';
        if (preg_match('/\b(central government|central govt|ssc|railway|rrb|upsc|ibps|sbi|banking|defence|army|navy|air force|cisf|crpf|bsf)\b/iu', $text)) return 'Central Govt';
        return 'CGSSB';
    }

    private function detectDepartment(string $text, ?string $hint = null): string
    {
        $haystack = mb_strtolower($text.' '.(string)$hint);
        foreach (self::DEPARTMENTS as $department => $keywords) {
            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($haystack, mb_strtolower($keyword))) return $department;
            }
        }
        return 'Other Departments';
    }

    private function extractFacts(string $text): array
    {
        $facts = [];
        if (preg_match('/(?:total\s*)?(?:posts?|vacancies|पद|रिक्तियों?)\D{0,30}(\d{1,6}(?:,\d{3})*)/iu', $text, $m)) $facts['vacancies'] = $m[1];
        if (preg_match('/(?:apply|application|आवेदन)\D{0,40}(?:from|start|शुरू)\D{0,15}(\d{1,2}[\/-][A-Za-z0-9]+[\/-]\d{2,4}|\d{1,2}\s+[A-Za-z]+\s+\d{4})/iu', $text, $m)) $facts['application_start'] = trim($m[1]);
        if (preg_match('/(?:last date|closing date|अंतिम तिथि|अंतिम तारीख)\D{0,20}(\d{1,2}[\/-][A-Za-z0-9]+[\/-]\d{2,4}|\d{1,2}\s+[A-Za-z]+\s+\d{4})/iu', $text, $m)) $facts['last_date'] = trim($m[1]);
        return $facts;
    }

    private function get(string $url): string
    {
        return Http::timeout(30)->retry(2,1000)->withHeaders(['User-Agent'=>'Mozilla/5.0 (compatible; CGJobsBot/1.0)','Accept'=>'text/html,application/xhtml+xml,application/xml,application/json,text/plain;q=0.9,*/*;q=0.8'])->get($url)->throw()->body();
    }

    private function parseListing(string $html, JobSource $source): array
    {
        $dom=$this->dom($html);$xpath=new \DOMXPath($dom);$out=[];
        foreach($xpath->query('//a[@href]') as $a){$title=$this->cleanText($a->textContent);$href=$this->absoluteUrl(trim($a->getAttribute('href')),$source->base_url);if(!$href||mb_strlen($title)<12||!$this->sameHost($href,$source->base_url))continue;if(!preg_match('/(job|recruit|vacancy|bharti|rojgar|notification|recruitment|2026|2025|teacher|police|admit)/iu',$title.' '.$href))continue;$out[$href]=['url'=>$href,'title'=>$title,'summary'=>$title,'category'=>$this->detectDepartment($title)];}
        return array_slice(array_values($out),0,30);
    }

    private function parseRss(string $xml, JobSource $source): array
    {
        $simple=@simplexml_load_string($xml);if(!$simple)return[];$nodes=$simple->channel->item??$simple->entry??[];$out=[];
        foreach($nodes as $n){$url=(string)($n->link['href']??$n->link);$title=$this->cleanText((string)$n->title);if(!$url||!$title)continue;$out[]=['url'=>$this->absoluteUrl($url,$source->base_url),'title'=>$title,'summary'=>$this->makeShortSummary((string)($n->description??$n->summary)),'content'=>$this->cleanText(strip_tags((string)($n->description??$n->summary))),'category'=>$this->detectDepartment($title),'published_at'=>(string)($n->pubDate??$n->published)];}
        return array_slice($out,0,30);
    }

    private function parseJson(string $json, JobSource $source): array
    {
        $data=json_decode($json,true);if(!is_array($data))return[];$rows=$data['items']??$data['posts']??$data['results']??$data;if(!is_array($rows))return[];$out=[];
        foreach($rows as $n){if(!is_array($n))continue;$url=$n['url']??$n['link']??$n['permalink']??null;$title=$n['title']??$n['name']??null;if(is_array($title))$title=$title['rendered']??'';if(!$url||!$title)continue;$out[]=['url'=>$this->absoluteUrl((string)$url,$source->base_url),'title'=>$this->cleanText((string)$title),'summary'=>$this->makeShortSummary(strip_tags((string)($n['excerpt']??$n['description']??''))),'content'=>$this->cleanText(strip_tags((string)($n['content']??$n['body']??$n['description']??''))),'category'=>$this->detectDepartment((string)$title),'published_at'=>$n['date']??$n['published_at']??null];}
        return array_slice($out,0,30);
    }

    private function parseDetail(string $html, string $url): array
    {
        $dom=$this->dom($html);$xpath=new \DOMXPath($dom);$this->removeNoise($xpath);
        $title=$this->meta($xpath,'og:title')?:$this->firstText($xpath,'//h1')?:$this->firstText($xpath,'//title');
        $date=$this->meta($xpath,'article:published_time')?:$this->firstAttr($xpath,'//time[@datetime]','datetime');$node=null;
        foreach(['//article','//*[contains(concat(" ",normalize-space(@class)," ")," post-body ")]','//*[contains(concat(" ",normalize-space(@class)," ")," entry-content ")]','//*[contains(concat(" ",normalize-space(@class)," ")," post-content ")]','//main'] as $selector){$nodes=$xpath->query($selector);if($nodes&&$nodes->length&&mb_strlen($nodes->item(0)->textContent)>250){$node=$nodes->item(0);break;}}
        $node=$node?:$dom->documentElement;$content=$this->nodeText($node);$apply=$this->absoluteUrl($this->findLink($xpath,'apply|online application|आवेदन')??'',$url);$notification=$this->absoluteUrl($this->findLink($xpath,'notification|advertisement|विज्ञापन|pdf')??'',$url);
        return ['title'=>$this->cleanText($title),'content'=>$content,'summary'=>$this->makeShortSummary($content),'published_at'=>$date,'url'=>$url,'apply_url'=>$apply,'notification_url'=>$notification];
    }

    private function findLink(\DOMXPath $xpath,string $pattern): ?string{foreach($xpath->query('//a[@href]') as $a){$label=$this->cleanText($a->textContent);$href=trim($a->getAttribute('href'));if($href&&preg_match('/'.$pattern.'/iu',$label.' '.$href))return$href;}return null;}
    private function dom(string $html): \DOMDocument{libxml_use_internal_errors(true);$dom=new \DOMDocument();$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NOWARNING|LIBXML_NOERROR);libxml_clear_errors();return$dom;}
    private function removeNoise(\DOMXPath $xpath): void{foreach($xpath->query('//script|//style|//noscript|//iframe|//form|//nav|//footer|//header|//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"sidebar")]|//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"social")]|//*[contains(translate(@class,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","abcdefghijklmnopqrstuvwxyz"),"advert")]') as $node)$node->parentNode?->removeChild($node);}
    private function nodeText(\DOMNode $node): string{$parts=[];foreach($node->childNodes as $child){if($child->nodeType===XML_TEXT_NODE){$text=$this->cleanText($child->textContent);if($text!=='')$parts[]=$text;}elseif($child->nodeType===XML_ELEMENT_NODE){$text=$this->nodeText($child);if($text!=='')$parts[]=$text;}}return$this->cleanText(implode("\n",$parts));}
    private function meta(\DOMXPath $xpath,string $property):?string{$n=$xpath->query('//meta[@property="'.$property.'"]|//meta[@name="'.$property.'"]')->item(0);return$n?trim((string)$n->getAttribute('content')):null;}
    private function firstText(\DOMXPath $xpath,string $selector):?string{$n=$xpath->query($selector)->item(0);return$n?trim($n->textContent):null;}
    private function firstAttr(\DOMXPath $xpath,string $selector,string $attr):?string{$n=$xpath->query($selector)->item(0);return$n?trim((string)$n->getAttribute($attr)):null;}
    private function cleanText(string $text):string{$text=html_entity_decode(strip_tags($text),ENT_QUOTES|ENT_HTML5,'UTF-8');$text=preg_replace('/[ \t]+/u',' ',$text)??$text;$text=preg_replace('/\n{3,}/u',"\n\n",$text)??$text;return trim($text);}
    private function makeShortSummary(string $text):string{$text=$this->cleanText($text);if(mb_strlen($text)<=360)return$text;$cut=mb_substr($text,0,360);$pos=mb_strrpos($cut,' ');return mb_substr($cut,0,$pos?:360).'…';}
    private function sameHost(string $url,string $base):bool{return strtolower((string)parse_url($url,PHP_URL_HOST))===strtolower((string)parse_url($base,PHP_URL_HOST));}
    private function absoluteUrl(string $url,string $base):?string{if($url==='')return null;if(preg_match('#^https?://#i',$url))return$url;$b=rtrim($base,'/');if(str_starts_with($url,'/')){$p=parse_url($base);return($p['scheme']??'https').'://'.($p['host']??'').$url;}return$b.'/'.ltrim($url,'/');}
}
