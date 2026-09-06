<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSection extends Model
{
    use HasFactory;

    protected $table = 'app_sections';

    protected $fillable = [
        'section_key',
        'name',
        'hindi_name',
        'description',
        'icon',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function categories()
    {
        return $this->hasMany(Category::class, 'section_id', 'section_key')->where('is_active', true)->orderBy('display_order', 'asc');
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->section_key,
            'name' => $this->name,
            'hindiName' => $this->hindi_name,
            'description' => $this->description,
            'icon' => $this->icon,
            'isActive' => (bool)$this->is_active,
            'categories' => $this->categories->map(function ($cat) {
                return [
                    'id' => $cat->slug,
                    'name' => $cat->name,
                    'hindiName' => $cat->hindi_name,
                    'section' => $this->section_key,
                    'icon' => $cat->icon,
                    'color' => $cat->color,
                ];
            })->values()->all(),
        ];
    }
}
