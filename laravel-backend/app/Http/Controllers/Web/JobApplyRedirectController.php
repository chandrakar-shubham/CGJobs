<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;

class JobApplyRedirectController extends Controller
{
    public function __invoke(string $id): RedirectResponse
    {
        $job=Job::query()->public()->where(fn($q)=>$q->where('custom_id',$id)->orWhere('id',$id))->firstOrFail();
        abort_unless($job->apply_url,404);
        $job->increment('apply_clicks');
        return redirect()->away($job->apply_url);
    }
}
