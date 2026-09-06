<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\JobPosterService;
use Illuminate\Http\Response;

class JobPosterController extends Controller
{
    public function show(string $id, JobPosterService $poster): Response
    {
        $job = Job::where('custom_id', $id)->orWhere('id', $id)->firstOrFail();
        return response($poster->render($job), 200, ['Content-Type' => 'image/svg+xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=86400']);
    }
}
