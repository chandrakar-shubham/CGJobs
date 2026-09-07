<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Job;
use App\Services\FirebaseNotificationService;
use Illuminate\Console\Command;

class PublishScheduledJobs extends Command
{
    protected $signature = 'cgjobs:publish-scheduled-jobs';
    protected $description = 'Publish due scheduled jobs and optionally broadcast their notifications';

    public function handle(FirebaseNotificationService $fcm): int
    {
        $published = 0;

        Job::query()
            ->where('workflow_status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($jobs) use ($fcm, &$published) {
                foreach ($jobs as $job) {
                    $job->update([
                        'workflow_status' => 'published',
                        'published_at' => now()->format('Y-m-d'),
                        'relative_time' => 'हाल ही में',
                        'relative_time_en' => 'Recently',
                        'is_new' => true,
                        'scheduled_at' => null,
                    ]);

                    $articleId = (string) ($job->custom_id ?: $job->id);
                    $alert = Alert::create([
                        'category' => $job->job_category ?: 'CGSSB',
                        'title' => 'नई भर्ती: '.trim((string) ($job->title ?: $job->title_en)),
                        'short_description' => trim((string) ($job->summary ?: $job->summary_en)) ?: 'नई भर्ती की जानकारी उपलब्ध है।',
                        'time' => 'हाल ही में',
                        'type' => 'SCHEDULED_JOB',
                        'article_id' => $articleId,
                        'action_url' => route('job.show', $articleId),
                        'is_broadcasted' => false,
                    ]);

                    $result = $fcm->broadcast(
                        title: $alert->title,
                        message: $alert->short_description,
                        category: $alert->category,
                        actionUrl: $alert->action_url,
                        articleId: $articleId,
                    );

                    if (($result['success'] ?? false) === true) {
                        $alert->update(['is_broadcasted' => true]);
                        $job->update(['notification_count' => ((int) $job->notification_count) + 1, 'last_notification_at' => now()]);
                    }
                    $published++;
                }
            });

        $this->info("Scheduled jobs published: {$published}");
        return self::SUCCESS;
    }
}
