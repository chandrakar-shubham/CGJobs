<?php

namespace App\Console\Commands;

use App\Models\JobImport;
use App\Services\JobBatchAiService;
use App\Services\UnifiedJobScraperPipeline;
use Illuminate\Console\Command;

class ScrapeJobsBatch extends Command
{
    protected $signature = 'jobs:scrape-batch 
                            {--sources=all : Comma-separated list of sources (cgpsc,vyapam,erojgar,central_govt,jobskind)}
                            {--auto-gemini : Automatically process newly staged items with Gemini in single batch}';

    protected $description = 'Scrape jobs from official portals and secondary sources into staging or auto-process with Gemini';

    public function handle(UnifiedJobScraperPipeline $pipeline, JobBatchAiService $aiService): int
    {
        $sourcesArg = $this->option('sources');
        $sources = $sourcesArg === 'all'
            ? ['cgpsc', 'vyapam', 'erojgar', 'central_govt', 'jobskind']
            : explode(',', $sourcesArg);

        $this->info("Starting scraper for sources: " . implode(', ', $sources));

        $res = $pipeline->scrapeAndStage([
            'sources' => $sources,
            'post_types' => ['job', 'admit_card', 'answer_key', 'result'],
        ]);

        $this->info("Scrape complete: {$res['fetched']} fetched, {$res['staged']} new items staged, {$res['skipped']} skipped.");

        if ($this->option('auto-gemini') && $res['staged'] > 0) {
            $this->info("Auto-processing {$res['staged']} staged items with Gemini in a single batch call...");

            $imports = JobImport::where('status', 'staged')->latest()->take(15)->get();
            $items = [];
            foreach ($imports as $imp) {
                $items[] = [
                    'id' => $imp->id,
                    'category' => $imp->job_category ?: 'CGSSB',
                    'title' => $imp->title,
                    'raw_content' => $imp->summary ?: $imp->content,
                    'source_name' => $imp->published_by ?: 'CG Govt Portal',
                ];
            }

            $batchResult = $aiService->processBatch($items);
            if ($batchResult['success']) {
                $indexed = [];
                foreach ($batchResult['processed'] as $p) {
                    if (isset($p['id'])) $indexed[$p['id']] = $p;
                }
                $created = 0;
                foreach ($imports as $imp) {
                    if (isset($indexed[$imp->id])) {
                        $aiService->createOrUpdateJobRecord($imp, $indexed[$imp->id]);
                        $imp->update(['status' => 'processed', 'processed_at' => now()]);
                        $created++;
                    }
                }
                $this->info("Successfully created {$created} Draft Jobs ready in admin panel.");
            } else {
                $this->error("Gemini batch processing failed: " . ($batchResult['message'] ?? 'Unknown error'));
            }
        }

        return Command::SUCCESS;
    }
}
