@extends('layouts.admin')
@section('title','Edit Imported Job')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-3"><div><h2 class="text-2xl font-bold text-slate-900">Edit Imported Job</h2><p class="text-sm text-slate-500 mt-1">Correct the job name, category, department, dates, content and links before or after publishing.</p></div><a href="{{ route('admin.job-sources.index') }}" class="px-4 py-2 border rounded-xl text-sm">← Back</a></div>
    @if($errors->any())<div class="p-4 rounded-xl bg-red-50 text-red-700">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('admin.job-imports.update',$import) }}" class="bg-white border rounded-2xl p-6 shadow-sm space-y-5">@csrf @method('PUT')
        <div class="grid md:grid-cols-2 gap-5">
            <div class="md:col-span-2"><label class="block text-sm font-bold mb-2">Job Name / Title *</label><input name="title" value="{{ old('title',$import->title) }}" required class="w-full border rounded-xl p-3"></div>
            <div><label class="block text-sm font-bold mb-2">Published By / Main Category *</label><select name="job_category" required class="w-full border rounded-xl p-3">@foreach($mainCategories as $cat)<option value="{{ $cat }}" @selected(old('job_category',$import->job_category)===$cat)>{{ $cat }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-bold mb-2">Concerned Department *</label><select name="department" required class="w-full border rounded-xl p-3">@foreach($departments as $department)<option value="{{ $department }}" @selected(old('department',$import->department)===$department)>{{ $department }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-bold mb-2">Source Published Date</label><input type="datetime-local" name="published_at" value="{{ old('published_at',$import->published_at?->format('Y-m-d\TH:i')) }}" class="w-full border rounded-xl p-3"></div>
            <div><label class="block text-sm font-bold mb-2">Original Article URL *</label><input type="url" name="external_url" value="{{ old('external_url',$import->external_url) }}" required class="w-full border rounded-xl p-3"></div>
            <div><label class="block text-sm font-bold mb-2">Apply URL</label><input type="url" name="apply_url" value="{{ old('apply_url',$import->raw_payload['facts']['apply_url'] ?? '') }}" class="w-full border rounded-xl p-3"></div>
            <div><label class="block text-sm font-bold mb-2">Official Notification URL</label><input type="url" name="notification_url" value="{{ old('notification_url',$import->raw_payload['facts']['notification_url'] ?? '') }}" class="w-full border rounded-xl p-3"></div>
            <div><label class="block text-sm font-bold mb-2">Main Source Image URL</label><input type="url" name="image_url" value="{{ old('image_url',$import->image_url) }}" class="w-full border rounded-xl p-3"></div>
        </div>

        @if($import->image_url || !empty($import->image_urls))
        <div class="border rounded-2xl p-4 bg-slate-50"><div class="font-bold mb-3">Fetched Source Images ({{ count($import->image_urls ?: []) }})</div><div class="grid grid-cols-2 md:grid-cols-4 gap-3">@foreach(($import->image_urls ?: []) as $image)<a href="{{ $image }}" target="_blank" rel="noopener" class="block"><img src="{{ $image }}" loading="lazy" class="w-full aspect-[4/3] object-contain bg-white border rounded-xl"><div class="text-[10px] text-slate-500 truncate mt-1">{{ $image }}</div></a>@endforeach</div></div>
        @endif

        <div><label class="block text-sm font-bold mb-2">Short Summary</label><textarea name="summary" rows="4" class="w-full border rounded-xl p-3">{{ old('summary',$import->summary) }}</textarea></div>
        <div><label class="block text-sm font-bold mb-2">Full Article Content</label><textarea name="content" rows="16" class="w-full border rounded-xl p-3 font-mono text-sm">{{ old('content',$import->content) }}</textarea></div>
        <div class="flex flex-wrap gap-3 pt-2"><button class="px-6 py-3 bg-slate-900 text-white rounded-xl font-bold">Save Changes</button><a href="{{ $import->external_url }}" target="_blank" rel="noopener" class="px-6 py-3 border rounded-xl font-bold">Open Original ↗</a>@if($import->job)<a href="{{ route('job.show',$import->job->custom_id ?: $import->job->id) }}" target="_blank" class="px-6 py-3 border rounded-xl font-bold">View CGJobs Webpage ↗</a>@endif</div>
    </form>

    @if($import->status==='pending')<div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 flex flex-col md:flex-row md:items-center justify-between gap-4"><div><b>Ready to publish?</b><div class="text-sm text-emerald-800 mt-1">Save your edits first, then approve it from the Jobs API page.</div></div><form method="POST" action="{{ route('admin.job-sources.approve',$import) }}" onsubmit="return confirm('Approve and publish this job?')">@csrf<button class="px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold">Approve & Publish</button></form></div>@endif
</div>
@endsection
