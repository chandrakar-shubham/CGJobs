<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobImport extends Model
{
    protected $fillable = [
        'job_source_id','external_url','external_key','title','summary','content','category','image_url','published_at','raw_payload','status','job_id','error_message','fetched_at','processed_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function source() { return $this->belongsTo(JobSource::class, 'job_source_id'); }
    public function job() { return $this->belongsTo(Job::class); }
}
