<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobImport;
use App\Models\JobSource;
use App\Services\JobScrapers\CentralGovtScraper;
use App\Services\JobScrapers\CgpscScraper;
use App\Services\JobScrapers\ErojgarScraper;
use App\Services\JobScrapers\JobskindScraper;
use App\Services\JobScrapers\VyapamScraper;
use Illuminate\Support\Facades\Log;

class UnifiedJobScraperPipeline
{
    protected CgpscScraper $cgpsc;
    protected VyapamScraper $vyapam;
    protected ErojgarScraper $erojgar;
    protected CentralGovtScraper $centralGovt;
    protected JobskindScraper $jobskind;

    public function __construct(
        CgpscScraper $cgpsc,
        VyapamScraper $vyapam,
        ErojgarScraper $erojgar,
        CentralGovtScraper $centralGovt,
        JobskindScraper $jobskind
    ) {
        $this->cgpsc = $cgpsc;
        $this->vyapam = $vyapam;
        $this->erojgar = $erojgar;
        $this->centralGovt = $centralGovt;
        $this->jobskind = $jobskind;
    }

    /**
     * Run scrapers according to filter options
     *
     * @param array $options [
     *    'sources' => ['cgpsc', 'vyapam', 'erojgar', 'central_govt', 'jobskind'],
     *    'post_types' => ['job', 'admit_card', 'answer_key', 'result'],
     *    'district' => 'All',
     *    'from' => 'YYYY-MM-DD',
     *    'to' => 'YYYY-MM-DD',
     * ]
     * @return array Metrics summary
     */
    public function scrapeAndStage(array $options = []): array
    {
        $selectedSources = !empty($options['sources'])
            ? (array)$options['sources']
            : ['cgpsc', 'vyapam', 'erojgar', 'central_govt', 'jobskind'];

        $totalFetched = 0;
        $totalStaged = 0;
        $totalSkipped = 0;

        $rawItems = [];

        // 1. CGPSC
        if (in_array('cgpsc', $selectedSources)) {
            $items = $this->cgpsc->scrape($options);
            $rawItems = array_merge($rawItems, $items);
        }

        // 2. CGSSB (Vyapam)
        if (in_array('vyapam', $selectedSources)) {
            $items = $this->vyapam->scrape($options);
            $rawItems = array_merge($rawItems, $items);
        }

        // 3. CONTRACTUAL (E-Rojgar)
        if (in_array('erojgar', $selectedSources)) {
            $items = $this->erojgar->scrape($options);
            $rawItems = array_merge($rawItems, $items);
        }

        // 4. CENTRAL GOVT
        if (in_array('central_govt', $selectedSources)) {
            $items = $this->centralGovt->scrape($options);
            $rawItems = array_merge($rawItems, $items);
        }

        // 5. Secondary: Jobskind
        if (in_array('jobskind', $selectedSources)) {
            $items = $this->jobskind->scrape($options);
            $rawItems = array_merge($rawItems, $items);
        }

        $totalFetched = count($rawItems);

        // Stage items into JobImport with status = 'staged'
        foreach ($rawItems as $item) {
            $url = $item['source_url'] ?? $item['official_notification_url'];
            if (!$url || empty($item['title'])) {
                $totalSkipped++;
                continue;
            }

            // Deduplication: check if already exists in JobImport or Jobs
            if (JobImport::where('external_url', $url)->exists() ||
                Job::where('source_url', $url)->exists() ||
                Job::where('official_notification_url', $url)->exists()) {
                $totalSkipped++;
                continue;
            }

            // Also deduplicate by exact title in recent 30 days
            if (JobImport::where('title', $item['title'])->where('created_at', '>=', now()->subDays(30))->exists()) {
                $totalSkipped++;
                continue;
            }

            // Get or create JobSource record
            $sourceRecord = JobSource::firstOrCreate(
                ['name' => $item['source_name']],
                [
                    'base_url' => $item['official_website'] ?? 'https://cgstate.gov.in',
                    'fetch_url' => $url,
                    'source_type' => 'html',
                    'default_category' => $item['category'],
                    'publish_mode' => 'approval',
                    'is_active' => true,
                ]
            );

            try {
                JobImport::create([
                    'job_source_id' => $sourceRecord->id,
                    'external_url' => $url,
                    'external_key' => md5($item['category'] . '|' . $item['title']),
                    'title' => $item['title'],
                    'summary' => $item['raw_content'] ?? $item['title'],
                    'content' => $item['raw_content'] ?? $item['title'],
                    'category' => $item['department'] ?? 'Other Departments',
                    'job_category' => $item['category'],
                    'department' => $item['department'] ?? 'Other Departments',
                    'published_by' => $item['source_name'],
                    'image_url' => $item['image_url'] ?? null,
                    'published_at' => $item['published_at'] ?? now(),
                    'status' => 'staged', // Staged in console, ready for Gemini batch
                    'raw_payload' => [
                        'source_key' => $item['source_key'] ?? 'unknown',
                        'source_internal' => $item['source_internal'] ?? '',
                        'post_type' => $item['post_type'] ?? 'job',
                        'district' => $item['district'] ?? 'All Chhattisgarh',
                        'official_notification_url' => $item['official_notification_url'] ?? null,
                        'apply_url' => $item['apply_url'] ?? null,
                        'last_date' => $item['last_date'] ?? null,
                    ],
                    'fetched_at' => now(),
                ]);
                $totalStaged++;
            } catch (\Throwable $e) {
                Log::warning("Staging failed for item {$item['title']}: " . $e->getMessage());
                $totalSkipped++;
            }
        }

        return [
            'fetched' => $totalFetched,
            'staged'  => $totalStaged,
            'skipped' => $totalSkipped,
        ];
    }
}
