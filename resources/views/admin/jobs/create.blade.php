@extends('layouts.admin')
@section('title','Create New Job')
@section('content')
<div class="max-w-6xl mx-auto space-y-5">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"><div><h2 class="text-xl font-black text-slate-900">Create New Job</h2><p class="text-xs text-slate-500 mt-1">Create a job with the same canonical fields used by crawled jobs, API and public frontends.</p></div><a href="{{ route('admin.jobs.index',['section'=>'jobs']) }}" class="px-4 py-2 border rounded-xl text-xs font-bold">← All Jobs</a></div>
<form method="POST" action="{{ route('admin.jobs.store') }}" id="createJobForm">@csrf
@include('admin.jobs._canonical-form',['values'=>[],'crawler'=>false])
<div class="cj-footer-actions"><button type="button" class="cj-btn cj-draft" data-create-workflow="draft">Save Draft</button><button type="button" class="cj-btn cj-schedule" data-create-workflow="scheduled">Schedule Job</button><button type="button" class="cj-btn cj-publish" data-create-workflow="published">Publish Job</button></div>
</form></div>
<style>.cj-footer-actions{display:flex;justify-content:flex-end;flex-wrap:wrap;gap:8px;margin-top:14px;padding:12px;border:1px solid #e2e8f0;border-radius:14px;background:#fff}.cj-footer-actions .cj-btn{min-width:125px}</style>
<script>document.addEventListener('DOMContentLoaded',function(){const form=document.getElementById('createJobForm'),status=document.getElementById('cjWorkflow'),schedule=document.getElementById('cjSchedule');document.querySelectorAll('[data-create-workflow]').forEach(btn=>btn.addEventListener('click',function(){const v=this.dataset.createWorkflow;if(status)status.value=v;if(schedule)schedule.classList.toggle('hidden',v!=='scheduled');if(v==='scheduled'){schedule?.querySelector('input')?.focus();return}form?.submit()}));});</script>
@endsection
