<?php

namespace App\Jobs;

use App\Models\JobImport;
use App\Models\JobSource;
use App\Services\JobImportNormalizer;
use App\Services\JobSourceService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessJobSourceSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 210;
    public int $tries = 1;

    public function __construct(
        public int $sourceId,
        public ?string $from = null,
        public ?string $to = null,
        public bool $deep = true,
    ) {}

    public function handle(JobSourceService $service, JobImportNormalizer $normalizer): void
    {
        $source = JobSource::find($this->sourceId);
        if (!$source) return;

        $beforeId = (int) JobImport::max('id');
        $from = $this->from ? Carbon::parse($this->from) : null;
        $to = $this->to ? Carbon::parse($this->to) : null;

        try {
            $result = $service->sync($source, $from, $to, $this->deep);

            JobImport::where('job_source_id', $source->id)
                ->where('id', '>', $beforeId)
                ->where('status', 'pending')
                ->orderBy('id')
                ->chunkById(20, function ($imports) use ($normalizer) {
                    foreach ($imports as $import) $normalizer->normalize($import);
                });

            Log::info('Background job source sync completed', [
                'source' => $source->name,
                'fetched' => $result['fetched'] ?? 0,
                'imported' => $result['imported'] ?? 0,
                'skipped' => $result['skipped'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            Log::error('Background job source sync failed', [
                'source' => $source->name,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
