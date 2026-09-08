@extends('layouts.web')

@section('title', $article->title . ' | CGJobs Current Affairs')
@section('meta_description', Str::limit(strip_tags($article->summary), 150))

@section('content')
<div class="max-w-4xl mx-auto space-y-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 text-xs text-slate-500">
        <a href="{{ url('/current-affairs') }}" class="hover:text-brand-600 transition">
            <i class="fa-solid fa-house mr-1"></i> समसामयिकी
        </a>
        <span>/</span>
        <a href="{{ url('/current-affairs?category=' . urlencode($article->category)) }}" class="text-brand-600 font-semibold hover:underline">
            {{ $article->category }}
        </a>
        <span>/</span>
        <span class="truncate max-w-[200px] text-slate-400">{{ Str::limit($article->title, 35) }}</span>
    </nav>

    <!-- Main Article Card -->
    <article class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden p-6 sm:p-10 space-y-6">

        <!-- Category & Time Header -->
        <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-xl bg-brand-50 text-brand-700 font-bold text-xs border border-brand-200">
                    <i class="fa-solid fa-tag mr-1 text-[10px]"></i> {{ $article->category }}
                </span>
                @if($article->is_breaking)
                    <span class="px-2.5 py-1 rounded-xl bg-rose-500 text-white font-bold text-[10px] animate-pulse">
                        BREAKING
                    </span>
                @endif
            </div>

            <div class="text-xs text-slate-400 flex items-center space-x-3">
                <span><i class="fa-regular fa-calendar mr-1"></i> {{ $article->published_at ?: date('d M Y') }}</span>
                <span>•</span>
                <span><i class="fa-regular fa-clock mr-1"></i> {{ $article->relative_time ?: 'आज' }}</span>
            </div>
        </div>

        <!-- Headline -->
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 leading-tight">
            {{ $article->title }}
        </h1>

        <!-- Optional Banner Image -->
        @if($article->image_url)
            <div class="rounded-2xl overflow-hidden bg-slate-100 max-h-96 w-full shadow-inner">
                <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover" onerror="this.style.display='none'">
            </div>
        @endif

        <!-- 60-Word Inshorts Capsule Box -->
        <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-5 space-y-2">
            <div class="flex items-center space-x-2 text-xs font-bold text-emerald-900 uppercase tracking-wider">
                <i class="fa-solid fa-bolt text-emerald-600"></i>
                <span>60-शब्द त्वरित सारांश (Inshorts Capsule)</span>
            </div>
            <p class="text-sm text-emerald-950 font-medium leading-relaxed">
                {{ $article->summary }}
            </p>
        </div>

        <!-- Exam Takeaway / परीक्षा दृष्टि Box (Crucial for CGPSC / CGSSB Aspirants) -->
        @if($article->exam_takeaway)
            <div class="bg-amber-50/90 border border-amber-300/80 rounded-2xl p-5 space-y-3 shadow-sm">
                <div class="flex items-center space-x-2 text-xs font-bold text-amber-900 uppercase tracking-wider">
                    <i class="fa-solid fa-graduation-cap text-amber-600 text-sm"></i>
                    <span>परीक्षा दृष्टि (CGPSC, व्यापम व आयोग परीक्षाओं हेतु मुख्य बिंदु)</span>
                </div>
                <div class="text-xs sm:text-sm text-amber-950 leading-relaxed whitespace-pre-line font-medium">
                    {{ $article->exam_takeaway }}
                </div>
            </div>
        @endif

        <!-- Detailed Long-Form Article Body -->
        <div class="space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed pt-2 border-t border-slate-100">
            <h3 class="text-base font-bold text-slate-900">विस्तृत विवरण व विश्लेषण (Full Details)</h3>
            <p class="leading-loose whitespace-pre-line">
                {{ $article->detailed_content ?: $article->summary }}
            </p>
        </div>

        <!-- MANDATORY SOURCE CITATION & ATTRIBUTION BOX -->
        <div class="mt-8 bg-slate-50 border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 uppercase tracking-wider">
                    <i class="fa-solid fa-quote-left text-brand-600 text-sm"></i>
                    <span>स्रोत साभार एवं संदर्भ (Source Attribution)</span>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200 text-slate-700">
                    Verified Media / Official Govt
                </span>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed">
                यह समसामयिकी लेख प्रतियोगी परीक्षा अभ्यर्थियों के शैक्षिक मार्गदर्शन हेतु 
                <strong class="text-slate-800">{{ $article->source ?: 'शासकीय विज्ञप्ति / समाचार माध्यम' }}</strong> 
                पर प्रकाशित मूल समाचार से संदर्भित किया गया है।
            </p>

            @if($article->source_url)
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-[11px] text-slate-500 truncate max-w-sm">
                        मूल समाचार लिंक: <span class="font-mono text-slate-600">{{ Str::limit($article->source_url, 45) }}</span>
                    </div>

                    <a href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center space-x-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition shadow-sm">
                        <span>मूल स्रोत पोर्टल पर पढ़ें (Read at {{ $article->source ?: 'Source' }})</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
            @endif
        </div>

        <!-- Quick Share & Navigation Bar -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <a href="{{ url('/current-affairs') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-slate-600 hover:text-brand-600 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>सभी समसामयिकी देखें</span>
            </a>

            <div class="flex items-center space-x-2">
                <button onclick="navigator.clipboard.writeText(window.location.href); alert('लिंक कॉपी हो गया!');" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 text-xs font-semibold flex items-center space-x-1.5" title="लिंक कॉपी करें">
                    <i class="fa-solid fa-link"></i>
                    <span class="hidden sm:inline">शेयर लिंक</span>
                </button>
            </div>
        </div>

    </article>

    <!-- Related Exam Articles -->
    @if($related->isNotEmpty())
        <div class="space-y-4 pt-6">
            <h3 class="text-lg font-bold text-slate-900 flex items-center space-x-2">
                <i class="fa-solid fa-book-bookmark text-brand-600"></i>
                <span>अन्य संबंधित समसामयिकी (Related Current Affairs)</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($related as $rel)
                    <a href="{{ url('/current-affairs/' . ($rel->slug ?: $rel->id)) }}" class="block bg-white p-4 rounded-2xl border border-slate-200 hover:border-brand-500/50 hover:shadow-sm transition space-y-2 group">
                        <div class="flex items-center justify-between text-[10px] text-slate-400">
                            <span class="font-bold text-brand-600">{{ $rel->category }}</span>
                            <span>{{ $rel->relative_time ?: 'हाल ही में' }}</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 line-clamp-2 group-hover:text-brand-600 transition">
                            {{ $rel->title }}
                        </h4>
                        <p class="text-[11px] text-slate-500 line-clamp-2">
                            {{ $rel->summary }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
