@extends('web.layout')

@section('title', $article->title . ' — ' . ($lang === 'en' ? 'CGJobs Current Affairs' : 'CGJobs समसामयिकी'))

@push('head')
<meta name="description" content="{{ Str::limit(strip_tags($article->summary), 160) }}">
<meta property="og:title" content="{{ $article->title }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($article->summary), 160) }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ url()->current() }}">
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $article->title,
    'description' => strip_tags($article->summary),
    'datePublished' => $article->published_at ?: optional($article->created_at)->toIso8601String(),
    'dateModified' => optional($article->updated_at)->toIso8601String(),
    'mainEntityOfPage' => url()->current(),
    'url' => url()->current(),
    'publisher' => ['@type' => 'Organization', 'name' => 'CGJobs Portal']
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-6">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="hover:text-brand-600 transition flex items-center">
                <i class="fa-solid fa-house text-slate-400 mr-1.5"></i>
                <span>{{ $lang === 'en' ? 'Home' : 'होम' }}</span>
            </a>
            <span>/</span>
            <a href="{{ route('listing.current-affairs', ['lang' => $lang]) }}" class="hover:text-brand-600 transition">
                {{ $lang === 'en' ? 'Current Affairs' : 'समसामयिकी' }}
            </a>
            @if($article->category)
                <span>/</span>
                <a href="{{ route('listing.current-affairs', ['category' => $article->category, 'lang' => $lang]) }}" class="text-brand-600 font-bold hover:underline">
                    {{ $article->category }}
                </a>
            @endif
            <span>/</span>
            <span class="text-slate-400 truncate max-w-xs sm:max-w-sm">{{ Str::limit($article->title, 40) }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Main Content Area -->
            <article class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm space-y-6">

                <!-- Category & Time Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-5 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                            <i class="fa-solid fa-newspaper mr-1 text-[10px]"></i>
                            {{ $article->category ?: ($lang === 'en' ? 'Current Affairs' : 'समसामयिकी') }}
                        </span>
                        @if($article->is_breaking)
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-500 text-white animate-pulse">
                                BREAKING
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-400 flex items-center space-x-3">
                        <span><i class="fa-regular fa-calendar mr-1"></i> {{ $article->published_at ?: optional($article->created_at)->format('d M Y') }}</span>
                        <span>•</span>
                        <span><i class="fa-regular fa-clock mr-1"></i> {{ $article->relative_time ?: ($lang === 'en' ? 'Today' : 'आज') }}</span>
                    </div>
                </div>

                <!-- Main Headline -->
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight">
                    {{ $article->title }}
                </h1>

                <!-- Optional Banner Image -->
                @if($article->image_url)
                    <div class="rounded-2xl overflow-hidden bg-slate-100 max-h-96 w-full shadow-inner border border-slate-100">
                        <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                    </div>
                @endif

                <!-- 60-Word Inshorts Capsule Box -->
                @if($article->summary)
                    <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-5 sm:p-6 space-y-2">
                        <div class="flex items-center space-x-2 text-xs font-black text-emerald-900 uppercase tracking-wider">
                            <i class="fa-solid fa-bolt text-emerald-600"></i>
                            <span>{{ $lang === 'en' ? '60-Word Inshorts Capsule' : '60-शब्द त्वरित सारांश (INSHORTS CAPSULE)' }}</span>
                        </div>
                        <p class="text-sm sm:text-base text-emerald-950 font-medium leading-relaxed">
                            {{ $article->summary }}
                        </p>
                    </div>
                @endif

                <!-- Exam Takeaway / परीक्षा दृष्टि Box -->
                @if($article->exam_takeaway)
                    <div class="bg-amber-50/90 border border-amber-300/80 rounded-2xl p-5 sm:p-6 space-y-3 shadow-sm">
                        <div class="flex items-center space-x-2 text-xs font-black text-amber-900 uppercase tracking-wider">
                            <i class="fa-solid fa-graduation-cap text-amber-600 text-sm"></i>
                            <span>{{ $lang === 'en' ? 'Exam Focus (CGPSC, Vyapam & State Exams)' : 'परीक्षा दृष्टि (CGPSC, व्यापम व आयोग परीक्षाओं हेतु मुख्य बिंदु)' }}</span>
                        </div>
                        <div class="text-xs sm:text-sm text-amber-950 leading-relaxed whitespace-pre-line font-medium">
                            {{ $article->exam_takeaway }}
                        </div>
                    </div>
                @endif

                <!-- Detailed Long-Form Article Body -->
                @if($article->detailed_content)
                    <div class="space-y-4 text-sm sm:text-base text-slate-700 leading-relaxed pt-4 border-t border-slate-100">
                        <h2 class="text-base font-black text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-align-left text-brand-600"></i>
                            <span>{{ $lang === 'en' ? 'Detailed Analysis & Background' : 'विस्तृत विवरण व विश्लेषण (Full Details)' }}</span>
                        </h2>
                        <div class="prose prose-slate max-w-none text-sm sm:text-base leading-relaxed text-slate-700 whitespace-pre-line">
                            {!! strip_tags($article->detailed_content, '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote>') !!}
                        </div>
                    </div>
                @endif

                <!-- Mandatory Source Citation & Attribution Box -->
                <div class="mt-8 bg-slate-50 border border-slate-200 rounded-2xl p-5 sm:p-6 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2 text-xs font-bold text-slate-700 uppercase tracking-wider">
                            <i class="fa-solid fa-quote-left text-brand-600 text-sm"></i>
                            <span>{{ $lang === 'en' ? 'Source Citation & Attribution' : 'स्रोत साभार एवं संदर्भ (Source Attribution)' }}</span>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-200 text-slate-700">
                            Verified Official / Media
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        {{ $lang === 'en'
                            ? 'This article is curated for educational reference and competitive examination guidance, sourced from official releases and news portals: '
                            : 'यह समसामयिकी लेख प्रतियोगी परीक्षा अभ्यर्थियों के शैक्षिक मार्गदर्शन हेतु शासकीय विज्ञप्ति / समाचार माध्यम से संदर्भित किया गया है: ' }}
                        <strong class="text-slate-800">{{ $article->source ?: 'शासकीय विज्ञप्ति / DPRCG / PIB' }}</strong>.
                    </p>

                    @if($article->source_url)
                        <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="text-[11px] text-slate-500 truncate max-w-sm">
                                {{ $lang === 'en' ? 'Source Link:' : 'मूल समाचार लिंक:' }}
                                <span class="font-mono text-slate-600">{{ Str::limit($article->source_url, 45) }}</span>
                            </div>

                            <a href="{{ $article->source_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center space-x-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold transition shadow-sm">
                                <span>{{ $lang === 'en' ? 'Read at Official Source ↗' : 'मूल स्रोत पोर्टल पर पढ़ें ↗' }}</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Navigation & Share Toolbar -->
                <div class="pt-6 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <a href="{{ route('listing.current-affairs', ['lang' => $lang]) }}" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 text-xs font-bold transition">
                        <i class="fa-solid fa-arrow-left text-[11px]"></i>
                        <span>{{ $lang === 'en' ? 'Back to Current Affairs' : 'सभी समसामयिकी देखें' }}</span>
                    </a>

                    <div class="flex items-center space-x-2">
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('लिंक कॉपी हो गया!');" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold flex items-center space-x-2 transition" title="Copy Link">
                            <i class="fa-solid fa-link text-slate-500"></i>
                            <span>{{ $lang === 'en' ? 'Copy Link' : 'शेयर लिंक' }}</span>
                        </button>
                    </div>
                </div>

            </article>

            <!-- Sidebar -->
            <aside class="lg:col-span-4 space-y-6">

                <!-- Related Articles -->
                @if($related->isNotEmpty())
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                        <div class="flex items-center space-x-2 pb-3 border-b border-slate-100">
                            <i class="fa-solid fa-newspaper text-brand-600"></i>
                            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider">
                                {{ $lang === 'en' ? 'Related Current Affairs' : 'अन्य संबंधित समसामयिकी' }}
                            </h3>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($related as $rel)
                                <a href="{{ route('current-affairs.show', $rel->slug ?: ($rel->custom_id ?: $rel->id)) }}?lang={{ $lang }}" class="py-3 block group">
                                    <span class="text-[10px] font-bold text-brand-600 uppercase block mb-1">
                                        {{ $rel->category ?: 'Current Affairs' }}
                                    </span>
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug">
                                        {{ $rel->title }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400 mt-1 block">
                                        {{ $rel->relative_time ?: ($lang === 'en' ? 'Recent' : 'हाल ही में') }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Android App Card Promo -->
                <div class="bg-gradient-to-br from-brand-900 to-slate-900 rounded-3xl p-6 text-white space-y-4 shadow-xl">
                    <div class="w-10 h-10 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-bold text-lg shadow-md">
                        <i class="fa-brands fa-android text-emerald-300"></i>
                    </div>
                    <h3 class="text-lg font-black leading-tight">
                        {{ $lang === 'en' ? 'Download CGJobs Android App' : 'CGJobs Android App डाउनलोड करें' }}
                    </h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        {{ $lang === 'en' ? 'Get instant notifications for new jobs, admit cards, exam dates and daily current affairs on your mobile.' : 'नवीनतम भर्तियों, एडमिट कार्ड और परीक्षा तिथि के त्वरित पुश नोटिफिकेशन सीधे अपने मोबाइल पर पाएं।' }}
                    </p>
                    <a href="https://play.google.com/store" target="_blank" rel="noopener" class="block text-center py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-900 font-extrabold text-xs shadow-md transition">
                        {{ $lang === 'en' ? 'Get on Google Play' : 'Google Play से पाएं' }}
                    </a>
                </div>

                <!-- Quick Job Directory Navigation -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-3">
                    <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-500">
                        {{ $lang === 'en' ? 'Explore More Opportunities' : 'अन्य अनुभाग देखें' }}
                    </h4>
                    <div class="space-y-2">
                        <a href="{{ route('listing.jobs', ['lang' => $lang]) }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-brand-50 text-slate-800 hover:text-brand-700 text-xs font-bold transition">
                            <span class="flex items-center space-x-2">
                                <i class="fa-solid fa-briefcase text-emerald-600"></i>
                                <span>{{ $lang === 'en' ? 'All Government Jobs' : 'सभी सरकारी नौकरियां' }}</span>
                            </span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                        <a href="{{ route('listing.gk', ['lang' => $lang]) }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-purple-50 text-slate-800 hover:text-purple-700 text-xs font-bold transition">
                            <span class="flex items-center space-x-2">
                                <i class="fa-solid fa-book-open text-purple-600"></i>
                                <span>Static GK & Exam Notes</span>
                            </span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

            </aside>

        </div>
    </div>
</div>
@endsection
