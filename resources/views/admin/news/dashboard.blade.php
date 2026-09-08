@extends('layouts.admin')
@section('title','News Dashboard')
@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
    <div><h2 class="text-2xl font-black text-slate-900">News Dashboard</h2><p class="text-xs text-slate-500 mt-1">Current Affairs control center — fetch, curate, AI process, review and publish.</p></div>
    <div class="flex gap-2"><a href="{{route('admin.news.index',['stage'=>'fetched'])}}" class="px-4 py-2.5 bg-white border rounded-xl text-xs font-bold text-slate-700">Manage News</a><a href="{{route('admin.news.create')}}" class="px-4 py-2.5 bg-cyan-600 text-white rounded-xl text-xs font-bold">+ Create News</a></div>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
    @foreach([['total','Total News','fa-newspaper','slate','all'],['fetched','Fetched / Review','fa-inbox','amber','fetched'],['processed','AI Processed','fa-robot','blue','processed'],['published','Published','fa-circle-check','emerald','all'],['archived','Archived','fa-box-archive','purple','all']] as [$k,$label,$icon,$tone,$stage])
      <a href="{{route('admin.news.index',['stage'=>$stage])}}" class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm hover:shadow-md transition"><div class="flex items-center justify-between"><span class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{$label}}</span><i class="fa-solid {{$icon}} text-{{$tone}}-500"></i></div><div class="text-2xl font-black text-slate-900 mt-2">{{number_format($stats[$k])}}</div></a>
    @endforeach
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="bg-white border rounded-2xl p-4"><div class="text-[10px] font-black uppercase text-slate-400">Published Today</div><div class="text-2xl font-black mt-1">{{number_format($stats['today'])}}</div></div>
    <div class="bg-white border rounded-2xl p-4"><div class="text-[10px] font-black uppercase text-slate-400">AI Failed</div><div class="text-2xl font-black text-rose-600 mt-1">{{number_format($stats['failed'])}}</div></div>
    <div class="bg-white border rounded-2xl p-4"><div class="text-[10px] font-black uppercase text-slate-400">Website Live</div><div class="text-2xl font-black text-blue-700 mt-1">{{number_format($stats['web_published'])}}</div></div>
    <div class="bg-white border rounded-2xl p-4"><div class="text-[10px] font-black uppercase text-slate-400">Mobile/App Live</div><div class="text-2xl font-black text-cyan-700 mt-1">{{number_format($stats['mobile_published'])}}</div></div>
  </div>

  <div class="bg-white border rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b flex flex-wrap justify-between gap-3"><div><h3 class="font-black text-sm">News Publishing Pipeline</h3><p class="text-[11px] text-slate-500 mt-1">Only curated articles should move into the AI batch.</p></div><a href="{{route('admin.ai-engine.index')}}" class="text-xs font-bold text-purple-700">AI Settings →</a></div>
    <div class="p-5 grid grid-cols-1 md:grid-cols-4 gap-3">
      @foreach([['1','Fetch & Curate','Fetch news, remove unwanted articles and build your batch.','admin.news.index','fetched','amber'],['2','AI Process','Generate mobile + website + SEO content for the selected batch.','admin.news.index','processed','purple'],['3','Review','Open, edit and verify both generated versions.','admin.news.index','processed','blue'],['4','Publish','Publish selected articles to Website + Mobile/App.','admin.news.index','all','emerald']] as [$n,$title,$desc,$routeName,$stage,$tone])
      <a href="{{route($routeName,['stage'=>$stage])}}" class="rounded-2xl border p-4 hover:shadow-md transition"><div class="flex items-center gap-2"><span class="w-7 h-7 rounded-full bg-{{$tone}}-50 text-{{$tone}}-700 flex items-center justify-center text-xs font-black">{{$n}}</span><span class="font-black text-sm">{{$title}}</span></div><p class="text-[11px] text-slate-500 mt-3 leading-5">{{$desc}}</p></a>
      @endforeach
    </div>
  </div>

  <div class="grid lg:grid-cols-2 gap-5">
    <div class="bg-white border rounded-2xl overflow-hidden"><div class="px-5 py-4 border-b"><h3 class="font-black text-sm">Category Distribution</h3></div><div class="p-5 space-y-3">@forelse($categoryStats as $row)<div><div class="flex justify-between text-xs mb-1"><span class="font-bold">{{$row->category ?: 'Uncategorized'}}</span><span class="text-slate-500">{{$row->total}}</span></div><div class="h-2 bg-slate-100 rounded-full overflow-hidden"><div class="h-full bg-cyan-500 rounded-full" style="width:{{min(100,($row->total/max(1,$stats['total']))*100)}}%"></div></div></div>@empty<p class="text-xs text-slate-400">No news yet.</p>@endforelse</div></div>
    <div class="bg-white border rounded-2xl overflow-hidden"><div class="px-5 py-4 border-b"><h3 class="font-black text-sm">Top News Sources</h3></div><div class="p-5 grid grid-cols-2 gap-2">@forelse($sourceStats as $row)<div class="p-3 rounded-xl bg-slate-50"><div class="text-xs font-bold text-slate-700 truncate">{{$row->source}}</div><div class="text-lg font-black text-cyan-700">{{$row->total}}</div></div>@empty<p class="text-xs text-slate-400">No sources yet.</p>@endforelse</div></div>
  </div>

  <div class="grid lg:grid-cols-2 gap-5">
    <div class="bg-white border rounded-2xl overflow-hidden"><div class="px-5 py-4 border-b flex justify-between"><h3 class="font-black text-sm">Recently Fetched</h3><a href="{{route('admin.news.index',['stage'=>'fetched'])}}" class="text-xs font-bold text-cyan-600">View all</a></div><div class="divide-y">@forelse($recent as $item)<div class="px-5 py-3 flex justify-between gap-3"><div class="min-w-0"><div class="text-xs font-bold truncate">{{$item->title}}</div><div class="text-[10px] text-slate-500">{{$item->category}} · {{$item->source ?: 'Unknown'}}</div></div><span class="text-[10px] font-black whitespace-nowrap">{{ucfirst($item->status)}}</span></div>@empty<div class="p-6 text-xs text-slate-400">No news fetched yet.</div>@endforelse</div></div>
    <div class="bg-white border rounded-2xl overflow-hidden"><div class="px-5 py-4 border-b flex justify-between"><h3 class="font-black text-sm">Recently Published</h3><a href="{{route('admin.news.index',['stage'=>'all'])}}" class="text-xs font-bold text-emerald-600">View all</a></div><div class="divide-y">@forelse($recentPublished as $item)<div class="px-5 py-3 flex justify-between gap-3"><div class="min-w-0"><div class="text-xs font-bold truncate">{{$item->title}}</div><div class="text-[10px] text-slate-500">{{$item->category}}</div></div><div class="text-[10px] font-black text-right"><div>{{$item->published_web?'Web ✓':'Web —'}}</div><div>{{$item->published_mobile?'App ✓':'App —'}}</div></div></div>@empty<div class="p-6 text-xs text-slate-400">No published news yet.</div>@endforelse</div></div>
  </div>
</div>
@endsection
