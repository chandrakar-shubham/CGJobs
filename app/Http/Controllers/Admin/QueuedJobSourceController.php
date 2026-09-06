<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessJobSourceSync;
use App\Models\JobImport;
use App\Models\JobSource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;

class QueuedJobSourceController extends Controller
{
    public function sync(Request $request, JobSource $jobSource)
    {
        $from = $request->filled('sync_from') ? Carbon::parse($request->input('sync_from')) : null;
        $to = $request->filled('sync_to') ? Carbon::parse($request->input('sync_to')) : null;

        Queue::connection('database')->push(new ProcessJobSourceSync(
            $jobSource->id,
            $from?->toDateString(),
            $to?->toDateString(),
            true
        ));

        return back()->with('success', 'Fetch started in background. You can continue using the admin panel; refresh this page to see newly imported jobs.');
    }

    public function refreshPending(JobSource $jobSource)
    {
        $removed = JobImport::where('job_source_id', $jobSource->id)->where('status', 'pending')->delete();

        Queue::connection('database')->push(new ProcessJobSourceSync($jobSource->id, null, null, true));

        return back()->with('success', "Background refresh started. {$removed} old pending imports were replaced; newly fetched jobs will appear after processing.");
    }
}
