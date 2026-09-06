<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'slug',
        'name',
        'hindi_name',
        'icon',
        'color',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function section()
    {
        return $this->belongsTo(AppSection::class, 'section_id', 'section_key');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->slug ?: (string)$this->id,
            'name' => $this->name,
            'hindiName' => $this->hindi_name ?: $this->name,
            'section' => $this->section_id ?: 'jobs',
            'icon' => $this->icon ?: 'briefcase',
            'color' => $this->color ?: '#1565C0',
        ];
    }
}
