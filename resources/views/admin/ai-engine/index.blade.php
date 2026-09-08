@extends('layouts.admin')
@section('title','AI Content Engine')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div><h1 class="text-2xl font-bold text-slate-900">🤖 AI Content Engine</h1><p class="text-sm text-slate-500 mt-1">One control center for AI processing. News Studio and this page use the same persistent News Batch.</p></div>
    <a href="{{route('admin.news.index')}}" class="px-4 py-2 rounded-lg bg-cyan-600 text-white text-sm font-semibold">Open News Studio</a>
  </div>
  @if(session('success'))<div class="p-3 rounded-lg bg-emerald-50 text-emerald-700">{{session('success')}}</div>@endif
  @if($errors->any())<div class="p-3 rounded-lg bg-red-50 text-red-700">{{$errors->first()}}</div>@endif

  <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
    @foreach(['total'=>'Total','pending'=>'Pending','generated'=>'Generated','published'=>'Published','failed'=>'Failed'] as $key=>$label)
      <div class="bg-white border rounded-xl p-4"><div class="text-xs text-slate-500">{{$label}}</div><div class="text-2xl font-bold mt-1">{{$stats->$key ?? 0}}</div></div>
    @endforeach
  </div>

  <div class="bg-white border rounded-xl p-5">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4"><div><h2 class="font-semibold">Current News Batch</h2><p class="text-xs text-slate-500 mt-1">This is the same server-side batch used by News Studio. Fetching more news never clears it.</p></div><span class="px-3 py-1 rounded-full bg-cyan-50 text-cyan-700 text-sm font-bold">{{$batchCount}} selected</span></div>
    @if($batchCount)
      <div class="space-y-2 mb-4">@foreach($batchNews as $news)<div class="flex items-center justify-between border rounded-lg px-3 py-2 text-sm"><span class="truncate pr-3">#{{$news->id}} — {{$news->title}}</span><span class="text-xs text-slate-400">{{$news->source ?: 'Unknown source'}}</span></div>@endforeach</div>
      <form method="POST" action="{{route('admin.news.batch-process')}}">@csrf<button class="px-4 py-2 rounded-lg bg-cyan-600 text-white font-semibold">Process Current News Batch ({{$batchCount}})</button></form>
    @else
      <div class="p-5 rounded-lg bg-slate-50 text-sm text-slate-600">No News items are currently in the AI Batch. Go to News Studio → Fetch → select articles → Add Selected to AI Batch.</div>
    @endif
  </div>

  <div class="bg-white border rounded-xl p-5"><h2 class="font-semibold mb-4">Fetch News</h2><form method="POST" action="{{route('admin.ai-engine.ingest-news')}}" class="grid md:grid-cols-[1fr_120px_auto] gap-3 items-end">@csrf<label class="text-sm">Search query<input name="query" value="Chhattisgarh latest news OR CGPSC OR CG Vyapam OR Chhattisgarh government" class="mt-1 w-full border rounded-lg p-2"></label><label class="text-sm">Limit<input type="number" min="1" max="100" name="limit" value="20" class="mt-1 w-full border rounded-lg p-2"></label><button class="px-4 py-2 rounded-lg bg-cyan-600 text-white">Fetch to News Studio</button></form><p class="text-xs text-slate-500 mt-2">This action only fetches and deduplicates. It never spends AI requests. Review and batch selection happen in News Studio.</p></div>

  <div class="bg-white border rounded-xl p-5"><h2 class="font-semibold mb-3">Provider, Limits & Publishing</h2><form method="POST" action="{{route('admin.ai-engine.settings')}}" class="grid md:grid-cols-2 gap-4">@csrf
    <label class="text-sm">Provider<select name="provider" class="mt-1 w-full border rounded-lg p-2"><option value="gemini" @selected(($setting->provider ?? 'gemini')==='gemini')>Google Gemini</option><option value="groq" @selected(($setting->provider ?? '')==='groq')>Groq</option></select></label>
    <label class="text-sm">API Key<input type="password" name="api_key" placeholder="Leave blank to keep existing key" class="mt-1 w-full border rounded-lg p-2"></label>
    <label class="text-sm">Model<input name="model" value="{{$setting->model ?? 'gemini-3.8-flash'}}" class="mt-1 w-full border rounded-lg p-2"></label>
    <label class="text-sm">Fallback Provider<select name="fallback_provider" class="mt-1 w-full border rounded-lg p-2"><option value="">None</option><option value="gemini" @selected(($setting->fallback_provider ?? '')==='gemini')>Google Gemini</option><option value="groq" @selected(($setting->fallback_provider ?? '')==='groq')>Groq</option></select></label>
    <label class="text-sm">Fallback API Key<input type="password" name="fallback_api_key" placeholder="Leave blank to keep existing key" class="mt-1 w-full border rounded-lg p-2"></label><label class="text-sm">Fallback Model<input name="fallback_model" value="{{$setting->fallback_model ?? ''}}" placeholder="Provider default" class="mt-1 w-full border rounded-lg p-2"></label>
    <label class="text-sm">Max items/request<input type="number" min="1" max="100" name="max_items_per_request" value="{{$setting->max_items_per_request ?? 10}}" class="mt-1 w-full border rounded-lg p-2"></label><label class="text-sm">Daily requests<input type="number" name="daily_request_limit" value="{{$setting->daily_request_limit ?? 100}}" class="mt-1 w-full border rounded-lg p-2"></label><label class="text-sm">Daily tokens<input type="number" name="daily_token_limit" value="{{$setting->daily_token_limit ?? 1000000}}" class="mt-1 w-full border rounded-lg p-2"></label><label class="text-sm">Monthly budget<input type="number" step="0.01" name="monthly_budget" value="{{$setting->monthly_budget ?? ''}}" class="mt-1 w-full border rounded-lg p-2"></label>
    <div class="flex flex-wrap gap-4 items-center pt-6 text-sm"><label><input type="checkbox" name="enabled" value="1" @checked($setting?->enabled ?? true)> Enabled</label><label><input type="checkbox" name="auto_publish_news" value="1" @checked($setting?->auto_publish_news ?? false)> Auto-publish News</label><label><input type="checkbox" name="auto_publish_jobs" value="1" @checked($setting?->auto_publish_jobs ?? false)> Auto-publish Jobs</label></div>
    <div><button class="px-4 py-2 rounded-lg bg-slate-900 text-white">Save Settings</button>@if($setting)<span class="ml-3 text-xs text-slate-500">Primary encrypted key • {{$setting->maskedKey()}} @if($setting->fallback_api_key) • Fallback encrypted key • {{$setting->maskedFallbackKey()}} @endif</span>@endif</div>
  </form></div>

  <div class="bg-white border rounded-xl p-5"><h2 class="font-semibold mb-3">Jobs AI Processing</h2><p class="text-xs text-slate-500 mb-3">Jobs continue through the existing Jobs ContentEngine. No Jobs API/model/routes are changed by the News pipeline.</p><form method="POST" action="{{route('admin.ai-engine.process')}}">@csrf<input type="hidden" name="type" value="job">@foreach($jobIds as $id)<input type="hidden" name="ids[]" value="{{$id}}">@endforeach<button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Process latest Jobs ({{count($jobIds)}})</button></form></div>

  @if($failedNews->count())<div class="bg-white border rounded-xl p-5"><h2 class="font-semibold mb-3">Failed News / Retry</h2><div class="space-y-2">@foreach($failedNews as $item)<div class="flex flex-wrap items-center justify-between gap-3 border rounded-lg p-3"><div><div class="font-medium">News #{{$item->source_id}}</div><div class="text-xs text-red-600 mt-1">{{$item->error_message}}</div></div><form method="POST" action="{{route('admin.ai-engine.retry',$item)}}">@csrf<button class="px-3 py-1.5 rounded-lg border text-sm text-blue-600">Regenerate</button></form></div>@endforeach</div></div>@endif

  <div class="bg-white border rounded-xl overflow-hidden"><div class="p-5 border-b"><h2 class="font-semibold">AI Processing Records</h2></div><div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-50"><tr><th class="p-3 text-left">ID</th><th class="p-3 text-left">Type</th><th class="p-3 text-left">Source</th><th class="p-3 text-left">Status</th><th class="p-3 text-left">Tokens</th><th class="p-3 text-left">Processed</th><th class="p-3 text-right">Actions</th></tr></thead><tbody>@forelse($contents as $item)<tr class="border-t"><td class="p-3">{{$item->id}}</td><td class="p-3 uppercase">{{$item->source_type}}</td><td class="p-3 max-w-md truncate">{{data_get($item->source_snapshot,'title','Source #'.$item->source_id)}}</td><td class="p-3">{{$item->status}}</td><td class="p-3">{{number_format($item->input_tokens+$item->output_tokens)}}</td><td class="p-3">{{$item->processed_at?->format('d M Y H:i') ?? '—'}}</td><td class="p-3 text-right">@if($item->generated_content)<a class="text-purple-600 mr-3" href="{{route('admin.ai-engine.preview',$item)}}">Preview</a>@endif<form class="inline" method="POST" action="{{route('admin.ai-engine.retry',$item)}}">@csrf<button class="text-blue-600 mr-3">Regenerate</button></form>@if($item->status==='generated')<form class="inline" method="POST" action="{{route('admin.ai-engine.publish',$item)}}">@csrf<button class="text-emerald-600">Publish</button></form>@endif</td></tr>@empty<tr><td colspan="7" class="p-8 text-center text-slate-500">No AI content processed yet.</td></tr>@endforelse</tbody></table></div><div class="p-4">{{$contents->links()}}</div></div>
</div>
@endsection
