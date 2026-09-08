@extends('layouts.admin')
@section('title','News Studio')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div><h2 class="text-2xl font-bold">News / Current Affairs Studio</h2><p class="text-sm text-slate-500 mt-1">Fetch → review → add selected news to the persistent AI Batch → process → edit → publish.</p></div>
    <div class="flex gap-2"><a href="{{route('admin.ai-engine.index')}}" class="px-4 py-2 rounded-xl bg-purple-50 text-purple-700 text-sm font-semibold">AI Content Engine</a><a href="{{route('admin.news.create')}}" class="px-4 py-2 rounded-xl bg-blue-700 text-white text-sm font-semibold">+ Create News</a></div>
</div>
@if(session('success'))<div class="mb-4 rounded-xl bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">{{session('success')}}</div>@endif
@if($errors->any())<div class="mb-4 rounded-xl bg-rose-50 text-rose-800 px-4 py-3 text-sm">{{$errors->first()}}</div>@endif
<div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
    <div class="bg-white rounded-2xl border p-4"><div class="text-xs text-slate-500">Fetched / Review</div><div class="text-2xl font-bold text-amber-600">{{$stats['fetched']}}</div></div>
    <div class="bg-white rounded-2xl border p-4"><div class="text-xs text-slate-500">AI Batch</div><div class="text-2xl font-bold text-purple-600">{{count($batchIds)}}</div></div>
    <div class="bg-white rounded-2xl border p-4"><div class="text-xs text-slate-500">AI Processed</div><div class="text-2xl font-bold text-blue-600">{{$stats['processed']}}</div></div>
    <div class="bg-white rounded-2xl border p-4"><div class="text-xs text-slate-500">Published</div><div class="text-2xl font-bold text-emerald-600">{{$stats['published']}}</div></div>
    <div class="bg-white rounded-2xl border p-4"><div class="text-xs text-slate-500">Archived</div><div class="text-2xl font-bold text-slate-600">{{$stats['archived']}}</div></div>
</div>
<div class="flex flex-wrap gap-2 mb-4">@foreach([['fetched','1. Fetched News'],['processed','2. AI Processed'],['all','3. All News']] as $tab)<a href="{{route('admin.news.index',['stage'=>$tab[0]])}}" class="px-4 py-2 rounded-xl text-sm font-semibold {{$stage===$tab[0]?'bg-slate-900 text-white':'bg-white border text-slate-700'}}">{{$tab[1]}}</a>@endforeach</div>

@if($stage==='fetched')
<div class="bg-white rounded-2xl border p-5 mb-5">
    <div class="flex items-center justify-between mb-3 gap-3"><div><h3 class="font-bold">1. Fetch News for Review</h3><p class="text-xs text-slate-500">Fetching never calls AI. Fetch again any time; your persistent AI Batch is preserved.</p></div><span class="text-xs px-2 py-1 rounded-lg bg-slate-100">Google News · Trending · NewsData · NewsAPI</span></div>
    <form method="POST" action="{{route('admin.news.fetch')}}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-3">@csrf
        <div class="lg:col-span-2"><label class="text-xs font-semibold">Search / custom query</label><input name="query" value="{{request('query')}}" placeholder="e.g. Chhattisgarh latest, RBI, climate..." class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
        <div><label class="text-xs font-semibold">Topic</label><select name="topic" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"><option value="">All Current Affairs</option><option value="chhattisgarh">Chhattisgarh</option><option value="cgpsc">CGPSC</option><option value="cg_vyapam">CG Vyapam</option><option value="national">National / India</option><option value="international">International</option><option value="economy">Economy & Banking</option><option value="environment">Environment & Ecology</option><option value="science">Science & Technology</option><option value="defence">Defence</option><option value="polity">Polity & Governance</option><option value="education">Education</option><option value="sports">Sports</option><option value="awards">Awards & Appointments</option><option value="reports">Reports & Indexes</option><option value="important_days">Important Days</option></select></div>
        <div><label class="text-xs font-semibold">Geography</label><select name="geography" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"><option value="chhattisgarh">Chhattisgarh</option><option value="india">India</option><option value="world">World</option></select></div>
        <div><label class="text-xs font-semibold">Source</label><select name="source" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"><option value="all">All Sources</option><option value="google_trending">Google Trending</option><option value="google_news">Google News</option><option value="newsdata">NewsData.io</option><option value="newsapi">NewsAPI</option></select></div>
        <div><label class="text-xs font-semibold">Articles per fetch</label><select name="limit" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm">@foreach([10,20,30,50,100] as $n)<option value="{{$n}}" {{request('limit',20)==$n?'selected':''}}>{{$n}}</option>@endforeach</select></div>
        <div><label class="text-xs font-semibold">From</label><input type="date" name="from" value="{{request('from')}}" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
        <div><label class="text-xs font-semibold">To</label><input type="date" name="to" value="{{request('to')}}" class="mt-1 w-full rounded-xl border px-3 py-2 text-sm"></div>
        <div class="lg:col-span-2 flex items-end"><button class="w-full rounded-xl bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold">Fetch News for Review</button></div>
    </form>
