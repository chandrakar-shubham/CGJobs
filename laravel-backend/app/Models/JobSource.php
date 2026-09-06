<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobSource extends Model
{
    protected $fillable = [
        'name','base_url','fetch_url','source_type','frequency_minutes','publish_mode',
        'default_category','is_active','last_fetched_at','last_success_at','last_error','last_items_fetched','last_items_imported','last_items_skipped',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_fetched_at' => 'datetime',
        'last_success_at' => 'datetime',
        'frequency_minutes' => 'integer',
        'last_items_fetched' => 'integer',
        'last_items_imported' => 'integer',
        'last_items_skipped' => 'integer',
    ];

    public function imports()
    {
        return $this->hasMany(JobImport::class);
    }
}
