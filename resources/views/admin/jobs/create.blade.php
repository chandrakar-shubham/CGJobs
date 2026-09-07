@extends('layouts.admin')
@section('title','Create New Job')
@section('content')
<div class="max-w-6xl mx-auto space-y-5">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"><div><h2 class="text-xl font-black text-slate-900">Create New Job</h2><p class="text-xs text-slate-500 mt-1">Create a job with the same canonical fields used by crawled jobs, API and public frontends.</p></div><a href="{{ route('admin.jobs.index',['section'=>'jobs']) }}" class="px-4 py-2 border rounded-xl text-xs font-bold">← All Jobs</a></div>
<form method="POST" action="{{ route('admin.jobs.store') }}" id="createJobForm">@csrf
@include('admin.jobs._canonical-form',['values'=>[],'crawler'=>false])
</form></div>
@endsection
