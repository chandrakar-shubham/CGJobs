@extends('layouts.admin')
@section('title','Create News')
@section('content')
<div class="flex items-center justify-between mb-5"><div><h2 class="text-2xl font-bold">Create News</h2><p class="text-sm text-slate-500 mt-1">Add an isolated Current Affairs article.</p></div><a href="{{route('admin.news.index')}}" class="text-sm text-blue-700">← All News</a></div>
<form method="POST" action="{{route('admin.news.store')}}">@include('admin.news._form')</form>
@endsection
