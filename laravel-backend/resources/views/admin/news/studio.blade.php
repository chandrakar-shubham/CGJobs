@extends('layouts.admin')
@section('title','News Studio')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div><h2 class="text-2xl font-bold">News / Current Affairs Studio</h2><p class="text-sm text-slate-500 mt-1">Fetch → review → batch AI process → review → publish. Jobs remain completely isolated.</p></div>
  <div class="flex gap-2"><a href="{{route('admin.ai-engine.index')}}" class="px-4 py-2 rounded-xl bg-purple-50 text-purple-700 text-sm font-semibold">AI Settings</a><a href="{{route('admin.news.create')}}" class="px-4 py-2 rounded-xl bg-blue-700 text-white text-sm font-semibold">+ Create News</a></div>
</div>
@if(session('success'))<div class="mb-4 rounded-xl bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{session('success')}}</div>@endif
@if($errors->any())<div class="mb-4 rounded-xl bg-rose-50 text-rose-800 px-4 py-3 text-sm">{{$errors->first()}}</div>@endif
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
 @foreach([['fetched','Fetched / Review','amber'],['processed','AI Processed','blue'],['published','Published','emerald'],['failed','AI Failed','rose']] as $s)<div class="bg-white rounded-2xl border p-4"><div class="text-xs text-slate-500">{{$s[1]}}</div><div class="text-2xl font-bold text-{{$s[2]}}-600">{{$stats[$s[0]]}}</div></div>@endforeach
</div>
<div class="flex flex-wrap gap-2 mb-4">
 @foreach([['fetched','1. Fetched News'],['processed','2. AI Processed'],['published','3. Published']] as $tab)<a href="{{route('admin.news.index',['stage'=>$tab[0]])}}" class="px-4 py-2 rounded-xl text-sm font-semibold {{ $stage===$tab[0]?'bg-slate-900 text-white':'bg-white border text-slate-700' }}">{{$tab[1]}}</a>@endforeach
</div>

@if($stage==='fetched')
<div class="bg-white rounded-2xl border p-5 mb-5">
 <div class="flex items-center justify-between mb-3"><div><h3 class="font-bold">Fetch News</h3><p class="text-xs text-slate-500">No AI request is made here. Articles enter the review queue first.</p></div><span class="text-xs px-2 py-1 rounded-lg bg-slate-100">Sources: Google News/Trending + configured APIs</span></div>
 <form method="POST" action="{{route('admin.news.fetch')}}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">@csrf
  <div class="lg:col-span-2"><label class="text-xs font-semibold">Search / custom query</label><input name="query" placeholder="e.g. Chhattisgarh latest, RBI, climate..." class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
  <div><label class="text-xs font-semibold">Topic</label><select name="topic" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"><option value="">All Current Affairs</option><option value="chhattisgarh">Chhattisgarh</option><option value="cgpsc">CGPSC</option><option value="cg_vyapam">CG Vyapam</option><option value="national">National / India</option><option value="international">International</option><option value="economy">Economy & Banking</option><option value="environment">Environment & Ecology</option><option value="science">Science & Technology</option><option value="defence">Defence</option><option value="polity">Polity & Governance</option><option value="education">Education</option><option value="sports">Sports</option><option value="awards">Awards & Appointments</option><option value="reports">Reports & Indexes</option><option value="important_days">Important Days</option></select></div>
  <div><label class="text-xs font-semibold">Geography</label><select name="geography" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"><option value="chhattisgarh">Chhattisgarh</option><option value="india">India</option><option value="world">World</option></select></div>
  <div><label class="text-xs font-semibold">Source</label><select name="source" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"><option value="all">All Sources</option><option value="google_trending">Google Trending</option><option value="google_news">Google News</option><option value="newsdata">NewsData.io</option><option value="newsapi">NewsAPI</option></select></div>
  <div><label class="text-xs font-semibold">Articles</label><select name="limit" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"><option>10</option><option selected>20</option><option>30</option><option>50</option><option>100</option></select></div>
  <div><label class="text-xs font-semibold">From</label><input type="date" name="from" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
  <div><label class="text-xs font-semibold">To</label><input type="date" name="to" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
  <div class="lg:col-span-2 flex items-end"><button class="w-full rounded-xl bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold">Fetch News for Review</button></div>
 </form>
</div>
@else
<div class="bg-white rounded-2xl border p-4 mb-4 flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-bold">{{ucfirst($stage)}} News</h3><p class="text-xs text-slate-500">Select multiple articles for the next workflow step.</p></div></div>
@endif

