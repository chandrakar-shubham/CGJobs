@extends('layouts.admin')
@section('title','AI Content Preview')
@section('content')
<div class="max-w-5xl mx-auto space-y-5"><div class="flex items-center justify-between"><div><h1 class="text-2xl font-bold">AI Content Preview</h1><p class="text-sm text-slate-500">{{$content->source_type}} #{{$content->source_id}} • Review before publishing.</p></div><a href="{{route('admin.ai-engine.index')}}" class="px-3 py-2 rounded-lg border">← Back</a></div>
@foreach(['mobile'=>'Mobile / App','website'=>'Website Article','seo'=>'SEO','faq'=>'FAQ'] as $section=>$label)
@if(data_get($content->generated_content,$section))<section class="bg-white border rounded-xl p-5"><h2 class="font-semibold text-lg mb-3">{{$label}}</h2><pre class="whitespace-pre-wrap text-sm leading-6">{{json_encode(data_get($content->generated_content,$section), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)}}</pre></section>@endif
@endforeach
@if($content->status==='generated')<form method="POST" action="{{route('admin.ai-engine.publish',$content)}}">@csrf<button class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white">Publish This Content</button></form>@endif
</div>
@endsection
