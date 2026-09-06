<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobImport;
use App\Models\JobSource;
use App\Services\JobSourceService;
use Illuminate\Http\Request;

class JobSourceController extends Controller
{
    public function index()
    {
        return view('admin.job-sources.index', ['sources'=>JobSource::latest()->get(),'pending'=>JobImport::where('status','pending')->with('source')->latest()->paginate(20)]);
    }

    public function store(Request $request)
    {
        $data=$request->validate([
            'name'=>'required|string|max:120','base_url'=>'required|url|max:500','fetch_url'=>'required|url|max:1000',
            'source_type'=>'required|in:html,rss,blogger,wordpress,json_api,rest_api','frequency_minutes'=>'required|integer|min:15|max:43200',
            'publish_mode'=>'required|in:approval,auto','default_category'=>'nullable|string|max:120','is_active'=>'nullable|boolean'
        ]);
        JobSource::create($data+['is_active'=>$request->boolean('is_active')]);
        return back()->with('success','Job source added successfully.');
    }

    public function update(Request $request, JobSource $jobSource)
    {
        $data=$request->validate(['name'=>'required|string|max:120','base_url'=>'required|url|max:500','fetch_url'=>'required|url|max:1000','source_type'=>'required|in:html,rss,blogger,wordpress,json_api,rest_api','frequency_minutes'=>'required|integer|min:15|max:43200','publish_mode'=>'required|in:approval,auto','default_category'=>'nullable|string|max:120']);
        $jobSource->update($data+['is_active'=>$request->boolean('is_active')]);
        return back()->with('success','Job source updated.');
    }

    public function destroy(JobSource $jobSource) { $jobSource->delete(); return back()->with('success','Job source removed.'); }

    public function sync(JobSource $jobSource, JobSourceService $service)
    {
        try { $r=$service->sync($jobSource); return back()->with('success',"Sync complete: {$r['fetched']} fetched, {$r['imported']} imported, {$r['skipped']} skipped."); }
        catch (\Throwable $e) { return back()->withErrors(['sync'=>'Sync failed: '.$e->getMessage()]); }
    }

    public function approve(JobImport $jobImport, JobSourceService $service)
    { $service->publish($jobImport); return back()->with('success','Imported job approved and published.'); }

    public function reject(JobImport $jobImport)
    { $jobImport->update(['status'=>'rejected','processed_at'=>now()]); return back()->with('success','Imported job rejected.'); }
}
