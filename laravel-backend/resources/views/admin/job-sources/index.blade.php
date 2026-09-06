@extends('layouts.admin')
@section('title','Jobs API & Sources')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  <div><h2 class="text-2xl font-bold text-slate-900">Jobs API & Website Sources</h2><p class="text-sm text-slate-500 mt-1">Website se jobs fetch karein, structured taxonomy lagayein aur approval ke baad publish karein.</p></div>
  @if(session('success'))<div class="p-4 rounded-xl bg-emerald-50 text-emerald-700">{{session('success')}}</div>@endif
  @if($errors->any())<div class="p-4 rounded-xl bg-red-50 text-red-700">{{$errors->first()}}</div>@endif
  <div class="bg-white border rounded-2xl p-6 shadow-sm">
    <h3 class="font-bold mb-4">Add Website Source</h3>
    <form method="POST" action="{{route('admin.job-sources.store')}}" class="grid md:grid-cols-2 gap-4">@csrf
      <input name="name" placeholder="Website name e.g. JobsKind" required class="border rounded-xl p-3">
      <input name="base_url" placeholder="https://www.jobskind.com/" type="url" required class="border rounded-xl p-3">
      <input name="fetch_url" placeholder="Listing/archive/feed URL" type="url" required class="border rounded-xl p-3 md:col-span-2">
      <select name="source_type" class="border rounded-xl p-3"><option value="html">Website / HTML</option><option value="rss">RSS / Atom</option><option value="blogger">Blogger</option><option value="wordpress">WordPress</option><option value="json_api">JSON API</option><option value="rest_api">REST API</option></select>
      <select name="frequency_minutes" class="border rounded-xl p-3"><option value="15">Every 15 minutes</option><option value="30" selected>Every 30 minutes</option><option value="60">Every hour</option><option value="120">Every 2 hours</option><option value="360">Every 6 hours</option><option value="720">Every 12 hours</option><option value="1440">Daily</option></select>
      <select name="publish_mode" class="border rounded-xl p-3"><option value="approval">Fetch → My Approval</option><option value="auto">Fetch → Auto Publish</option></select>
      <select name="default_category" class="border rounded-xl p-3"><option value="CGSSB">Default: CGSSB</option><option value="CGPSC">Default: CGPSC</option><option value="Central Govt">Default: Central Govt</option><option value="Contractual">Default: Contractual</option></select>
      <label class="flex items-center gap-2 p-3"><input type="checkbox" name="is_active" value="1" checked> Active</label>
      <button class="md:col-span-2 bg-slate-900 text-white rounded-xl p-3 font-bold">Add Source</button>
    </form>
  </div>
  <div class="bg-white border rounded-2xl overflow-hidden shadow-sm"><div class="p-5 border-b"><h3 class="font-bold">Connected Websites</h3></div>
    <div class="divide-y">@forelse($sources as $s)<div class="p-5 grid lg:grid-cols-6 gap-3 items-center"><div><b>{{$s->name}}</b><div class="text-xs text-slate-500">{{$s->source_type}} · {{$s->default_category ?: 'Auto classify'}}</div></div><div class="text-xs break-all lg:col-span-2">{{$s->fetch_url}}</div><div class="text-sm">Every {{$s->frequency_minutes}} min<br><span class="text-slate-500">{{$s->publish_mode==='auto'?'Auto':'Approval'}}</span></div><div class="text-xs">Last: {{$s->last_fetched_at?->diffForHumans() ?: 'Never'}}<br>Fetched {{$s->last_items_fetched}} · Imported {{$s->last_items_imported}}</div><div class="flex gap-2"><form method="POST" action="{{route('admin.job-sources.sync',$s)}}">@csrf<button class="px-3 py-2 bg-teal-600 text-white rounded-lg text-xs">Sync Now</button></form><form method="POST" action="{{route('admin.job-sources.destroy',$s)}}">@csrf @method('DELETE')<button onclick="return confirm('Remove source?')" class="px-3 py-2 bg-red-50 text-red-700 rounded-lg text-xs">Remove</button></form></div></div>@empty<div class="p-8 text-center text-slate-500">No sources configured yet.</div>@endforelse</div>
  </div>
  <div class="bg-white border rounded-2xl overflow-hidden shadow-sm"><div class="p-5 border-b flex justify-between"><h3 class="font-bold">Imported Jobs Awaiting Approval</h3><span>{{$pending->total()}}</span></div>@forelse($pending as $i)<div class="p-5 border-b"><div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4"><div><div class="font-semibold">{{$i->title}}</div><div class="text-xs text-slate-500 mt-1">{{$i->job_category ?: 'Auto'} } · {{$i->department ?: 'Other Departments'}} · {{$i->source->name}} · {{$i->external_url}}</div></div><div class="flex gap-2"><form method="POST" action="{{route('admin.job-sources.approve',$i)}}">@csrf<button class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold">Approve & Publish</button></form><form method="POST" action="{{route('admin.job-sources.reject',$i)}}">@csrf<button class="px-4 py-2 bg-red-50 text-red-700 rounded-lg text-sm">Reject</button></form></div></div></div>@empty<div class="p-8 text-center text-slate-500">No jobs awaiting approval.</div>@endforelse<div class="p-4">{{$pending->links()}}</div></div>
</div>
@endsection
