@extends('layouts.admin')

@section('title', 'सभी भर्तियां (All Jobs)')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">भर्ती व रोजगार अधिसूचनाएं</h2>
            <p class="text-xs text-slate-500 mt-0.5">छत्तीसगढ़ के सभी विभागों की रिक्तियों को देखें, संपादित करें या हटाएं</p>
        </div>
        <a href="{{ route('admin.jobs.create') }}" class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
            <i class="fa-solid fa-plus mr-2"></i> नई भर्ती पोस्ट करें
        </a>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <form method="GET" action="{{ route('admin.jobs.index') }}" class="flex-1 flex flex-wrap gap-3 items-center w-full">
            <div class="relative flex-1 min-w-[240px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="शीर्षक, पद या योग्यता खोजें..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="w-44">
                <select name="category" onchange="this.form.submit()" class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="All">सभी विभाग (All)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ request('category') == $cat->name ? 'selected' : '' }}>
                            {{ $cat->hindi_name ?: $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition">
                फिल्टर करें
            </button>
            @if(request('search') || (request('category') && request('category') !== 'All'))
                <a href="{{ route('admin.jobs.index') }}" class="px-3 py-2 text-xs text-rose-600 hover:underline">
                    रीसेट
                </a>
            @endif
        </form>
    </div>

    <!-- Jobs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 font-semibold">ID</th>
                        <th class="py-3 px-4 font-semibold">शीर्षक व विवरण</th>
                        <th class="py-3 px-4 font-semibold">विभाग (Category)</th>
                        <th class="py-3 px-4 font-semibold">पद संख्या</th>
                        <th class="py-3 px-4 font-semibold">अंतिम तिथि</th>
                        <th class="py-3 px-4 font-semibold text-right">कार्रवाई</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jobs as $job)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-xs font-mono text-slate-400">
                                #{{ $job->id }}
                            </td>
                            <td class="py-3.5 px-4 max-w-md">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1">{{ $job->title }}</div>
                                <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $job->summary }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($job->is_breaking)
                                        <span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 text-[10px] font-bold rounded">HOT</span>
                                    @endif
                                    @if($job->official_notification_url)
                                        <a href="{{ $job->official_notification_url }}" target="_blank" class="text-[11px] text-blue-600 hover:underline">
                                            <i class="fa-regular fa-file-pdf mr-0.5"></i> PDF
                                        </a>
                                    @endif
                                    @if($job->apply_url)
                                        <a href="{{ $job->apply_url }}" target="_blank" class="text-[11px] text-emerald-600 hover:underline">
                                            <i class="fa-solid fa-arrow-up-right-from-square mr-0.5"></i> Apply
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 bg-blue-50 text-brand-700 border border-blue-100 rounded-lg text-xs font-semibold">
                                    {{ $job->category }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs font-semibold text-slate-700">
                                {{ $job->vacancies ?: '—' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-600">
                                {{ $job->last_date ?: 'शीघ्र' }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.jobs.edit', $job) }}" class="p-1.5 rounded-lg text-brand-600 hover:bg-brand-50 transition" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('क्या आप इस अधिसूचना को हटाना चाहते हैं?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                कोई भर्ती नहीं मिली। <a href="{{ route('admin.jobs.create') }}" class="text-brand-600 underline">नई भर्ती जोड़ें</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
