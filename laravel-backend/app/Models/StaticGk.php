<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticGk extends Model
{
    use HasFactory;

    protected $table = 'static_gks';

    protected $fillable = [
        'custom_id','title','title_en','hindi_title','category','category_en','category_hindi',
        'question','question_en','answer','answer_en','key_points','key_points_en','detailed_notes',
        'detailed_notes_en','year_exam_reference','year_exam_reference_en','is_verified','display_order',
    ];

    protected $casts = [
        'key_points' => 'array', 'key_points_en' => 'array', 'is_verified' => 'boolean', 'display_order' => 'integer',
    ];

    public function toApiArray(?string $language = null): array
    {
        $language = $language ?: request()->query('lang', 'hi');
        $language = in_array($language, ['hi', 'en'], true) ? $language : 'hi';
        $english = $language === 'en';
        $fallback = fn ($en, $hi) => $english ? ($en ?: $hi) : ($hi ?: $en);
        $factsHi = is_array($this->key_points) ? $this->key_points : [];
        $factsEn = is_array($this->key_points_en) ? $this->key_points_en : $factsHi;
        $facts = $english ? ($factsEn ?: $factsHi) : ($factsHi ?: $factsEn);
        $answer = $fallback($this->answer_en, $this->answer);
        $notes = $fallback($this->detailed_notes_en, $this->detailed_notes);

        return [
            'id' => $this->custom_id ?: (string)$this->id,
            'language' => $language,
            'title' => $fallback($this->title_en, $this->title),
            'titleHindi' => $this->hindi_title ?: $this->title,
            'titleEnglish' => $this->title_en ?: $this->title,
            'category' => $fallback($this->category_en, $this->category),
            'categoryHindi' => $this->category_hindi ?: $this->category,
            'categoryEnglish' => $this->category_en ?: $this->category,
            'question' => $fallback($this->question_en, $this->question),
            'answer' => $answer,
            'summary' => $answer ?: ($notes ?: $this->title),
            'facts' => $facts,
            'keyPoints' => $facts,
            'examTip' => $fallback($this->year_exam_reference_en, $this->year_exam_reference) ?: ($english ? 'Important for CGPSC and Vyapam examinations' : 'CGPSC व व्यापम परीक्षाओं हेतु अत्यंत महत्वपूर्ण तथ्य'),
            'relatedExam' => $fallback($this->year_exam_reference_en, $this->year_exam_reference) ?: ($english ? 'CGPSC, Vyapam' : 'CGPSC, व्यापम'),
            'detailedNotes' => $notes,
            'yearExamReference' => $fallback($this->year_exam_reference_en, $this->year_exam_reference),
            'isVerified' => (bool)$this->is_verified,
        ];
    }
}
