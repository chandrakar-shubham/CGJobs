<?php

namespace App\Console\Commands;

use App\Models\JobImport;
use App\Models\JobSource;
use App\Services\JobImportNormalizer;
use App\Services\JobSourceService;
use Illuminate\Console\Command;

class SyncJobSources extends Command
{
    protected $signature = 'cgjobs:sync-sources {--source= : Sync one source by ID}';
    protected $description = 'Fetch configured job sources that are due';

    public function handle(JobSourceService $service, JobImportNormalizer $normalizer): int
    {
        $query = JobSource::where('is_active', true);
        if ($id = $this->option('source')) $query->whereKey($id);
        $sources = $query->get(); $count = 0;
        foreach ($sources as $source) {
            if (!$this->due($source)) continue;
            try {
                $r = $service->sync($source);
                JobImport::where('job_source_id', $source->id)->latest('id')->limit(max(1,(int)$r['imported']))->get()->each(fn($import) => $normalizer->normalize($import));
                $this->info("{$source->name}: fetched {$r['fetched']}, imported {$r['imported']}");
                $count++;
            } catch (\Throwable $e) { $this->error("{$source->name}: {$e->getMessage()}"); }
        }
        return self::SUCCESS;
    }

    private function due(JobSource $source): bool
    {
        return !$source->last_fetched_at || $source->last_fetched_at->lte(now()->subMinutes(max(1,(int)$source->frequency_minutes)));
    }
}
