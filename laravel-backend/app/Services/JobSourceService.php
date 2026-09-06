<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobImport;
use App\Models\JobSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobSourceService
{
    private const MAIN_CATEGORIES = ['CGSSB', 'CGPSC', 'Central Govt', 'Contractual'];

    private const DEPARTMENTS = [
        'Education' => ['education','teacher','shikshak','school','vyakhyata','lecturer','professor','samagra shiksha','sages'],
        'Police' => ['police','constable','sub inspector','si ','home guard','nagar sena'],
        'Revenue' => ['revenue','rajasva','patwari','tehsildar','naib tehsildar'],
        'PHE' => ['phe','public health engineering','water supply','jal sansadhan'],
        'PWD' => ['pwd','public works','sub engineer','civil engineer','works department'],
        'Health' => ['health','doctor','nurse','nhm','hospital','medical','pharmacist','lab assistant','lab technician','dme','ayurved'],
        'Women & Child Development' => ['women','child development','anganwadi','wcd','supervisor'],
        'Forest' => ['forest','van vibhag','wildlife','ranger','forest guard'],
        'Agriculture' => ['agriculture','krishi','agricultural','horticulture'],
        'Panchayat' => ['panchayat','rural development','gram panchayat'],
        'Transport' => ['transport','motor vehicle','rto','parivahan'],
    ];

    public function sync(JobSource $source, ?Carbon $from = null, ?Carbon $to = null, bool $deep = false): array
    {
        $source->update(['last_fetched_at' => now(), 'last_error' => null]);

        try {
            $items = match ($source->source_type) {
                'rss', 'blogger' => $this->parseRss($this->get($source->fetch_url), $source),
                'json_api', 'rest_api', 'wordpress' => $this->parseJson($this->get($source->fetch_url), $source),
                default => $this->parseHtmlSource($source, $deep),
            };

            $imported = 0;
            $skipped = 0;

            foreach ($items as $item) {
                if (empty($item['url']) || empty($item['title'])) { $skipped++; continue; }

                $url = $this->absoluteUrl($item['url'], $source->base_url);
                if (!$url || !$this->sameHost($url, $source->base_url)) { $skipped++; continue; }

                if (JobImport::where('job_source_id', $source->id)->where('external_url', $url)->exists()
                    || Job::where('source_url', $url)->exists()) { $skipped++; continue; }

                try {
                    $detail = $this->parseDetail($this->get($url), $url);
                    $item = array_merge($item, array_filter($detail, fn ($v) => $v !== null && $v !== ''));
                } catch (\Throwable $e) {
                    Log::warning('Job detail fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
                }

                if (!$this->inDateRange($item['published_at'] ?? null, $from, $to)) { $skipped++; continue; }

                $title = Str::limit($this->cleanText($item['title'] ?? ''), 250, '');
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
                    'title' => $title,
                    'summary' => $summary,
                    'content' => $content ?: $summary,
                    'category' => $department,
                    'job_category' => $jobCategory,
                    'department' => $department,
                    'image_url' => $item['image_url'] ?? null,
                    'published_at' => $this->normaliseDate($item['published_at'] ?? null),
                    'raw_payload' => array_merge($item, ['facts' => $facts]),
                    'status' => $source->publish_mode === 'auto' ? 'approved' : 'pending',
                    'fetched_at' => now(),
                ]);

                if ($source->publish_mode === 'auto') $this->publish($import);
                $imported++;
            }

            $source->update([
                'last_success_at' => now(),
                'last_items_fetched' => count($items),
                'last_items_imported' => $imported,
                'last_items_skipped' => $skipped,
            ]);

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
        $jobCategory = in_array($import->job_category, self::MAIN_CATEGORIES, true)
            ? $import->job_category
            : $this->detectMainCategory($title.' '.$content, 'CGSSB');
        $department = $import->department ?: $this->detectDepartment($title.' '.$content, $import->category);
        $facts = is_array($import->raw_payload['facts'] ?? null)
            ? $import->raw_payload['facts']
            : $this->extractFacts($title.' '.$content);

        $job = Job::create([
            'title' => $title, 'title_en' => $title,
            'summary' => $summary, 'summary_en' => $summary,
            'detailed_content' => $content, 'detailed_content_en' => $content,
            'category' => $department, 'category_en' => $department,
            'job_category' => $jobCategory, 'department' => $department,
            'section' => 'jobs', 'post_type' => 'job',
            'source' => $import->source?->name ?: 'Source Website',
            'source_url' => $import->external_url, 'image_url' => $import->image_url,
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
            $t = app(TranslationService::class)->translateMany([
                'title' => $job->title_en,
                'summary' => $job->summary_en,
                'detailed' => $job->detailed_content_en,
                'category' => $job->category_en,
            ]);
            $ready = !empty($t['title']);
            $job->update([
                'title' => $t['title'] ?: $job->title,
                'summary' => $t['summary'] ?: $job->summary,
                'detailed_content' => $t['detailed'] ?: $job->detailed_content,
                'category' => $t['category'] ?: $job->category,
                'translation_status' => $ready ? 'ready' : 'pending',
                'translation_error' => $ready ? null : 'Automatic Hindi translation temporarily unavailable.',
                'translated_at' => $ready ? now() : null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Imported job translation failed', ['job' => $job->id, 'error' => $e->getMessage()]);
        }

        return $job->fresh();
    }

    private function parseHtmlSource(JobSource $source, bool $deep): array
    {
        $queue = [$source->fetch_url];
        $visited = [];
        $items = [];

        while ($queue) {
            $pageUrl = array_shift($queue);
            $pageKey = $this->normaliseUrl($pageUrl);
            if (!$pageKey || isset($visited[$pageKey])) continue;
            $visited[$pageKey] = true;

            try {
                $html = $this->get($pageUrl);
                foreach ($this->parseListing($html, $source) as $item) $items[$item['url']] = $item;

                if (!$deep) break;
                foreach ($this->paginationLinks($html, $source) as $next) {
                    $nextKey = $this->normaliseUrl($next);
                    if ($nextKey && !isset($visited[$nextKey])) $queue[] = $next;
                }
            } catch (\Throwable $e) {
                Log::warning('Job listing page fetch failed', ['url' => $pageUrl, 'error' => $e->getMessage()]);
            }
        }

        return array_values($items);
    }

    private function parseListing(string $html, JobSource $source): array
    {
        $dom = $this->dom($html);
        $xpath = new \DOMXPath($dom);
        $out = [];

        foreach ($xpath->query('//a[@href]') as $a) {
            $href = $this->absoluteUrl(trim($a->getAttribute('href')), $source->base_url);
            if (!$href || !$this->sameHost($href, $source->base_url) || $this->isNavigationUrl($href)) continue;
            $title = $this->listingTitle($xpath, $a, $href);
            if (!$title || mb_strlen($title) < 10 || $this->looksLikePaginationText($title)) continue;
            if (!preg_match('/(job|recruit|vacancy|bharti|rojgar|notification|recruitment|teacher|police|admit|2027|2026|2025|assistant|officer|staff|worker|constable)/iu', $title.' '.$href)) continue;
            $out[$href] = ['url'=>$href,'title'=>$title,'summary'=>$title,'category'=>$this->detectDepartment($title)];
        }

        return array_values($out);
    }

    private function paginationLinks(string $html, JobSource $source): array
    {
        $dom = $this->dom($html);
        $xpath = new \DOMXPath($dom);
        $links = [];
        foreach ($xpath->query('//a[@href]') as $a) {
            $href = $this->absoluteUrl(trim($a->getAttribute('href')), $source->base_url);
            $text = mb_strtolower($this->cleanText($a->textContent));
            if (!$href || !$this->sameHost($href, $source->base_url)) continue;
            if (preg_match('/\b(older|older posts|next|अधिक पुराने|पुराने पोस्ट)\b/iu', $text)
                || preg_match('#/page/[0-9]+/?$#i', parse_url($href, PHP_URL_PATH) ?: '')
                || str_contains($href, 'updated-max=')) $links[] = $href;
        }
        return array_values(array_unique($links));
    }

    private function parseRss(string $xml, JobSource $source): array
    {
        $simple = @simplexml_load_string($xml); if (!$simple) return [];
        $nodes = $simple->channel->item ?? $simple->entry ?? []; $out = [];
        foreach ($nodes as $n) {
            $url = (string)($n->link['href'] ?? $n->link); $title = $this->cleanText((string)$n->title);
            if (!$url || !$title) continue;
            $out[] = ['url'=>$this->absoluteUrl($url,$source->base_url),'title'=>$title,'summary'=>$this->makeShortSummary((string)($n->description??$n->summary)),'content'=>$this->cleanText(strip_tags((string)($n->description??$n->summary))),'category'=>$this->detectDepartment($title),'published_at'=>(string)($n->pubDate??$n->published)];
        }
        return $out;
    }

    private function parseJson(string $json, JobSource $source): array
    {
        $data = json_decode($json,true); if (!is_array($data)) return [];
        $rows = $data['items'] ?? $data['posts'] ?? $data['results'] ?? $data; if (!is_array($rows)) return [];
        $out = [];
        foreach ($rows as $n) {
            if (!is_array($n)) continue;
            $url = $n['url'] ?? $n['link'] ?? $n['permalink'] ?? null; $title = $n['title'] ?? $n['name'] ?? null;
            if (is_array($title)) $title = $title['rendered'] ?? '';
            if (!$url || !$title) continue;
            $out[] = ['url'=>$this->absoluteUrl((string)$url,$source->base_url),'title'=>$this->cleanText((string)$title),'summary'=>$this->makeShortSummary(strip_tags((string)($n['excerpt']??$n['description']??''))),'content'=>$this->cleanText(strip_tags((string)($n['content']??$n['body']??$n['description']??''))),'category'=>$this->detectDepartment((string)$title),'published_at'=>$n['date']??$n['published_at']??null];
        }
        return $out;
    }

    private function parseDetail(string $html, string $url): array
    {
        $dom = $this->dom($html); $xpath = new \DOMXPath($dom); $this->removeNoise($xpath);
        $title = $this->meta($xpath,'og:title') ?: $this->firstText($xpath,'//h1') ?: $this->firstText($xpath,'//title');
        $date = $this->meta($xpath,'article:published_time') ?: $this->firstAttr($xpath,'//time[@datetime]','datetime');
        $node = null;
        foreach(['//article','//*[contains(concat(" ",normalize-space(@class)," ")," post-body ")]','//*[contains(concat(" ",normalize-space(@class)," ")," entry-content ")]','//*[contains(concat(" ",normalize-space(@class)," ")," post-content ")]','//main'] as $selector){$nodes=$xpath->query($selector);if($nodes&&$nodes->length&&mb_strlen($nodes->item(0)->textContent)>250){$node=$nodes->item(0);break;}}
        $node = $node ?: $dom->documentElement;
        $fullText = $this->cleanText($node->textContent);
        if (!$date) $date = $this->findDateInText($fullText);
        $apply = null; $notification = null; $image = $this->meta($xpath,'og:image');
        foreach($xpath->query('//a[@href]') as $a){$href=$this->absoluteUrl(trim($a->getAttribute('href')),$url);if(!$href)continue;$text=mb_strtolower($this->cleanText($a->textContent));if(!$notification&&preg_match('/(notification|विज्ञापन|विभागीय विज्ञापन|download.*notice|short notice|full detail)/iu',$text))$notification=$href;if(!$apply&&preg_match('/(apply|आवेदन|online form|click here|registration)/iu',$text))$apply=$href;}
        return ['title'=>$this->cleanText((string)$title),'summary'=>$this->makeShortSummary($fullText),'content'=>$fullText,'published_at'=>$date,'apply_url'=>$apply,'notification_url'=>$notification,'image_url'=>$image,'category'=>$this->detectDepartment((string)$title.' '.$fullText)];
    }

    private function detectMainCategory(string $text, ?string $default): string
    {
        $text = mb_strtolower($text);
        if (in_array(trim((string)$default), self::MAIN_CATEGORIES, true)) return trim((string)$default);
        if (preg_match('/\b(cgpsc|chhattisgarh public service commission|public service commission)\b/iu',$text)) return 'CGPSC';
        if (preg_match('/\b(cgssb|staff selection board|vyapam|vyavsayik pariksha|chhattisgarh professional examination|cg vyapam)\b/iu',$text)) return 'CGSSB';
        if (preg_match('/\b(contractual|contract|samvida|anubandh|outsourcing|walk[- ]?in)\b/iu',$text)) return 'Contractual';
        if (preg_match('/\b(central government|central govt|ssc|railway|rrb|upsc|ibps|sbi|banking|defence|army|navy|air force|cisf|crpf|bsf)\b/iu',$text)) return 'Central Govt';
        return 'CGSSB';
    }

    private function detectDepartment(string $text, ?string $hint = null): string
    {
        $haystack = mb_strtolower($text.' '.(string)$hint);
        foreach(self::DEPARTMENTS as $department=>$keywords) foreach($keywords as $keyword) if($keyword!==''&&str_contains($haystack,mb_strtolower($keyword))) return $department;
        return 'Other Departments';
    }

    private function extractFacts(string $text): array
    {
        $facts=[];
        if(preg_match('/(?:total\s*)?(?:posts?|vacancies|पद|रिक्तियों?)\D{0,40}(\d{1,6}(?:,\d{3})*)/iu',$text,$m))$facts['vacancies']=$m[1];
        if(preg_match('/(?:apply|application|आवेदन)\D{0,60}(?:from|start|शुरू)\D{0,20}(\d{1,2}[\/-][A-Za-z0-9]+[\/-]\d{2,4}|\d{1,2}\s+[A-Za-z]+\s+\d{4})/iu',$text,$m))$facts['application_start']=trim($m[1]);
        if(preg_match('/(?:last date|closing date|अंतिम तिथि|अंतिम तारीख)\D{0,30}(\d{1,2}[\/-][A-Za-z0-9]+[\/-]\d{2,4}|\d{1,2}\s+[A-Za-z]+\s+\d{4})/iu',$text,$m))$facts['last_date']=trim($m[1]);
        return $facts;
    }

    private function listingTitle(\DOMXPath $xpath, \DOMElement $anchor, string $href): string
    {
        foreach(['ancestor::article[1]//h1[1]','ancestor::article[1]//h2[1]','ancestor::article[1]//h3[1]','ancestor::article[1]//h4[1]','ancestor::*[self::div or self::section][1]//h2[1]','ancestor::*[self::div or self::section][1]//h3[1]'] as $selector){$node=$xpath->query($selector,$anchor)?->item(0);$text=$node?$this->cleanText($node->textContent):'';if($text&&!$this->looksLikePaginationText($text))return$text;}
        $text=$this->cleanText($anchor->textContent);if($text&&!preg_match('/^(check now|get the details|read more|click here|details|apply now)$/iu',$text))return$text;
        $path=trim((string)parse_url($href,PHP_URL_PATH),'/');$slug=preg_replace('/\.(html?|php)$/i','',basename($path));return$this->cleanText(str_replace(['-','_'],' ',urldecode($slug)));
    }

    private function looksLikePaginationText(string $text): bool { return (bool)preg_match('/^(older|newer|next|previous|older posts|newer posts|home|more|menu|search|अधिक पुराने|पुराने पोस्ट|नए पोस्ट)$/iu',trim($text)); }

    private function isNavigationUrl(string $url): bool
    {
        $path=mb_strtolower((string)parse_url($url,PHP_URL_PATH));$query=mb_strtolower((string)parse_url($url,PHP_URL_QUERY));
        return str_contains($path,'/feed')||str_contains($path,'/category/')||str_contains($path,'/tag/')||str_contains($path,'/author/')||str_contains($path,'/wp-json')||str_contains($query,'s=')||str_contains($query,'q=');
    }

    private function inDateRange($value, ?Carbon $from, ?Carbon $to): bool
    {
        if(!$from&&!$to)return true;if(!$value)return false;try{$date=Carbon::parse($value);}catch(\Throwable){return false;}
        if($from&&$date->lt($from->copy()->startOfDay()))return false;if($to&&$date->gt($to->copy()->endOfDay()))return false;return true;
    }

    private function normaliseDate($value): ?Carbon { if(!$value)return null;try{return Carbon::parse($value);}catch(\Throwable){return null;} }
    private function findDateInText(string $text): ?string { if(preg_match('/\b(\d{1,2}[\/-]\d{1,2}[\/-]\d{2,4})\b/',$text,$m))return$m[1];if(preg_match('/\b(\d{1,2}\s+(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4})\b/i',$text,$m))return$m[1];return null; }

    private function get(string $url): string { return Http::timeout(30)->retry(2,1000)->withHeaders(['User-Agent'=>'Mozilla/5.0 (compatible; CGJobsBot/1.0)','Accept'=>'text/html,application/xhtml+xml,application/xml,application/json,text/plain;q=0.9,*/*;q=0.8'])->get($url)->throw()->body(); }
    private function dom(string $html): \DOMDocument { libxml_use_internal_errors(true);$dom=new \DOMDocument();@$dom->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NOWARNING|LIBXML_NOERROR);libxml_clear_errors();return$dom; }
    private function removeNoise(\DOMXPath $xpath): void { foreach($xpath->query('//script|//style|//nav|//footer|//header|//aside|//form|//noscript') as $node)$node->parentNode?->removeChild($node); }
    private function meta(\DOMXPath $xpath,string $property):?string{$n=$xpath->query('//meta[@property="'.$property.'"]|//meta[@name="'.$property.'"]')->item(0);return$n?trim((string)$n->getAttribute('content')):null;}
    private function firstText(\DOMXPath $xpath,string $selector):?string{$n=$xpath->query($selector)->item(0);return$n?trim($n->textContent):null;}
    private function firstAttr(\DOMXPath $xpath,string $selector,string $attr):?string{$n=$xpath->query($selector)->item(0);return$n?trim((string)$n->getAttribute($attr)):null;}
    private function cleanText(string $text):string{$text=html_entity_decode(strip_tags($text),ENT_QUOTES|ENT_HTML5,'UTF-8');$text=preg_replace('/[\t\r\n\x{00A0}]+/u',' ',$text)??$text;return trim(preg_replace('/\s{2,}/u',' ',$text)??$text);}
    private function makeShortSummary(string $text):string{$text=$this->cleanText($text);if(mb_strlen($text)<=360)return$text;$cut=mb_substr($text,0,360);$pos=mb_strrpos($cut,' ');return mb_substr($cut,0,$pos?:360).'…';}
    private function sameHost(string $url,string $base):bool{return strtolower((string)parse_url($url,PHP_URL_HOST))===strtolower((string)parse_url($base,PHP_URL_HOST));}
    private function absoluteUrl(string $url,string $base):?string{if($url===''||str_starts_with($url,'#')||str_starts_with(strtolower($url),'javascript:'))return null;if(filter_var($url,FILTER_VALIDATE_URL))return$this->normaliseUrl($url);$p=parse_url($base);if(!$p||empty($p['scheme'])||empty($p['host']))return null;if(str_starts_with($url,'//'))return$this->normaliseUrl($p['scheme'].':'.$url);if(str_starts_with($url,'/'))return$this->normaliseUrl($p['scheme'].'://'.$p['host'].$url);return$this->normaliseUrl(rtrim($p['scheme'].'://'.$p['host'].rtrim(dirname($p['path']??'/'),'/'),'/').'/'.ltrim($url,'/'));}
    private function normaliseUrl(string $url):string{return rtrim($url,'#');}
}
