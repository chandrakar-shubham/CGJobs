<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Job;
use App\Models\JobImport;
use Illuminate\Support\Str;

class JobPublishNotificationService
{
    public function notify(JobImport $import, Job $job): ?Alert
    {
        $source = $import->source;
        if (!$source || !$source->notify_on_publish) return null;

        $articleId = (string) ($job->custom_id ?: $job->id);
        if (Alert::where('article_id', $articleId)->where('type', 'RECRUITMENT')->exists()) return null;

        $title = trim((string) ($job->title ?: $job->title_en));
        $message = trim((string) ($job->summary ?: $job->summary_en));
        if ($message === '') $message = Str::limit($title, 180, '…');

        $alert = Alert::create([
            'category' => $job->job_category ?: 'CGSSB',
            'title' => $title,
            'short_description' => Str::limit($message, 500, '…'),
            'time' => 'हाल ही में',
            'type' => 'RECRUITMENT',
            'article_id' => $articleId,
            'action_url' => route('job.show', $articleId),
            'is_broadcasted' => false,
        ]);

        $result = app(FirebaseNotificationService::class)->broadcast(
            title: $title,
            message: $alert->short_description,
            category: $alert->category,
            actionUrl: $alert->action_url,
            articleId: $articleId,
        );

        if ($result['success']) $alert->update(['is_broadcasted' => true]);

        return $alert->fresh();
    }
}
