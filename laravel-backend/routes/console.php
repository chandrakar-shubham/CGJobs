<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('cgjobs:sync-sources')->everyMinute()->withoutOverlapping(10);
Schedule::command('cgjobs:publish-scheduled-jobs')->everyMinute()->withoutOverlapping(10);
Schedule::command('cgjobs:send-deadline-reminders')->dailyAt('08:00')->withoutOverlapping(10);

// Isolated News pipeline: fetch -> duplicate check -> draft -> AI batch -> publish according to AI settings.
Schedule::command('cgjobs:ingest-news --limit=20')->everyTenMinutes()->withoutOverlapping(15);

Schedule::command('queue:work database --queue=default --stop-when-empty --max-time=240 --timeout=210 --tries=1')
    ->everyMinute()
    ->withoutOverlapping(250);
