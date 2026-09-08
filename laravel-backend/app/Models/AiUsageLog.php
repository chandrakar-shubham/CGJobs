<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = [
        'provider_setting_id','provider','model','source_type','item_count',
        'input_tokens','output_tokens','fallback_used','success','error_message',
    ];

    protected $casts = [
        'fallback_used' => 'boolean',
        'success' => 'boolean',
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
    ];
}
