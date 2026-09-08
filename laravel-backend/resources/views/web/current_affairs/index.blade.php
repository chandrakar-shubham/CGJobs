@extends('web.layout')

@section('title', 'दैनिक समसामयिकी व करंट अफेयर्स | CGPSC, CGSSB, व्यापम परीक्षा विशेषांक')
@section('meta_description', 'छत्तीसगढ़ व राष्ट्रीय दैनिक करंट अफेयर्स, शासकीय योजनाएं, बजट, पुरस्कार एवं आयोग परीक्षाओं के महत्वपूर्ण बिंदु')

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

    <!-- Hero Header Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-brand-900 to-slate-900 text-white p-8 sm:p-10 shadow-xl border border-white/10">
        <div class="relative z-10 max-w-2xl space-y-3">
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-brand-500/20 text-brand-300 text-xs font-bold border border-brand-500/30">
                <span class="w-2 h-2 rounded-full bg-brand-400 animate-pulse"></span>
                <span>CGPSC & CGSSB / व्यापम विशेष बुलेटिन</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                दैनिक समसामयिकी (Current Affairs Capsule)
            </h1>
            <p class="text-sm text-slate-300 leading-relaxed">
                छत्तीसगढ़ राज्य सेवा, शिक्षक भर्ती, पुलिस आरक्षक एवं केंद्रीय सरकारी परीक्षाओं के नवीनतम घटनाक्रम, सरकारी योजनाएं एवं 'परीक्षा दृष्टि' महत्वपूर्ण तथ्य।
            </p>
        </div>
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Search & Filter Chips -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form method="GET" action="{{ url('/current-affairs') }}" class="flex-1 max-w-md relative">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="समसामयिकी खोजें (उदा: योजना, बजट, बस्तर, इसरो)..." class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-brand-500 focus:outline-none shadow-sm">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
            @if(request('q'))
                <a href="{{ url('/current-affairs') }}" class="absolute right-3.5 top-3 text-xs text-slate-400 hover:text-slate-600">✕</a>
            @endif
        </form>

        <div class="flex items-center space-x-2 overflow-x-auto pb-1 text-xs font-medium">
            <a href="{{ url('/current-affairs') }}" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ !request('category') ? 'bg-brand-600 text-white font-bold shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                सभी समसामयिकी (All)
            </a>
            @foreach($categories as $cat)
                <a href="{{ url('/current-affairs?category=' . urlencode($cat)) }}" class="px-3.5 py-2 rounded-xl transition whitespace-nowrap {{ request('category') == $cat ? 'bg-brand-600 text-white font-bold shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                    {{ $cat }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- News Grid -->
    @if($news->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center space-y-3">
            <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-2xl">
                <i class="fa-regular fa-newspaper"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800">वर्तमान में कोई लेख उपलब्ध नहीं है</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">कृपया कुछ समय बाद पुनः देखें अथवा अन्य श्रेणी चुनें।</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($news as $item)
                <article class="bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition overflow-hidden flex flex-col group">
                    @if($item->image_url)
                        <div class="relative h-44 w-full overflow-hidden bg-slate-100">
                            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" onerror="this.src='https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=600&auto=format&fit=crop&q=80'">
                            <div class="absolute top-3 left-3 bg-slate-900/80 backdrop-blur-sm text-white px-2.5 py-1 rounded-lg text-[10px] font-bold">
                                {{ $item->category }}
                            </div>
                            <div class="absolute bottom-2 right-3 text-white/90 text-[10px] font-medium drop-shadow">
                                <i class="fa-regular fa-clock mr-1"></i> {{ $item->relative_time ?: 'आज' }}
                            </div>
                        </div>
                    @else
                        <div class="p-4 pb-0 flex items-center justify-between text-xs">
                            <span class="px-2.5 py-1 rounded-lg bg-brand-50 text-brand-700 font-bold text-[10px] border border-brand-200">
                                {{ $item->category }}
                            </span>
                            <span class="text-slate-400 text-[11px]"><i class="fa-regular fa-clock mr-1"></i> {{ $item->relative_time ?: 'आज' }}</span>
                        </div>
                    @endif

                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <h2 class="text-base font-bold text-slate-900 line-clamp-2 leading-snug group-hover:text-brand-600 transition">
                                <a href="{{ url('/current-affairs/' . ($item->slug ?: $item->id)) }}">
                                    {{ $item->title }}
                                </a>
                            </h2>

                            <!-- 60-Word Inshorts Capsule -->
                            <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                                {{ $item->summary }}
                            </p>
                        </div>

                        <!-- Exam Takeaway Badge -->
                        @if($item->exam_takeaway)
                            <div class="bg-amber-50/80 border border-amber-200 rounded-xl p-2.5 text-[11px] text-amber-900 font-medium">
                                <i class="fa-solid fa-lightbulb text-amber-600 mr-1"></i>
                                <span>CGPSC/व्यापम परीक्षा दृष्टि तथ्य सम्मिलित</span>
                            </div>
                        @endif

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                            <span class="text-[11px] text-slate-400 truncate max-w-[140px]">
                                <i class="fa-solid fa-circle-check text-emerald-500 mr-1"></i>
                                स्रोत: {{ $item->source ?: 'शासकीय विज्ञप्ति' }}
                            </span>

                            <a href="{{ url('/current-affairs/' . ($item->slug ?: $item->id)) }}" class="inline-flex items-center space-x-1 font-bold text-brand-600 hover:text-brand-700">
                                <span>पूरा लेख पढ़ें</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pt-6">
            {{ $news->links() }}
        </div>
    @endif

    </div>
</div>
@endsection