<form id="batchForm" method="POST" action="{{route('admin.news.batch-process')}}">
 @csrf
 <div class="bg-white rounded-2xl border overflow-hidden">
  <div class="p-3 border-b bg-slate-50 flex flex-wrap items-center justify-between gap-2">
   <div class="flex gap-2 items-center"><input id="selectAll" type="checkbox" class="h-4 w-4"><span class="text-sm font-semibold">Select all on this page</span><span id="batchCount" class="text-xs px-2 py-1 rounded-lg bg-blue-50 text-blue-700">0 selected</span></div>
   <div class="flex gap-2">
    @if($stage==='fetched')<button type="button" onclick="addToBatch()" class="px-3 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold">Add Selected to AI Batch</button><button type="submit" class="px-3 py-2 rounded-lg bg-purple-700 text-white text-xs font-semibold">Process AI Batch</button>@elseif($stage==='processed')<button formaction="{{route('admin.news.publish-selected')}}" type="submit" class="px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Publish Selected</button>@endif
   </div>
  </div>
  <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 border-b"><tr><th class="p-3 w-10"></th><th class="p-3 text-left w-24">Image</th><th class="p-3 text-left">Article / Content</th><th class="p-3 text-left">Source</th><th class="p-3 text-left">Category</th><th class="p-3 text-left">Status</th><th class="p-3 text-right">Actions</th></tr></thead><tbody class="divide-y">
  @forelse($news as $item)
   <tr class="hover:bg-slate-50 align-top"><td class="p-3"><input name="ids[]" value="{{$item->id}}" type="checkbox" class="news-check h-4 w-4"></td><td class="p-3">@if($item->image_url)<img src="{{$item->image_url}}" alt="" class="w-20 h-14 object-cover rounded-lg">@else<div class="w-20 h-14 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] text-slate-400">No image</div>@endif</td><td class="p-3 min-w-[360px]"><div class="font-semibold">{{Str::limit($item->title,110)}}</div><div class="text-xs text-slate-500 mt-1">{{Str::limit(strip_tags($item->content ?: $item->summary),240)}}</div><div class="text-[11px] text-slate-400 mt-2">ID {{$item->id}} · {{$item->published_at?->format('d M Y H:i')}}</div></td><td class="p-3 min-w-[150px]">{{$item->source ?: 'Unknown'}} @if($item->original_url)<a target="_blank" rel="noopener" href="{{$item->original_url}}" class="block text-blue-600 text-xs mt-1">Original ↗</a>@endif</td><td class="p-3"><span class="px-2 py-1 rounded-lg bg-slate-100 text-xs">{{$item->category}}</span></td><td class="p-3"><span class="px-2 py-1 rounded-lg text-xs font-semibold {{$item->status==='published'?'bg-emerald-50 text-emerald-700':'bg-amber-50 text-amber-700'}}">{{ucfirst($item->status)}}</span></td><td class="p-3"><div class="flex justify-end gap-1.5"><a href="{{route('admin.news.view',$item)}}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs">View</a><a href="{{route('admin.news.edit',$item)}}" class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs">Edit</a><form method="POST" action="{{route('admin.news.studio-delete',$item)}}" onsubmit="return confirm('Delete this article?')">@csrf @method('DELETE')<button class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs">Delete</button></form></div></td></tr>
  @empty<tr><td colspan="7" class="p-12 text-center text-slate-500">No articles in this stage.</td></tr>@endforelse
  </tbody></table></div><div class="p-4">{{$news->links()}}</div>
 </div>
</form>
<form id="savedBatch" method="POST" action="{{route('admin.news.batch-process')}}" class="hidden">@csrf</form>
<script>
const checks=()=>Array.from(document.querySelectorAll('.news-check')).filter(x=>x.checked);
const refresh=()=>document.getElementById('batchCount').textContent=checks().length+' selected';
document.querySelectorAll('.news-check').forEach(x=>x.addEventListener('change',refresh));
document.getElementById('selectAll')?.addEventListener('change',e=>{document.querySelectorAll('.news-check').forEach(x=>x.checked=e.target.checked);refresh();});
function addToBatch(){const ids=checks().map(x=>x.value); if(!ids.length){alert('Select at least one article.');return;} localStorage.setItem('cgNewsBatch',JSON.stringify(ids)); alert(ids.length+' article(s) added to the AI batch. Click Process AI Batch to continue.');}
const saved=JSON.parse(localStorage.getItem('cgNewsBatch')||'[]');
if(saved.length && document.getElementById('batchForm')){document.querySelectorAll('.news-check').forEach(x=>{if(saved.includes(x.value))x.checked=true});refresh();}
document.getElementById('batchForm')?.addEventListener('submit',e=>{localStorage.removeItem('cgNewsBatch');});
</script>
@endsection