</div>
@endif

<div class="bg-white rounded-2xl border p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
    <div><h3 class="font-bold">{{$stage==='fetched'?'Review Queue':($stage==='processed'?'2. AI Processed News':'3. All News')}}</h3><p class="text-xs text-slate-500">{{$stage==='fetched'?'Delete unwanted articles. Keep selecting across multiple fetches until your target is reached.':'The same News record contains both website and mobile/app versions.'}}</p></div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="px-3 py-2 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold">AI Batch: {{count($batchIds)}}</span>
        <form method="POST" action="{{route('admin.news.batch-clear')}}" class="inline">@csrf<button type="submit" class="px-3 py-2 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold" {{count($batchIds)?'':'disabled'}}>Clear AI Batch</button></form>
        @if($stage==='fetched')<form method="POST" action="{{route('admin.news.clear-queue')}}" class="inline" onsubmit="return confirm('Delete every article currently in the fetched/review queue? Processed, published and archived news will not be touched.')">@csrf<button type="submit" class="px-3 py-2 rounded-lg bg-rose-50 text-rose-700 text-xs font-semibold">Clear Review Queue</button></form>@endif
        @if(count($batchIds))<form method="POST" action="{{route('admin.news.batch-process')}}" class="inline">@csrf<button type="submit" class="px-4 py-2 rounded-lg bg-purple-700 text-white text-xs font-semibold">Process Entire AI Batch</button></form>@endif
    </div>
</div>

@if($stage==='fetched')<form id="bulk-form" method="POST" action="{{route('admin.news.batch-add')}}">@csrf</form>@elseif($stage==='processed')<form id="bulk-form" method="POST" action="{{route('admin.news.publish-selected')}}">@csrf</form>@endif

