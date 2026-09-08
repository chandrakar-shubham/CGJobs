@extends('layouts.admin')

@section('title', 'समसामयिकी व समाचार (News & Current Affairs)')

@section('content')
<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                समसामयिकी व समाचार प्रबंधन (News / Current Affairs)
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                दैनिक समसामयिकी, परीक्षा उपयोगी करंट अफेयर्स व शासकीय समाचार विज्ञप्तियों का संपादन व प्रकाशन।
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.ai-engine.index') }}" class="px-4 py-2.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-bold transition flex items-center gap-1.5 border border-purple-200">
                <i class="fa-solid fa-robot"></i>
                <span>AI Content Engine</span>
            </a>
            <a href="{{ route('admin.news.create') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-black shadow-md shadow-brand-600/20 transition flex items-center gap-1.5">
                <i class="fa-solid fa-circle-plus"></i>
                <span>+ नया समाचार जोड़ें</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">कुल समाचार (Total)</span>
            <span class="text-2xl font-black text-slate-900">{{ $news->total() }}</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">प्रकाशित (Published)</span>
            <span class="text-2xl font-black text-emerald-600">{{ \App\Models\News::where('status', 'published')->count() }}</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block">ड्राफ्ट / समीक्षा (Draft)</span>
            <span class="text-2xl font-black text-amber-600">{{ \App\Models\News::whereIn('status', ['draft', 'review'])->count() }}</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-2xs space-y-1">
            <span class="text-[11px] font-bold text-brand-600 uppercase tracking-wider block">स्रोत लिंक युक्त (With Source)</span>
            <span class="text-2xl font-black text-brand-600">{{ \App\Models\News::whereNotNull('original_url')->where('original_url', '!=', '')->count() }}</span>
        </div>
    </div>

    <!-- Search & Filter Console -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-2xs">
        <form method="GET" action="{{ route('admin.news.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="शीर्षक या स्रोत खोजें..." class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <select name="category" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    <option value="">सभी श्रेणियां</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" @selected(request('category') === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                    <option value="">सभी स्थितियां</option>
                    @foreach(['draft', 'review', 'published', 'archived'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                    फ़िल्टर करें
                </button>
                <a href="{{ route('admin.news.index') }}" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                    रीसेट
                </a>
            </div>
        </form>
    </div>

    <!-- News Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[800px]">
                <thead class="bg-white border-b border-slate-100 text-slate-400 text-[10px] font-extrabold uppercase tracking-wider">
                    <tr>
                        <th class="p-4">समाचार शीर्षक</th>
                        <th class="p-4">स्रोत (Source)</th>
                        <th class="p-4">श्रेणी</th>
                        <th class="p-4">स्थिति</th>
                        <th class="p-4 text-right">कार्रवाई</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($news as $item)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <td class="p-4 min-w-[320px]">
                                <a href="{{ route('admin.news.edit', $item) }}" class="font-bold text-slate-900 group-hover:text-brand-600 transition line-clamp-2 text-xs sm:text-sm">
                                    {{ $item->title }}
                                </a>
                                <div class="text-[11px] text-slate-400 mt-1">
                                    ID: {{ $item->id }} • {{ $item->published_at ? $item->published_at->format('d M Y, H:i') : 'No publish date' }}
                                </div>
                            </td>

                            <td class="p-4 whitespace-nowrap text-xs">
                                <span class="font-semibold text-slate-700 block">{{ $item->source ?: 'शासकीय विज्ञप्ति' }}</span>
                                @if($item->original_url)
                                    <a href="{{ $item->original_url }}" target="_blank" rel="noopener" class="text-brand-600 text-[11px] hover:underline flex items-center gap-1 mt-0.5">
                                        <span>मूल लिंक ↗</span>
                                    </a>
                                @endif
                            </td>

                            <td class="p-4 whitespace-nowrap text-xs">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold">
                                    {{ $item->category ?: 'Current Affairs' }}
                                </span>
                            </td>

                            <td class="p-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $item->status === 'published' ? 'bg-emerald-100 text-emerald-800' : ($item->status === 'archived' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-800') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>

                            <td class="p-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($item->status !== 'published')
                                        <form method="POST" action="{{ route('admin.news.publish', $item) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                                                Publish
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.news.edit', $item) }}" class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit">
                                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.news.destroy', $item) }}" onsubmit="return confirm('क्या आप इस लेख को हटाना चाहते हैं?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-xs text-slate-400">
                                कोई समाचार उपलब्ध नहीं है। AI Content Engine से नए समाचार लाएं।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($news->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $news->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
