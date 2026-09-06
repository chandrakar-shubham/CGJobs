<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'hindi_name',
        'icon',
        'color',
        'display_order',
    ];

    public function toApiArray(): array
    {
        return [
            'id' => $this->slug ?: (string)$this->id,
            'name' => $this->name,
            'hindiName' => $this->hindi_name ?: $this->name,
        ];
    }
}
