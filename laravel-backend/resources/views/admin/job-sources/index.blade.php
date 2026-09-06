@extends('layouts.admin')
@section('title','Jobs API & Sources')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Jobs API & Website Sources</h2>
        <p class="text-sm text-slate-500 mt-1">Fetch → review/edit → approve → publish. Main job category is limited to CGSSB, CGPSC, Central Govt and Contractual.</p>
    </div>

    @if(session('success'))<div class="p-4 rounded-xl bg-emerald-50 text-emerald-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="p-4 rounded-xl bg-red-50 text-red-700">{{ $errors->first() }}</div>@endif

    <div class="bg-white border rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3 mb-4">
            <div><h3 class="font-bold">Import / Search Filters</h3><p class="text-xs text-slate-500 mt-1">Search by job name, source, main category and published/fetched date.</p></div>
            <a href="{{ route('admin.job-sources.index') }}" class="text-xs px-3 py-2 rounded-lg border bg-slate-50">Clear Filters</a>
        </div>
        <form method="GET" class="grid md:grid-cols-5 gap-3">
            <input name="q" value="{{ $filters['q'] }}" placeholder="Search job name / URL / department" class="border rounded-xl p-3 md:col-span-2">
            <select name="source_id" class="border rounded-xl p-3"><option value="">All Sources</option>@foreach($sources as $source)<option value="{{ $source->id }}" @selected((string)$filters['source_id']===(string)$source->id)>{{ $source->name }}</option>@endforeach</select>
            <select name="job_category" class="border rounded-xl p-3"><option value="">All Main Categories</option>@foreach($mainCategories as $cat)<option value="{{ $cat }}" @selected($filters['job_category']===$cat)>{{ $cat }}</option>@endforeach</select>
            <div class="flex gap-2 md:col-span-2"><input type="date" name="from" value="{{ $filters['from'] }}" class="border rounded-xl p-3 w-full" title="From date"><input type="date" name="to" value="{{ $filters['to'] }}" class="border rounded-xl p-3 w-full" title="To date"></div>
            <button class="bg-slate-900 text-white rounded-xl p-3 font-bold">Search</button>
        </form>
    </div>

    <div class="bg-white border rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3 mb-4"><div><h3 class="font-bold">Fixed Job Poster Canvas</h3><p class="text-xs text-slate-500 mt-1">Upload the blank/default canvas once. Every job uses the same template with job-specific text.</p></div></div>
        <form method="POST" action="{{ route('admin.job-poster-template.upload') }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center">@csrf<input type="file" name="template" accept="image/jpeg,image/png" required class="border rounded-xl p-3 flex-1"><button class="bg-slate-900 text-white rounded-xl px-5 py-3 font-bold">Upload Template</button></form>
    </div>

    <div class="bg-white border rounded-2xl p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4"><h3 class="font-bold">Add Website Source</h3><span class="text-xs text-slate-500">JobsKind default: HTML / Approval / CGSSB</span></div>
        <form method="POST" action="{{ route('admin.job-sources.store') }}" class="grid md:grid-cols-2 gap-4">@csrf
            <input name="name" placeholder="Website name e.g. JobsKind" required class="border rounded-xl p-3">
            <input name="base_url" placeholder="https://www.jobskind.com/" type="url" required class="border rounded-xl p-3">
            <input name="fetch_url" placeholder="Listing / archive / feed URL" type="url" required class="border rounded-xl p-3 md:col-span-2">
            <select name="source_type" class="border rounded-xl p-3"><option value="html">Website / HTML</option><option value="rss">RSS / Atom</option><option value="blogger">Blogger</option><option value="wordpress">WordPress</option><option value="json_api">JSON API</option><option value="rest_api">REST API</option></select>
            <select name="frequency_minutes" class="border rounded-xl p-3"><option value="15">Every 15 minutes</option><option value="30" selected>Every 30 minutes</option><option value="60">Every hour</option><option value="120">Every 2 hours</option><option value="360">Every 6 hours</option><option value="720">Every 12 hours</option><option value="1440">Daily</option></select>
            <select name="publish_mode" class="border rounded-xl p-3"><option value="approval">Fetch → My Approval</option><option value="auto">Fetch → Auto Publish</option></select>
            <select name="default_category" class="border rounded-xl p-3">@foreach($mainCategories as $cat)<option value="{{ $cat }}" @selected($cat==='CGSSB')>Default: {{ $cat }}</option>@endforeach</select>
            <label class="flex items-center gap-2 p-3"><input type="checkbox" name="is_active" value="1" checked> Active</label>
            <button class="md:col-span-2 bg-slate-900 text-white rounded-xl p-3 font-bold">Add Source</button>
        </form>
    </div>

    <div class="bg-white border rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b"><h3 class="font-bold">Connected Websites</h3></div>
        <div class="divide-y">
            @forelse($sources as $s)
                <div class="p-5 space-y-3">
                    <div class="grid lg:grid-cols-6 gap-3 items-center">
                        <div><b>{{ $s->name }}</b><div class="text-xs text-slate-500">{{ $s->source_type }} · Default {{ $s->default_category ?: 'CGSSB' }}</div></div>
                        <div class="text-xs break-all lg:col-span-2">{{ $s->fetch_url }}</div>
                        <div class="text-sm">Every {{ $s->frequency_minutes }} min<br><span class="text-slate-500">{{ $s->publish_mode==='auto'?'Auto':'Approval' }}</span></div>
                        <div class="text-xs">Last: {{ $s->last_fetched_at?->diffForHumans() ?: 'Never' }}<br>Fetched {{ $s->last_items_fetched }} · Imported {{ $s->last_items_imported }} · Skipped {{ $s->last_items_skipped }}</div>
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('admin.job-sources.sync',$s) }}">@csrf<button class="px-3 py-2 bg-teal-600 text-white rounded-lg text-xs">Sync All Available</button></form>
                            <form method="POST" action="{{ route('admin.job-sources.destroy',$s) }}">@csrf @method('DELETE')<button onclick="return confirm('Remove source?')" class="px-3 py-2 bg-red-50 text-red-700 rounded-lg text-xs">Remove</button></form>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.job-sources.sync',$s) }}" class="flex flex-wrap gap-2 items-end border-t pt-3">@csrf
                        <div><label class="block text-[11px] font-semibold text-slate-500 mb-1">Fetch From</label><input type="date" name="sync_from" class="border rounded-lg p-2 text-sm"></div>
                        <div><label class="block text-[11px] font-semibold text-slate-500 mb-1">Fetch To</label><input type="date" name="sync_to" class="border rounded-lg p-2 text-sm"></div>
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold">Fetch Date Range</button>
                    </form>
                </div>
            @empty
                <div class="p-8 text-center text-slate-500">No sources configured yet.</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white border rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div><h3 class="font-bold">Fetched Jobs — Awaiting Approval</h3><span class="text-xs text-slate-500">{{ $pending->total() }} matching pending imports</span></div>
            @if($pending->total())
                <form method="POST" action="{{ route('admin.job-imports.approve-all') }}" onsubmit="return confirm('Approve and publish ALL currently filtered pending jobs?')">@csrf
                    @foreach(request()->query() as $key=>$value) @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach
                    <button class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold">Approve All Filtered ({{ $pending->total() }})</button>
                </form>
            @endif
        </div>
        @forelse($pending as $i)
            <div class="p-5 border-b hover:bg-slate-50">
                <div class="flex flex-col xl:flex-row xl:items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="font-semibold text-slate-900">{{ $i->title ?: 'Untitled Job' }}</div>
                        <div class="text-xs text-slate-500 mt-1">Published by: <b>{{ $i->job_category ?: 'CGSSB' }}</b> · Department: <b>{{ $i->department ?: 'Other Departments' }}</b> · {{ $i->source->name ?? 'Source' }}</div>
                        <div class="text-xs text-slate-500 mt-1">Source date: {{ $i->published_at?->format('d M Y') ?: 'Not detected' }} · Fetched: {{ $i->fetched_at?->format('d M Y H:i') }}</div>
                        <a href="{{ $i->external_url }}" target="_blank" rel="noopener" class="text-xs text-blue-600 break-all hover:underline">View original source ↗</a>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        <a href="{{ route('admin.job-imports.edit',$i) }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-bold">Edit</a>
                        <form method="POST" action="{{ route('admin.job-sources.approve',$i) }}">@csrf<button class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold">Approve & Publish</button></form>
                        <form method="POST" action="{{ route('admin.job-sources.reject',$i) }}">@csrf<button class="px-4 py-2 bg-red-50 text-red-700 rounded-lg text-sm">Reject</button></form>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-500">No jobs awaiting approval for the selected filters.</div>
        @endforelse
        <div class="p-4">{{ $pending->links() }}</div>
    </div>

    <div class="bg-white border rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b"><h3 class="font-bold">Published Imported Jobs</h3><span class="text-xs text-slate-500">Published jobs with direct webpage links</span></div>
        @forelse($published as $i)
            <div class="p-5 border-b hover:bg-slate-50">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="min-w-0"><div class="font-semibold">{{ $i->title }}</div><div class="text-xs text-slate-500 mt-1">{{ $i->job_category }} · {{ $i->department }} · {{ $i->source->name ?? 'Source' }} · {{ $i->published_at?->format('d M Y') ?: '—' }}</div></div>
                    <div class="flex flex-wrap gap-2">
                        @if($i->job)<a href="{{ route('job.show',$i->job->custom_id ?: $i->job->id) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-bold">View CGJobs Webpage ↗</a><a href="{{ route('admin.jobs.edit',$i->job) }}" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm">Edit Published Job</a>@endif
                        <a href="{{ $i->external_url }}" target="_blank" rel="noopener" class="px-4 py-2 border rounded-lg text-sm">Original Source ↗</a>
                        <a href="{{ route('admin.job-imports.edit',$i) }}" class="px-4 py-2 border rounded-lg text-sm">Edit Import</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-slate-500">No published imports match the selected filters.</div>
        @endforelse
        <div class="p-4">{{ $published->links() }}</div>
    </div>
</div>
@endsection
