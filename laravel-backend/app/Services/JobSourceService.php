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
        $source->update(['last_fetched_at' => now(), 'last_error' => null]);
        try {
            $html = Http::timeout(30)->retry(2, 1000)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; CGJobsBot/1.0; +https://www.jobskind.com/)'
            ])->get($source->fetch_url)->throw()->body();
            $items = $this->parse($html, $source);
            $imported = 0; $skipped = 0;
            foreach ($items as $item) {
                if (empty($item['url']) || empty($item['title'])) { $skipped++; continue; }
                $key = sha1($item['url']);
                if (JobImport::where('job_source_id',$source->id)->where('external_url',$item['url'])->exists()) { $skipped++; continue; }
                $import = JobImport::create([
                    'job_source_id'=>$source->id,'external_key'=>$key,'external_url'=>$item['url'],
                    'title'=>Str::limit(trim($item['title']),250,''),'summary'=>$item['summary'] ?? null,
                    'content'=>$item['content'] ?? ($item['summary'] ?? null),'category'=>$item['category'] ?? $source->default_category,
                    'image_url'=>$item['image_url'] ?? null,'published_at'=>$item['published_at'] ?? null,
                    'raw_payload'=>$item,'status'=>$source->publish_mode === 'auto' ? 'approved' : 'pending','fetched_at'=>now(),
                ]);
                if ($source->publish_mode === 'auto') { $this->publish($import); }
                $imported++;
            }
            $source->update(['last_success_at'=>now(),'last_items_fetched'=>count($items),'last_items_imported'=>$imported,'last_items_skipped'=>$skipped]);
            return compact('imported','skipped') + ['fetched'=>count($items)];
        } catch (\Throwable $e) {
            Log::error('Job source sync failed', ['source'=>$source->id,'error'=>$e->getMessage()]);
            $source->update(['last_error'=>$e->getMessage()]);
            throw $e;
        }
    }

    public function publish(JobImport $import): Job
    {
        if ($import->job_id) return $import->job;
        $existing = Job::where('source_url',$import->external_url)->first();
        if ($existing) { $import->update(['job_id'=>$existing->id,'status'=>'published','processed_at'=>now()]); return $existing; }
        $title = trim($import->title); $summary = trim($import->summary ?: $import->content ?: $title);
        $job = Job::create([
            'title'=>$title,'title_en'=>$title,'summary'=>$summary,'summary_en'=>$summary,
            'detailed_content'=>$import->content ?: $summary,'detailed_content_en'=>$import->content ?: $summary,
            'category'=>$import->category ?: 'CG Vyapam','category_en'=>$import->category ?: 'CG Vyapam','section'=>'jobs','post_type'=>'job',
            'source'=>$import->source->name,'source_url'=>$import->external_url,'image_url'=>$import->image_url,
            'published_at'=>optional($import->published_at)->format('Y-m-d') ?: now()->format('Y-m-d'),'relative_time'=>'हाल ही में','relative_time_en'=>'Recently',
            'is_new'=>true,'application_start'=>'जारी','last_date'=>'शीघ्र','translation_status'=>'pending'
        ]);
        $import->update(['job_id'=>$job->id,'status'=>'published','processed_at'=>now()]);
        return $job;
    }

    private function parse(string $html, JobSource $source): array
    {
        $dom = new \DOMDocument(); @$dom->loadHTML('<?xml encoding="UTF-8">'.$html); $xpath = new \DOMXPath($dom); $out=[];
        $links = $xpath->query('//a[@href]');
        foreach ($links as $a) {
            $title=trim(preg_replace('/\s+/u',' ', $a->textContent)); $href=$a->getAttribute('href');
            if (mb_strlen($title)<12 || !preg_match('~^https?://~i',$href)) continue;
            if ($source->base_url && !str_starts_with($href,rtrim($source->base_url,'/'))) continue;
            if (preg_match('/(job|recruit|vacancy|bharti|rojgar|notification|post|2026|2025)/i',$title.' '.$href)) {
                $out[$href]=['url'=>$href,'title'=>$title,'summary'=>$title,'content'=>$title,'category'=>$this->detectCategory($title)];
            }
        }
        return array_slice(array_values($out),0,50);
    }

    private function detectCategory(string $title): string
    {
        $t=mb_strtolower($title);
        if (str_contains($t,'police')) return 'CG Police';
        if (str_contains($t,'psc')) return 'CGPSC';
        if (preg_match('/teacher|school|education|shikshak|vyakhyata/u',$t)) return 'CG Education';
        if (preg_match('/health|doctor|nurse|hospital|nhm/u',$t)) return 'CG Health';
        if (str_contains($t,'central') || str_contains($t,'ssc') || str_contains($t,'railway')) return 'Central Jobs';
        return 'CG Vyapam';
    }
}
