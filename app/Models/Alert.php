<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_id',
        'category',
        'title',
        'short_description',
        'time',
        'type',
        'is_read',
        'article_id',
        'action_url',
        'is_broadcasted',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_broadcasted' => 'boolean',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => $this->custom_id ?: (string)$this->id,
            'category' => $this->category ?: 'सूचना',
            'title' => $this->title,
            'shortDescription' => $this->short_description ?: '',
            'time' => $this->time ?: $this->created_at?->diffForHumans() ?: 'हाल ही में',
            'type' => $this->type ?: 'BREAKING',
            'isRead' => (bool)$this->is_read,
            'articleId' => $this->article_id,
        ];
    }
}
