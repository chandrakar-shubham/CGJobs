<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobVersion extends Model
{
    protected $fillable = ['job_id','action','snapshot','changed_by'];
    protected $casts = ['snapshot' => 'array'];
    public function job(){ return $this->belongsTo(Job::class); }
}