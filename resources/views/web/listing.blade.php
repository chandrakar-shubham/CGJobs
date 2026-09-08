@extends('web.layout')

@section('title', $title . ' — CGJobs Portal')

@section('content')
@php($lang = request('lang', session('language', 'hi')) === 'en' ? 'en' : 'hi')

<!-- Header Banner -->
<section class="bg-gradient-to-r from-slate-900 via-brand-950 to-brand-900 text-white py-12 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-brand-500/20 text-brand-300 border border-brand-500/30">
                <i class="fa-solid fa-briefcase mr-1.5 text-[10px]"></i>
                {{ $type === 'jobs' ? 'Recruitment Directory' : ($type === 'gk' ? 'Knowledge Hub' : 'Daily Current Affairs') }}
            </span>
            <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-white">
                {{ $title }}
            </h1>
            <p class="text-slate-300 text-sm leading-relaxed">
                {{ $type === 'jobs' ? 'छत्तीसगढ़ एवं केंद्रीय भर्ती की सटीक जानकारी — CGSSB व्यापम, CGPSC, संविदा एवं केंद्रीय विभागों की नवीनतम रिक्तियां।' : ($type === 'gk' ? 'प्रतियोगी परीक्षाओं के लिए उच्च स्तरीय छत्तीसगढ़ सामान्य ज्ञान एवं परीक्षा उपयोगी नोट्स।' : 'CGPSC व व्यापम परीक्षाओं हेतु विशेष रूप से तैयार 60-शब्दों के समसामयिकी कैप्सूल।') }}
            </p>
        </div>
    </div>
</section>

