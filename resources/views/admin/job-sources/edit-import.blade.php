@extends('layouts.admin')
@section('title','Edit Crawled Job')
@section('content')
<div class="max-w-6xl mx-auto space-y-5">
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3"><div><h2 class="text-xl font-black text-slate-900">Edit Crawled Job</h2><p class="text-xs text-slate-500 mt-1">Review and correct crawler data using exactly the same canonical job form as Create New Job.</p></div><div class="flex flex-wrap gap-2"><a href="{{ $import->external_url }}" target="_blank" rel="noopener" class="px-3 py-2 border rounded-xl text-xs font-bold">Open Original</a><a href="{{ route('admin.job-sources.index') }}" class="px-3 py-2 border rounded-xl text-xs font-bold">Back to Import / Sync</a></div></div>
@if($errors->any())<div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.job-imports.update',$import) }}" id="crawlerJobForm">@csrf @method('PUT')
@include('admin.jobs._canonical-form',['values'=>$values,'crawler'=>true])
<div class="crawler-footer"><div><strong>Publishing</strong><span>Save corrections, or approve this crawled record when it is ready for the public site.</span></div><div class="crawler-actions"><button type="submit" class="cj-btn cj-draft">Save Changes</button>@if($import->status==='pending')<button type="submit" formaction="{{ route('admin.job-sources.approve',$import) }}" formmethod="POST" class="cj-btn cj-publish">Approve &amp; Publish</button>@endif</div></div>
</form>
@if($import->job)<div class="text-right"><a href="{{ route('job.show',$import->job->custom_id ?: $import->job->id) }}" target="_blank" class="text-xs font-bold text-brand-700">View current CGJobs webpage</a></div>@endif
</div>
<style>.crawler-footer{margin-top:14px;border:1px solid #dbe4ee;border-radius:16px;background:#fff;padding:14px 16px;display:flex;align-items:center;justify-content:space-between;gap:16px;box-shadow:0 2px 7px rgba(15,23,42,.04)}.crawler-footer strong{display:block;font-size:12px;color:#0f172a}.crawler-footer span{display:block;font-size:9px;color:#64748b;margin-top:3px}.crawler-actions{display:flex;gap:8px;flex-wrap:wrap}.crawler-actions .cj-btn{min-width:130px}@media(max-width:650px){.crawler-footer{display:block}.crawler-actions{margin-top:10px}.crawler-actions .cj-btn{flex:1}}
</style>
@endsection
