@extends('layouts.admin')
@section('title','Edit News')
@section('content')
<div class="flex items-center justify-between mb-5"><div><h2 class="text-2xl font-bold">Edit News #{{$news->id}}</h2><p class="text-sm text-slate-500 mt-1">Edit fetched content, source details, status and original link.</p></div><a href="{{route('admin.news.index')}}" class="text-sm text-blue-700">← All News</a></div>
<form method="POST" action="{{route('admin.news.update',$news)}}">@method('PUT') @include('admin.news._form')</form>
@endsection
