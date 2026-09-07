<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Job;
use App\Services\FirebaseNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendJobDeadlineReminders extends Command
{
    protected $signature = 'cgjobs:send-deadline-reminders';
    protected $description = 'Push a single reminder when an active job closes within the next two days';

    public function handle(FirebaseNotificationService $fcm): int
    {
        $today = now()->startOfDay();
        $limit = $today->copy()->addDays(2)->endOfDay();
        $sent = 0;

        Job::query()
            ->where(function ($q) {
                $q->where('section', 'jobs')->orWhereNull('section');
            })
            ->whereNull('closing_reminder_sent_at')
            ->whereNotNull('last_date')
            ->orderBy('id')
            ->chunkById(100, function ($jobs) use ($today, $limit, $fcm, &$sent) {
                foreach ($jobs as $job) {
                    $closingDate = $this->parseClosingDate($job->last_date);
                    if (!$closingDate || $closingDate->lt($today) || $closingDate->gt($limit)) {
                        continue;
                    }

                    $articleId = (string) ($job->custom_id ?: $job->id);
                    $alert = Alert::firstOrCreate(
                        [
                            'article_id' => $articleId,
                            'type' => 'DEADLINE_REMINDER',
                            'title' => 'अंतिम तिथि निकट: '.trim((string) ($job->title ?: $job->title_en)),
                        ],
                        [
                            'category' => $job->job_category ?: 'CGSSB',
                            'short_description' => 'आवेदन की अंतिम तिथि '.$closingDate->format('d/m/Y').' है। समय रहते आवेदन करें।',
                            'time' => 'हाल ही में',
                            'action_url' => route('job.show', $articleId),
                            'is_broadcasted' => false,
                        ]
                    );

                    if ($alert->is_broadcasted) {
                        $job->update(['closing_reminder_sent_at' => now()]);
                        continue;
                    }

                    $result = $fcm->broadcast(
                        title: $alert->title,
                        message: $alert->short_description,
                        category: $alert->category,
                        actionUrl: $alert->action_url,
                        articleId: $articleId,
                    );

                    if (($result['success'] ?? false) === true) {
                        $alert->update(['is_broadcasted' => true]);
                        $job->update(['closing_reminder_sent_at' => now()]);
                        $sent++;
                    }
                }
            });

        $this->info("Deadline reminders sent: {$sent}");
        return self::SUCCESS;
    }

    private function parseClosingDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '' || in_array(mb_strtolower($value), ['शीघ्र', 'soon', 'open', 'ongoing', 'as soon as possible'], true)) {
            return null;
        }

        $formats = [
            'Y-m-d', 'd/m/Y', 'd-m-Y', 'd.m.Y',
            'd M Y', 'd F Y', 'd-M-Y', 'd-F-Y',
            'M d, Y', 'F d, Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
                if ($date !== false) return $date->startOfDay();
            } catch (\Throwable) {
                // Try the next supported source format.
            }
        }

        if (preg_match('/\b(\d{1,2})[\s\/-]+([A-Za-z]{3,9})[\s,\/-]+(20\d{2})\b/u', $value, $m)) {
            try {
                return Carbon::parse("{$m[1]} {$m[2]} {$m[3]}")->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        if (preg_match('/\b(\d{1,2})[\/-](\d{1,2})[\/-](20\d{2})\b/', $value, $m)) {
            try {
                return Carbon::createFromFormat('d/m/Y', "{$m[1]}/{$m[2]}/{$m[3]}")->startOfDay();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }
}
