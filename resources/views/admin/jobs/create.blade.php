@extends('layouts.admin')
@section('title','Create New Job')
@section('content')
<div class="max-w-6xl mx-auto space-y-5">
<div class="flex items-center justify-between gap-3"><div><h2 class="text-xl font-black text-slate-900">Create New Job</h2><p class="text-xs text-slate-500 mt-1">One compact form shared with crawler-edited jobs, with live SEO guidance.</p></div><a href="{{ route('admin.jobs.index',['section'=>'jobs']) }}" class="px-4 py-2 border rounded-xl text-xs font-bold">← All Jobs</a></div>
<form method="POST" action="{{ route('admin.jobs.store') }}">@csrf
@include('admin.jobs._canonical-form',['values'=>[],'crawler'=>false])
<div class="flex justify-end gap-2 mt-4"><button type="button" onclick="document.getElementById('cjWorkflow').value='draft';document.querySelector('form').submit()" class="px-5 py-2.5 rounded-xl border bg-white text-xs font-bold">Save as Draft</button><button class="px-6 py-2.5 rounded-xl bg-brand-600 text-white text-xs font-black">Save & Publish</button></div>
</form></div>
@endsection