<!-- Filter & Search Toolbar -->
<section class="py-10 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- Modern Filter Card -->
        <form method="GET" action="{{ url()->current() }}" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">
                
                <!-- Search Query -->
                <div class="{{ $type === 'jobs' ? 'lg:col-span-5' : 'lg:col-span-8' }}">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">
                        {{ $lang === 'en' ? 'Keyword Search' : 'खोजें (पद, विभाग, योग्यता)' }}
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="search" name="q" value="{{ $search }}" placeholder="{{ $lang === 'en' ? 'Search title, department...' : 'उदा. पुलिस, सहायक प्राध्यापक, व्यापम...' }}" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none text-sm font-semibold text-slate-900 transition">
                    </div>
                </div>

                @if($type === 'jobs')
                    <!-- Category Filter -->
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">
                            {{ $lang === 'en' ? 'Job Category' : 'भर्ती श्रेणी' }}
                        </label>
                        <select name="job_category" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand-500 outline-none text-sm font-semibold text-slate-800 transition">
                            <option value="">{{ $lang === 'en' ? 'All Categories (4 Types)' : 'सभी श्रेणियां (4 प्रकार)' }}</option>
                            @foreach($jobCategories as $c)
                                <option value="{{ $c }}" @selected($selectedJobCategory === $c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">
                            {{ $lang === 'en' ? 'Department' : 'विभाग' }}
                        </label>
                        <select name="department" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:border-brand-500 outline-none text-sm font-semibold text-slate-800 transition">
                            <option value="">{{ $lang === 'en' ? 'All Departments' : 'सभी विभाग' }}</option>
                            @foreach($departments as $d)
                                <option value="{{ $d }}" @selected($selectedDepartment === $d)>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Sorting -->
                <div class="{{ $type === 'jobs' ? 'lg:col-span-2' : 'lg:col-span-4' }} flex gap-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs shadow-md shadow-brand-600/20 transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-filter text-[11px]"></i>
                        <span>{{ $lang === 'en' ? 'Apply Filter' : 'फ़िल्टर करें' }}</span>
                    </button>
                </div>

            </div>

            <!-- Clear Active Filters -->
            @if($search || $selectedCategory || ($type==='jobs' && ($selectedJobCategory || $selectedDepartment)) || $sort!=='latest')
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">फ़िल्टर सक्रिय हैं</span>
                    <a href="{{ url()->current() }}" class="text-rose-600 hover:text-rose-700 font-bold transition">
                        फ़िल्टर हटाएं (Reset) ×
                    </a>
                </div>
            @endif
        </form>

        <!-- Count Indicator -->
        <div class="flex items-center justify-between text-xs font-bold text-slate-500 px-1">
            <span>कुल {{ $items->total() }} परिणाम प्राप्त हुए</span>
            <div class="flex items-center space-x-2">
                <span>क्रमबद्ध:</span>
                <a href="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" class="px-2 py-1 rounded {{ $sort === 'latest' ? 'bg-brand-50 text-brand-700 font-extrabold' : 'text-slate-600' }}">नवीनतम</a>
                @if($type === 'jobs')
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'closing']) }}" class="px-2 py-1 rounded {{ $sort === 'closing' ? 'bg-brand-50 text-brand-700 font-extrabold' : 'text-slate-600' }}">शीघ्र बंद होने वाले</a>
                @endif
            </div>
        </div>

        <!-- Grid of Items -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($items as $item)
                @if($type === 'gk')
                    <!-- GK Card -->
                    <a href="{{ route('gk.show', $item->custom_id ?: $item->id) }}" class="job-card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between group">
                        <div class="space-y-3">
                            <span class="px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                                {{ $item->category ?: 'Static GK' }}
                            </span>
                            <h3 class="text-base font-extrabold text-slate-900 group-hover:text-purple-600 transition leading-snug">
                                {{ $item->hindi_title ?: $item->title }}
                            </h3>
                            @if($item->question)
                                <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                    {{ $item->question }}
                                </p>
                            @endif
                            @if($item->answer)
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 font-medium line-clamp-3">
                                    {{ strip_tags($item->answer) }}
                                </div>
                            @endif
                        </div>

                        <div class="pt-4 mt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-purple-700">
                            <span>पूरा उत्तर एवं विश्लेषण पढ़ें</span>
                            <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                        </div>
                    </a>
                @else
                    <!-- Job / Current Affairs Card -->
                    <article class="job-card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between group">
                        <div class="space-y-3">
                            
                            <!-- Badges -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center space-x-1.5">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                                        {{ $type === 'jobs' ? ($item->job_category ?: 'CGSSB') : ($item->category ?: 'Current Affairs') }}
                                    </span>
                                    @if($item->is_new)
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-emerald-500 text-white">
                                            NEW
                                        </span>
                                    @endif
                                    @if($item->is_breaking)
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-rose-500 text-white animate-pulse">
                                            HOT
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400 font-medium">
                                    {{ $item->relative_time ?: ($item->published_at ?: 'आज') }}
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 class="text-base font-extrabold text-slate-900 group-hover:text-brand-600 transition leading-snug line-clamp-2">
                                <a href="{{ route($type === 'current-affairs' ? 'current-affairs.show' : 'job.show', $item->custom_id ?: $item->id) }}">
                                    {{ $item->title }}
                                </a>
                            </h3>

                            <!-- Summary -->
                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">
                                {{ $item->summary ?: 'विस्तृत विवरण एवं आधिकारिक अधिसूचना डाउनलोड करने के लिए देखें।' }}
                            </p>

                            <!-- Highlights (Vacancies / Last date) -->
                            @if($type === 'jobs')
                                <div class="grid grid-cols-2 gap-2 pt-2 text-xs">
                                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                        <span class="text-[10px] text-slate-400 font-bold block uppercase">कुल पद</span>
                                        <span class="font-black text-slate-800 text-xs truncate block">
                                            👥 {{ $item->vacancies ?: 'विज्ञप्ति अनुसार' }}
                                        </span>
                                    </div>
                                    <div class="p-2 rounded-xl bg-rose-50/70 border border-rose-100">
                                        <span class="text-[10px] text-rose-500 font-bold block uppercase">अंतिम तिथि</span>
                                        <span class="font-black text-rose-700 text-xs truncate block">
                                            📅 {{ $item->last_date ?: 'शीघ्र' }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                        </div>

                        <!-- Bottom Link -->
                        <div class="pt-4 mt-4 border-t border-slate-100">
                            <a href="{{ route($type === 'current-affairs' ? 'current-affairs.show' : 'job.show', $item->custom_id ?: $item->id) }}" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-brand-600 hover:text-white text-slate-800 font-extrabold text-xs transition flex items-center justify-center space-x-2">
                                <span>{{ $type === 'jobs' ? 'विवरण व ऑनलाइन आवेदन' : 'पूरा कैप्सूल पढ़ें' }}</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </article>
                @endif
            @empty
                <div class="col-span-3 text-center py-20 bg-white rounded-3xl border border-slate-200/80 p-8 space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-2xl mx-auto">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">कोई परिणाम नहीं मिला</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        कृपया अन्य शब्द खोजें या फ़िल्टर हटाकर पुनः प्रयास करें।
                    </p>
                    <a href="{{ url()->current() }}" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-brand-600 text-white text-xs font-bold shadow-md hover:bg-brand-700 transition">
                        <span>सभी परिणाम देखें</span>
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($items->hasPages())
            <div class="pt-8 flex justify-center">
                {{ $items->links() }}
            </div>
        @endif

    </div>
</section>
@endsection
