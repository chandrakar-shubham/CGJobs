@extends('layouts.admin')
@section('title','Edit Job')
@section('content')
<div class="max-w-6xl mx-auto space-y-5">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"><div><h2 class="text-xl font-black text-slate-900">Edit Job</h2><p class="text-xs text-slate-500 mt-1">ID: {{ $job->custom_id ?: $job->id }} · Status: {{ ucfirst($job->workflow_status ?: 'published') }}</p></div><div class="flex gap-2"><a href="{{ route('job.show',$job->custom_id ?: $job->id) }}" target="_blank" class="px-3 py-2 border rounded-xl text-xs font-bold">View ↗</a><a href="{{ route('admin.jobs.index',['section'=>'jobs']) }}" class="px-3 py-2 border rounded-xl text-xs font-bold">← All Jobs</a></div></div>
<div class="grid grid-cols-2 md:grid-cols-5 gap-2"><a href="{{route('admin.jobs.versions',$job)}}" class="tool">History</a><a href="{{route('admin.jobs.timeline',$job)}}" class="tool">Timeline</a><a href="{{route('admin.jobs.documents',$job)}}" class="tool">Documents</a><a href="{{route('admin.jobs.seo',$job)}}" class="tool">SEO</a><a href="{{route('admin.jobs.notification-history',$job)}}" class="tool">Notifications</a></div>
<form method="POST" action="{{ route('admin.jobs.update',$job) }}">@csrf @method('PUT')
@include('admin.jobs._canonical-form',['values'=>$job,'crawler'=>false])
</form></div>
<style>.tool{padding:10px;border:1px solid #e2e8f0;border-radius:11px;background:#fff;text-align:center;font-size:10px;font-weight:800;color:#475569}</style>
@endsection
