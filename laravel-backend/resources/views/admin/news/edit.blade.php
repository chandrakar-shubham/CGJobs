@extends('layouts.admin')
@section('title','Edit Current Affair')
@section('content')
<div class="mb-5"><h2 class="text-2xl font-bold">Edit Current Affair</h2><p class="text-sm text-slate-500 mt-1">Update content, relevance, source or publishing status.</p></div>
<form method="POST" action="{{route('admin.news.update',$news)}}" class="bg-slate-50">@method('PUT') @include('admin.news._form')</form>
@endsection
