@extends('layouts.admin')
@section('title','Edit Imported Job')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-3">
        <div><h2 class="text-2xl font-bold text-slate-900">Edit Imported Job</h2><p class="text-sm text-slate-500 mt-1">Correct the job name, category, department, dates, content and links before or after publishing.</p></div>
        <a href="{{ route('admin.job-sources.index') }}" class="px-4 py-2 border rounded-xl text-sm">← Back</a>
    </div>

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
            <div><label class="block text-sm font-bold mb-2">Image URL (optional)</label><input type="url" name="image_url" value="{{ old('image_url',$import->image_url) }}" class="w-full border rounded-xl p-3"></div>
        </div>

        <div><label class="block text-sm font-bold mb-2">Short Summary</label><textarea name="summary" rows="4" class="w-full border rounded-xl p-3">{{ old('summary',$import->summary) }}</textarea></div>
        <div><label class="block text-sm font-bold mb-2">Full Article Content</label><textarea name="content" rows="16" class="w-full border rounded-xl p-3 font-mono text-sm">{{ old('content',$import->content) }}</textarea></div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button class="px-6 py-3 bg-slate-900 text-white rounded-xl font-bold">Save Changes</button>
            @if($import->status==='pending')
                <button type="submit" formaction="{{ route('admin.job-sources.approve',$import) }}" formmethod="POST" class="px-6 py-3 bg-emerald-600 text-white rounded-xl font-bold" onclick="return confirm('Publish this job now?')">Save & Publish</button>
            @endif
            <a href="{{ $import->external_url }}" target="_blank" rel="noopener" class="px-6 py-3 border rounded-xl font-bold">Open Original ↗</a>
            @if($import->job)<a href="{{ route('job.show',$import->job->custom_id ?: $import->job->id) }}" target="_blank" class="px-6 py-3 border rounded-xl font-bold">View CGJobs Webpage ↗</a>@endif
        </div>
    </form>
</div>
@endsection
