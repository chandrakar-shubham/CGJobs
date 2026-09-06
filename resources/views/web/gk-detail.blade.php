@extends('web.layout')
@section('title', ($item->hindi_title ?: $item->title).' — CGJobs GK')
@push('head')
<meta property="og:title" content="{{ $item->hindi_title ?: $item->title }}"><meta property="og:description" content="{{ $item->answer ?: $item->detailed_notes }}"><meta property="og:type" content="article"><meta property="og:url" content="{{ url()->current() }}">
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'Article','headline'=>$item->hindi_title ?: $item->title,'description'=>$item->answer ?: $item->detailed_notes,'mainEntityOfPage'=>url()->current(),'url'=>url()->current(),'publisher'=>['@type'=>'Organization','name'=>'CGJobs']], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
@section('content')
<div class="article-wrap"><div class="container"><div class="article-layout"><article class="article"><div class="kicker">Static GK · {{ $item->category ?: 'General Knowledge' }}</div><h1>{{ $item->hindi_title ?: $item->title }}</h1>
@if($item->question)<div class="content" style="border-top:0;margin-top:15px;padding-top:0"><h2>Question</h2><p style="font-size:18px;font-weight:800;color:#0b1220">{{ $item->question }}</p></div>@endif
@if($item->answer)<div class="gk-answer">{{ $item->answer }}</div>@endif
@if($item->key_points)<div class="content"><h2>Key Points</h2><ul>@foreach($item->key_points as $point)<li>{{ is_string($point) ? $point : json_encode($point,JSON_UNESCAPED_UNICODE) }}</li>@endforeach</ul></div>@endif
@if($item->detailed_notes)<div class="content"><h2>Detailed Notes</h2><div style="white-space:pre-line">{{ $item->detailed_notes }}</div></div>@endif
@if($item->year_exam_reference)<div class="fact" style="margin-top:22px"><label>Exam reference</label><strong>{{ $item->year_exam_reference }}</strong></div>@endif
<div class="article-actions"><a class="btn btn-primary" href="https://play.google.com/store">📱 See in App</a></div></article>
<aside class="side">@if($related->count())<div class="side-card"><h3>Related GK</h3>@foreach($related as $r)<a class="side-link" href="{{ route('gk.show',$r->custom_id ?: $r->id) }}">{{ $r->hindi_title ?: $r->title }}</a>@endforeach</div>@endif<div class="side-card" style="margin-top:12px"><h3>Prepare smarter</h3><p style="font-size:12px;color:#64748b;line-height:1.6;margin:0">CGJobs app में GK को save करके revision के लिए रखें।</p><a class="read" href="https://play.google.com/store">Open app →</a></div></aside></div></div></div>
@endsection