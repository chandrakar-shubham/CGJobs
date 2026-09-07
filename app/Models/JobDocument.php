<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobDocument extends Model
{
    protected $fillable = ['job_id','title','document_type','url'];
    public function job(){ return $this->belongsTo(Job::class); }
}