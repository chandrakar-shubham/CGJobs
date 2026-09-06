<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticGk extends Model
{
    use HasFactory;

    protected $table = 'static_gks';

    protected $fillable = [
        'custom_id',
        'title',
        'hindi_title',
        'category',
        'category_hindi',
        'question',
        'answer',
        'key_points',
        'detailed_notes',
        'year_exam_reference',
        'is_verified',
        'display_order',
    ];

    protected $casts = [
        'key_points' => 'array',
        'is_verified' => 'boolean',
        'display_order' => 'integer',
    ];

    public function toApiArray(): array
    {
        $facts = is_array($this->key_points) ? $this->key_points : [];
        $summary = $this->answer ?: ($this->detailed_notes ?: ($facts[0] ?? $this->title));

        return [
            'id' => $this->custom_id ?: (string)$this->id,
            'title' => $this->title,
            'hindiTitle' => $this->hindi_title ?: $this->title,
            'category' => $this->category,
            'categoryHindi' => $this->category_hindi ?: $this->category,
            'question' => $this->question,
            'answer' => $this->answer,
            'summary' => $summary,
            'facts' => $facts,
            'keyPoints' => $facts,
            'examTip' => $this->year_exam_reference ?: 'CGPSC व व्यापम परीक्षाओं हेतु अत्यंत महत्वपूर्ण तथ्य',
            'relatedExam' => $this->year_exam_reference ?: 'CGPSC, व्यापम',
            'detailedNotes' => $this->detailed_notes,
            'yearExamReference' => $this->year_exam_reference,
            'isVerified' => (bool)$this->is_verified,
        ];
    }
}
