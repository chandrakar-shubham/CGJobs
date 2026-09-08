<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    public const WORKFLOW_STATUSES = ['draft', 'scheduled', 'published', 'archived'];

    protected $fillable = [
        'custom_id',
        'slug',
        'title',
        'title_en',
        'summary',
        'summary_en',
        'detailed_content',
        'detailed_content_en',
        'exam_takeaway',
        'category',
        'category_en',
        'job_category',
        'department',
        'section',
        'post_type',
        'workflow_status',
        'scheduled_at',
        'source',
        'source_url',
        'image_url',
        'published_at',
        'relative_time',
        'relative_time_en',
        'is_breaking',
        'is_new',
        'vacancies',
        'salary',
        'eligibility',
        'eligibility_en',
        'age_limit',
        'selection_process',
        'selection_process_en',
        'translation_status',
        'official_notification_url',
        'apply_url',
        'application_start',
        'last_date',
        'exam_date',
        'admit_card_date',
        'result_date',
        'views_count',
        'apply_clicks',
        'notification_count',
        'last_notification_at',
        'closing_reminder_sent_at',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
    ];

    public function scopePublic($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('workflow_status')
              ->orWhere('workflow_status', 'published');
        });
    }

    public function versions()
    {
        return $this->hasMany(JobVersion::class);
    }

    public function documents()
    {
        return $this->hasMany(JobDocument::class);
    }

    protected $casts = [
        'is_breaking' => 'boolean',
        'is_new' => 'boolean',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => $this->custom_id ?: (string)$this->id,
            'slug' => $this->slug ?: ('news-' . $this->id),
            'title' => $this->title,
            'summary' => $this->summary,
            'detailedContent' => $this->detailed_content ?: $this->summary,
            'examTakeaway' => $this->exam_takeaway,
            'category' => $this->category,
            'section' => $this->section ?: 'jobs',
            'postType' => $this->post_type ?: 'job',
            'source' => $this->source ?: 'cgstate.gov.in',
            'sourceUrl' => $this->source_url ?: 'https://cgstate.gov.in',
            'webArticleUrl' => url('/current-affairs/' . ($this->slug ?: $this->id)),
            'imageUrl' => $this->image_url,
            'publishedAt' => $this->published_at ?: $this->created_at?->format('Y-m-d') ?: date('Y-m-d'),
            'relativeTime' => $this->relative_time ?: 'हाल ही में',
            'isBreaking' => (bool)$this->is_breaking,
            'isNew' => (bool)$this->is_new,
            'vacancies' => $this->vacancies,
            'salary' => $this->salary,
            'eligibility' => $this->eligibility,
            'ageLimit' => $this->age_limit,
            'selectionProcess' => $this->selection_process,
            'officialNotificationUrl' => $this->official_notification_url,
            'applyUrl' => $this->apply_url,
            'importantDates' => [
                'applicationStart' => $this->application_start ?: 'जारी',
                'lastDate' => $this->last_date ?: 'शीघ्र',
                'examDate' => $this->exam_date,
                'admitCardDate' => $this->admit_card_date,
                'resultDate' => $this->result_date,
            ]
        ];
    }
}
