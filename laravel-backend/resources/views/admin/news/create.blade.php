@extends('layouts.admin')
@section('title','Create Current Affair')
@section('content')
<div class="mb-5"><h2 class="text-2xl font-bold">Create Current Affair</h2><p class="text-sm text-slate-500 mt-1">Add an exam-focused current affairs article without creating a Job record.</p></div>
<form method="POST" action="{{route('admin.news.store')}}" class="bg-slate-50">@include('admin.news._form')</form>
@endsection
