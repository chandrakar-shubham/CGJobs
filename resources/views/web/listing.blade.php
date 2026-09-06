@extends('web.layout')
@section('title',$title.' — CGJobs')
@section('content')
<section class="container mx-auto px-4 pt-12 pb-6"><div class="max-w-3xl"><div class="text-xs font-black uppercase tracking-widest text-slate-400">CGJobs</div><h1 class="text-3xl md:text-5xl font-black tracking-tight mt-2">{{ $title }}</h1><p class="text-slate-500 mt-3">नए updates को पढ़ें, सेव करें और चाहें तो exact content को Android app में खोलें।</p></div></section>
<section class="container mx-auto px-4 pb-14"><div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
@forelse($items as $item)
@if($type === 'gk')
<a href="{{ route('gk.show',$item->custom_id ?: $item->id) }}" class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-xl transition"><span class="text-xs font-bold text-slate-400">{{ $item->category ?: 'GK' }}</span><h2 class="font-extrabold text-lg mt-2 line-clamp-2">{{ $item->hindi_title ?: $item->title }}</h2><p class="text-sm text-slate-500 mt-2 line-clamp-3">{{ $item->answer ?: $item->detailed_notes }}</p><span class="inline-block mt-5 text-sm font-black">Read GK →</span></a>
@else
<a href="{{ route('job.show',$item->custom_id ?: $item->id) }}" class="bg-white border border-slate-200 rounded-2xl p-5 hover:shadow-xl transition"><div class="flex items-center gap-2 text-xs"><span class="bg-slate-100 rounded-full px-2 py-1 font-bold">{{ $item->category ?: 'Update' }}</span><span class="text-slate-400">{{ $item->relative_time ?: 'हाल ही में' }}</span></div><h2 class="font-extrabold text-lg mt-4 line-clamp-2">{{ $item->title }}</h2><p class="text-sm text-slate-500 mt-2 line-clamp-3">{{ $item->summary }}</p>@if($item->last_date)<div class="mt-4 text-xs font-bold">Last date: {{ $item->last_date }}</div>@endif<span class="inline-block mt-5 text-sm font-black">Read full update →</span></a>
@endif
@empty<div class="col-span-full bg-white border rounded-2xl p-12 text-center text-slate-500">अभी इस section में content उपलब्ध नहीं है।</div>@endforelse</div>
<div class="mt-8">{{ $items->links() }}</div></section>
@endsection