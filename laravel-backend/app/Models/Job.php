<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_id',
        'title',
        'summary',
        'detailed_content',
        'category',
        'source',
        'source_url',
        'image_url',
        'published_at',
        'relative_time',
        'is_breaking',
        'is_new',
        'vacancies',
        'eligibility',
        'age_limit',
        'selection_process',
        'official_notification_url',
        'apply_url',
        'application_start',
        'last_date',
        'exam_date',
        'admit_card_date',
        'result_date',
    ];

    protected $casts = [
        'is_breaking' => 'boolean',
        'is_new' => 'boolean',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => $this->custom_id ?: (string)$this->id,
            'title' => $this->title,
            'summary' => $this->summary,
            'detailedContent' => $this->detailed_content ?: $this->summary,
            'category' => $this->category,
            'source' => $this->source ?: 'cgstate.gov.in',
            'sourceUrl' => $this->source_url ?: 'https://cgstate.gov.in',
            'imageUrl' => $this->image_url,
            'publishedAt' => $this->published_at ?: $this->created_at?->format('Y-m-d') ?: date('Y-m-d'),
            'relativeTime' => $this->relative_time ?: 'हाल ही में',
            'isBreaking' => (bool)$this->is_breaking,
            'isNew' => (bool)$this->is_new,
            'vacancies' => $this->vacancies,
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
