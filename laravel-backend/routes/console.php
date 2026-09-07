<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Discover due sources and enqueue their work. The actual scraping runs from
// the database queue so web requests never wait for a full crawl.
Schedule::command('cgjobs:sync-sources')->everyMinute()->withoutOverlapping(10);

// Publish jobs whose scheduled time has arrived.
Schedule::command('cgjobs:publish-scheduled-jobs')->everyMinute()->withoutOverlapping(10);

// Send deadline reminders once per day. The command itself is idempotent and
// only notifies jobs whose closing date is today or within the next two days.
Schedule::command('cgjobs:send-deadline-reminders')->dailyAt('08:00')->withoutOverlapping(10);

// Hostinger only needs its normal `schedule:run` cron. This worker drains
// queued source-fetch tasks from the dedicated queue_jobs table.
Schedule::command('queue:work database --queue=default --stop-when-empty --max-time=240 --timeout=210 --tries=1')
    ->everyMinute()
    ->withoutOverlapping(250);
