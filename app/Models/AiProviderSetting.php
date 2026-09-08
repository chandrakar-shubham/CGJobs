<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiProviderSetting extends Model
{
    protected $fillable = [
        'provider','api_key','model','enabled','daily_request_limit','daily_token_limit',
        'max_items_per_request','monthly_budget','auto_publish_news','auto_publish_jobs',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'enabled' => 'boolean',
        'auto_publish_news' => 'boolean',
        'auto_publish_jobs' => 'boolean',
        'monthly_budget' => 'decimal:2',
    ];

    protected $hidden = ['api_key'];

    public function maskedKey(): string
    {
        try { $key = Crypt::decryptString($this->getRawOriginal('api_key')); }
        catch (\Throwable) { $key = ''; }
        return $key ? substr($key, 0, 4) . str_repeat('•', max(4, strlen($key) - 8)) . substr($key, -4) : '';
    }
}
