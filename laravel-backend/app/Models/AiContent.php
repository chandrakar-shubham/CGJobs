<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiContent extends Model
{
    protected $fillable = [
        'source_type','source_id','status','source_snapshot','generated_content',
        'input_tokens','output_tokens','attempts','error_message','processed_at','published_at',
    ];

    protected $casts = [
        'source_snapshot' => 'array',
        'generated_content' => 'array',
        'processed_at' => 'datetime',
        'published_at' => 'datetime',
    ];
}
