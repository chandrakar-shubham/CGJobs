@extends('layouts.admin')
@section('title','Edit Imported Job')
@section('content')
<div class="max-w-6xl mx-auto space-y-5">
<div class="flex items-center justify-between gap-3"><div><h2 class="text-xl font-black text-slate-900">Edit Crawled Job</h2><p class="text-xs text-slate-500 mt-1">The crawler and Create New Job now use the same canonical attributes and live SEO checker.</p></div><div class="flex gap-2"><a href="{{ $import->external_url }}" target="_blank" rel="noopener" class="px-3 py-2 border rounded-xl text-xs font-bold">Open Original ↗</a><a href="{{ route('admin.job-sources.index') }}" class="px-3 py-2 border rounded-xl text-xs font-bold">← Import / Sync</a></div></div>
@if($errors->any())<div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ route('admin.job-imports.update',$import) }}">@csrf @method('PUT')
@include('admin.jobs._canonical-form',['values'=>$values,'crawler'=>true])
<div class="flex flex-wrap justify-end gap-2 mt-4"><button class="px-5 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black">Save Changes</button>@if($import->status==='pending')<button type="submit" formaction="{{ route('admin.job-sources.approve',$import) }}" formmethod="POST" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-black">Approve & Publish</button>@endif</div>
</form>
</div>
@endsection