<div class="bg-white rounded-2xl border overflow-hidden">
    <div class="p-3 border-b bg-slate-50 flex flex-wrap items-center justify-between gap-2">
        <div class="flex gap-2 items-center"><input id="selectAll" type="checkbox" class="h-4 w-4" {{$stage==='all'?'disabled':''}}><span class="text-sm font-semibold">Select all on this page</span><span id="selectedCount" class="text-xs px-2 py-1 rounded-lg bg-blue-50 text-blue-700">0 selected</span></div>
        <div class="flex gap-2">@if($stage==='fetched')<button type="submit" form="bulk-form" class="px-4 py-2 rounded-lg bg-amber-500 text-white text-xs font-semibold">Add Selected to AI Batch</button><button type="button" onclick="deleteSelectedNews()" class="px-4 py-2 rounded-lg bg-rose-600 text-white text-xs font-semibold">Delete Selected</button>@elseif($stage==='processed')<button type="submit" form="bulk-form" class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold">Publish Selected → Website + App</button>@endif</div>
    </div>
    <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50 border-b"><tr><th class="p-3 w-10"></th><th class="p-3 text-left w-24">Image</th><th class="p-3 text-left">Article / Content</th><th class="p-3 text-left">Source</th><th class="p-3 text-left">Category</th><th class="p-3 text-left">Status / Channels</th><th class="p-3 text-right">Actions</th></tr></thead><tbody class="divide-y">
    @forelse($news as $item)
        @php($ai=$aiByNews[$item->id] ?? null)
        <tr class="hover:bg-slate-50 align-top">
            <td class="p-3">@if($stage!=='all')<input name="ids[]" value="{{$item->id}}" form="bulk-form" type="checkbox" class="news-check h-4 w-4">@endif</td>
            <td class="p-3">@if($item->image_url)<img src="{{$item->image_url}}" alt="" class="w-20 h-14 object-cover rounded-lg">@else<div class="w-20 h-14 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] text-slate-400">No image</div>@endif</td>
            <td class="p-3 min-w-[420px]">
                @if($stage==='processed' && $ai)
                    <div class="font-semibold text-blue-700">Mobile / App</div><div class="text-xs text-slate-600 mt-1">{{data_get($ai->generated_content,'mobile.summary','—')}}</div>
                    <div class="font-semibold text-emerald-700 mt-3">Website</div><div class="font-semibold mt-1">{{data_get($ai->generated_content,'website.title',$item->title)}}</div><div class="text-xs text-slate-500 mt-1">{{Str::limit(strip_tags(data_get($ai->generated_content,'website.content',$item->content ?: $item->summary)),420)}}</div>
                    <div class="font-semibold text-purple-700 mt-3">SEO</div><div class="text-xs text-slate-500 mt-1">{{data_get($ai->generated_content,'seo.title','—')}} · {{data_get($ai->generated_content,'seo.description','—')}}</div>
                @else
                    <div class="font-semibold">{{Str::limit($item->title,110)}}</div><div class="text-xs text-slate-500 mt-1">{{Str::limit(strip_tags($item->content ?: $item->summary),260)}}</div>
                @endif
                <div class="text-[11px] text-slate-400 mt-2">ID {{$item->id}} · {{$item->published_at?->format('d M Y H:i')}}</div>
            </td>
            <td class="p-3 min-w-[150px]">{{$item->source ?: 'Unknown'}} @if($item->original_url)<a target="_blank" rel="noopener" href="{{$item->original_url}}" class="block text-blue-600 text-xs mt-1">Original ↗</a>@endif</td>
            <td class="p-3"><span class="px-2 py-1 rounded-lg bg-slate-100 text-xs">{{$item->category}}</span></td>
            <td class="p-3"><span class="px-2 py-1 rounded-lg text-xs font-semibold {{$item->status==='published'?'bg-emerald-50 text-emerald-700':($item->status==='archived'?'bg-slate-100 text-slate-600':'bg-amber-50 text-amber-700')}}">{{ucfirst($item->status)}}</span>@if($item->status==='published')<div class="text-[11px] mt-2 space-y-1"><div>Web: {{$item->published_web?'✓':'—'}}</div><div>App: {{$item->published_mobile?'✓':'—'}}</div></div>@endif</td>
            <td class="p-3"><div class="flex flex-wrap justify-end gap-1.5"><a href="{{route('admin.news.view',$item)}}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs">View</a><a href="{{route('admin.news.edit',$item)}}" class="px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-xs">Edit</a>
                @if($stage==='all' && $item->status==='published')
                    <form method="POST" action="{{route('admin.news.archive',$item)}}" class="inline">@csrf<button class="px-2.5 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs">Archive</button></form>
                    <form method="POST" action="{{route('admin.news.channel',$item)}}" class="inline">@csrf<input type="hidden" name="channel" value="web"><input type="hidden" name="action" value="{{$item->published_web?'unpublish':'publish'}}"><button class="px-2.5 py-1.5 rounded-lg {{$item->published_web?'bg-amber-50 text-amber-700':'bg-blue-50 text-blue-700'}} text-xs">{{$item->published_web?'Unpublish Web':'Publish Web'}}</button></form>
                    <form method="POST" action="{{route('admin.news.channel',$item)}}" class="inline">@csrf<input type="hidden" name="channel" value="mobile"><input type="hidden" name="action" value="{{$item->published_mobile?'unpublish':'publish'}}"><button class="px-2.5 py-1.5 rounded-lg {{$item->published_mobile?'bg-amber-50 text-amber-700':'bg-purple-50 text-purple-700'}} text-xs">{{$item->published_mobile?'Unpublish App':'Publish App'}}</button></form>
                @endif
                <button type="button" onclick="deleteNews({{$item->id}})" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs">Delete</button>
            </div></td>
        </tr>
    @empty
        <tr><td colspan="7" class="p-12 text-center text-slate-500">No articles in this stage.</td></tr>
    @endforelse
    </tbody></table></div><div class="p-4">{{$news->links()}}</div>
</div>

<script>
const selectedBoxes=()=>Array.from(document.querySelectorAll('.news-check:checked'));
const refresh=()=>{const n=selectedBoxes().length;const c=document.getElementById('selectedCount');if(c)c.textContent=n+' selected';};
document.querySelectorAll('.news-check').forEach(x=>x.addEventListener('change',refresh));
document.getElementById('selectAll')?.addEventListener('change',e=>{document.querySelectorAll('.news-check').forEach(x=>x.checked=e.target.checked);refresh();});
function submitDelete(ids){if(!ids.length)return;const form=document.createElement('form');form.method='POST';form.action="{{route('admin.news.bulk-delete')}}";form.style.display='none';const csrf=document.createElement('input');csrf.type='hidden';csrf.name='_token';csrf.value="{{csrf_token()}}";form.appendChild(csrf);const method=document.createElement('input');method.type='hidden';method.name='_method';method.value='DELETE';form.appendChild(method);ids.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='ids[]';input.value=id;form.appendChild(input);});document.body.appendChild(form);form.submit();}
function deleteSelectedNews(){const ids=selectedBoxes().map(x=>x.value);if(!ids.length){alert('Select at least one article first.');return;}if(confirm('Delete the selected articles permanently?'))submitDelete(ids);}
function deleteNews(id){if(!confirm('Delete this article permanently?'))return;submitDelete([id]);}
refresh();
</script>
@endsection
