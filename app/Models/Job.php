<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_id','title','title_en','summary','summary_en','detailed_content','detailed_content_en',
        'category','category_en','section','post_type','source','source_url','image_url','published_at',
        'relative_time','relative_time_en','is_breaking','is_new','vacancies','salary','eligibility','eligibility_en',
        'age_limit','selection_process','selection_process_en','official_notification_url','apply_url',
        'application_start','last_date','exam_date','admit_card_date','result_date',
    ];

    protected $casts = ['is_breaking' => 'boolean', 'is_new' => 'boolean'];

    public function toApiArray(?string $language = null): array
    {
        $language = $language ?: request()->query('lang', 'hi');
        $language = in_array($language, ['hi', 'en'], true) ? $language : 'hi';
        $english = $language === 'en';

        $fallback = fn ($en, $hi) => $english ? ($en ?: $hi) : ($hi ?: $en);

        return [
            'id' => $this->custom_id ?: (string)$this->id,
            'language' => $language,
            'title' => $fallback($this->title_en, $this->title),
            'titleHindi' => $this->title,
            'titleEnglish' => $this->title_en ?: $this->title,
            'summary' => $fallback($this->summary_en, $this->summary),
            'detailedContent' => $fallback($this->detailed_content_en, $this->detailed_content ?: $this->summary),
            'category' => $fallback($this->category_en, $this->category),
            'categoryHindi' => $this->category,
            'categoryEnglish' => $this->category_en ?: $this->category,
            'section' => $this->section ?: 'jobs',
            'postType' => $this->post_type ?: 'job',
            'source' => $this->source ?: 'cgstate.gov.in',
            'sourceUrl' => $this->source_url ?: 'https://cgstate.gov.in',
            'imageUrl' => $this->image_url,
            'publishedAt' => $this->published_at ?: $this->created_at?->format('Y-m-d') ?: date('Y-m-d'),
            'relativeTime' => $fallback($this->relative_time_en, $this->relative_time ?: 'हाल ही में'),
            'isBreaking' => (bool)$this->is_breaking,
            'isNew' => (bool)$this->is_new,
            'vacancies' => $this->vacancies,
            'salary' => $this->salary,
            'eligibility' => $fallback($this->eligibility_en, $this->eligibility),
            'ageLimit' => $this->age_limit,
            'selectionProcess' => $fallback($this->selection_process_en, $this->selection_process),
            'officialNotificationUrl' => $this->official_notification_url,
            'applyUrl' => $this->apply_url,
            'importantDates' => [
                'applicationStart' => $this->application_start ?: ($english ? 'Open' : 'जारी'),
                'lastDate' => $this->last_date ?: ($english ? 'Soon' : 'शीघ्र'),
                'examDate' => $this->exam_date,
                'admitCardDate' => $this->admit_card_date,
                'resultDate' => $this->result_date,
            ],
        ];
    }
}
