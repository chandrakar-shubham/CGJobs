<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'title_en', 'summary', 'summary_en', 'content', 'content_en',
        'source', 'source_url', 'original_url', 'image_url', 'category', 'category_en',
        'tags', 'language', 'published_at', 'exam_relevance', 'featured', 'status',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'exam_relevance' => 'integer',
        'featured' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function toApiArray(?string $language = null): array
    {
        $language = $language ?: request()->query('lang', 'hi');
        $language = in_array($language, ['hi', 'en'], true) ? $language : 'hi';
        $english = $language === 'en';
        $pick = fn ($en, $hi) => $english ? ($en ?: $hi) : ($hi ?: $en);

        return [
            'id' => (string) $this->id,
            'language' => $language,
            'title' => $pick($this->title_en, $this->title),
            'titleHindi' => $this->title,
            'titleEnglish' => $this->title_en ?: $this->title,
            'summary' => $pick($this->summary_en, $this->summary),
            'detailedContent' => $pick($this->content_en, $this->content ?: $this->summary),
            'category' => $pick($this->category_en, $this->category),
            'categoryHindi' => $this->category,
            'categoryEnglish' => $this->category_en ?: $this->category,
            'section' => 'news',
            'source' => $this->source,
            'sourceUrl' => $this->source_url,
            'originalUrl' => $this->original_url ?: $this->source_url,
            'imageUrl' => $this->image_url,
            'publishedAt' => optional($this->published_at)->toDateString(),
            'examRelevance' => $this->exam_relevance,
            'featured' => (bool) $this->featured,
            'tags' => $this->tags ?: [],
            'status' => $this->status,
        ];
    }
}
