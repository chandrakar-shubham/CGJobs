<?php

namespace App\Console\Commands;

use App\Services\NewsIngestionService;
use Illuminate\Console\Command;

class IngestNews extends Command
{
    protected $signature = 'cgjobs:ingest-news {--query= : Search query} {--limit=20 : Maximum new articles} {--no-ai : Import only, skip AI processing}';
    protected $description = 'Fetch fresh news, remove duplicates, create draft News records and process them through the AI Content Engine';

    public function handle(NewsIngestionService $service): int
    {
        $result = $service->ingestAndProcess($this->option('query'), (int)$this->option('limit'), !$this->option('no-ai'));
        $this->info("Fetched {$result['fetched']}; created {$result['created']}; AI processed {$result['processed']}.");
        return self::SUCCESS;
    }
}
