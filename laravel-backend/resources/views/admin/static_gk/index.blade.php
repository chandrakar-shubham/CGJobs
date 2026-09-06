@extends('layouts.admin')

@section('title', 'Static GK प्रबंधन')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
                <i class="fa-solid fa-book-open text-indigo-600"></i>
                <span>Static GK व सामान्य ज्ञान प्रबंधन</span>
            </h2>
            <p class="text-sm text-slate-500 mt-1">छत्तीसगढ़ इतिहास, भूगोल, जनजाति व संस्कृति के महत्वपूर्ण परीक्षा नोट्स व प्रश्न</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.static-gk.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="fa-solid fa-plus"></i>
                <span>नया GK कार्ड जोड़ें (Add GK)</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80">
        <form method="GET" action="{{ route('admin.static-gk.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="शीर्षक, प्रश्न या उत्तर खोजें..." class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="w-48">
                <select name="category" onchange="this.form.submit()" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">सभी श्रेणियां (All Categories)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ request('category') == $cat->name ? 'selected' : '' }}>{{ $cat->hindi_name ?: $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-semibold hover:bg-slate-900 transition">
                <i class="fa-solid fa-filter mr-1.5"></i> फ़िल्टर
            </button>
            @if(request('search') || request('category'))
                <a href="{{ route('admin.static-gk.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-700 text-sm">
                    रीसेट
                </a>
            @endif
        </form>
    </div>

    <!-- GK Cards Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-4">शीर्षक व श्रेणी</th>
                        <th class="py-3.5 px-4">मुख्य बिंदु / प्रश्न</th>
                        <th class="py-3.5 px-4">परीक्षा संदर्भ</th>
                        <th class="py-3.5 px-4 text-center">सत्यापित</th>
                        <th class="py-3.5 px-4 text-right">कार्रवाई (Actions)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="font-bold text-slate-800 leading-snug">{{ $item->title }}</div>
                                <div class="text-xs text-indigo-600 font-medium mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ $item->category_hindi ?: $item->category }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 max-w-sm">
                                @if($item->question)
                                    <div class="text-xs font-semibold text-slate-700">Q: {{ Str::limit($item->question, 60) }}</div>
                                @endif
                                @if(is_array($item->key_points) && count($item->key_points) > 0)
                                    <ul class="text-[11px] text-slate-500 list-disc list-inside mt-1 space-y-0.5">
                                        @foreach(array_slice($item->key_points, 0, 2) as $pt)
                                            <li>{{ Str::limit($pt, 50) }}</li>
                                        @endforeach
                                    </ul>
                                @elseif($item->detailed_notes)
                                    <p class="text-xs text-slate-500 line-clamp-2 mt-1">{{ Str::limit($item->detailed_notes, 80) }}</p>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs text-slate-600">
                                {{ $item->year_exam_reference ?: 'सामान्य अध्ययन' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->is_verified)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        <i class="fa-solid fa-check mr-1"></i> Verified
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('admin.static-gk.edit', $item) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> संपादित करें
                                </a>
                                <form action="{{ route('admin.static-gk.destroy', $item) }}" method="POST" class="inline" onsubmit="return confirm('क्या आप वाकई इस GK कार्ड को हटाना चाहते हैं?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                        <i class="fa-solid fa-trash mr-1"></i> हटाएं
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                                <i class="fa-solid fa-book-bookmark text-3xl mb-2 block text-slate-300"></i>
                                कोई Static GK कार्ड नहीं मिला।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
