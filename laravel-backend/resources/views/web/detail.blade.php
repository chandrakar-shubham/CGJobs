@extends('web.layout')

@section('title', (($type === 'jobs' && $item->seo_title) ? $item->seo_title : $item->title) . ' — CGJobs')

@push('head')
@if($type==='jobs' && $item->seo_description)
    <meta name="description" content="{{ $item->seo_description }}">
@else
    <meta name="description" content="{{ strip_tags($item->summary) }}">
@endif
@if($type==='jobs' && $item->seo_keywords)
    <meta name="keywords" content="{{ $item->seo_keywords }}">
@endif
@if($type==='jobs' && $item->canonical_url)
    <link rel="canonical" href="{{ $item->canonical_url }}">
@endif
<meta property="og:title" content="{{ $type==='jobs' && $item->seo_title ? $item->seo_title : $item->title }}">
<meta property="og:description" content="{{ $type==='jobs' && $item->seo_description ? $item->seo_description : strip_tags($item->summary) }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ url()->current() }}">
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => $type === 'jobs' ? 'JobPosting' : 'Article',
    'headline' => $item->title,
    'description' => strip_tags($item->summary),
    'datePublished' => $item->published_at ?: optional($item->created_at)->toIso8601String(),
    'dateModified' => optional($item->updated_at)->toIso8601String(),
    'mainEntityOfPage' => url()->current(),
    'url' => url()->current(),
    'publisher' => ['@type' => 'Organization', 'name' => 'CGJobs Network']
], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<div class="bg-slate-50 py-10 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-600 transition">
                <i class="fa-solid fa-house text-slate-400 mr-1"></i> होम
            </a>
            <span>/</span>
            <a href="{{ route($type === 'jobs' ? 'listing.jobs' : 'listing.current-affairs') }}" class="hover:text-brand-600 transition">
                {{ $type === 'jobs' ? 'सरकारी नौकरियां' : 'समसामयिकी' }}
            </a>
            <span>/</span>
            <span class="text-slate-400 truncate max-w-xs">{{ Str::limit($item->title, 40) }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Main Content Area -->
            <article class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm space-y-8">
                
                <!-- Category & Badges Header -->
                <div class="flex flex-wrap items-center justify-between gap-3 pb-5 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <span class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                            <i class="fa-solid fa-tag mr-1 text-[10px]"></i>
                            {{ $type === 'jobs' ? ($item->job_category ?: 'CGSSB') : ($item->category ?: 'Current Affairs') }}
                        </span>
                        @if($type === 'jobs' && $item->department)
                            <span class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $item->department }}
                            </span>
                        @endif
                        @if($item->is_breaking)
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-rose-500 text-white animate-pulse">
                                BREAKING
                            </span>
                        @endif
                    </div>

                    <div class="text-xs text-slate-400 flex items-center space-x-2">
                        <i class="fa-regular fa-calendar text-slate-400"></i>
                        <span>{{ $item->published_at ?: optional($item->created_at)->format('d M Y') }}</span>
                    </div>
                </div>

                <!-- Main Heading -->
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight">
                    {{ $item->title }}
                </h1>

                <!-- Summary / Lead Callout -->
                @if($item->summary)
                    <div class="p-5 rounded-2xl bg-brand-50/60 border border-brand-200/80 text-sm sm:text-base text-slate-800 leading-relaxed font-medium">
                        {!! strip_tags($item->summary, '<p><br><strong><b><em><i><u>') !!}
                    </div>
                @endif

                <!-- Job Key Information Facts Grid -->
                @if($type === 'jobs')
                    <div class="space-y-3">
                        <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                            <i class="fa-solid fa-list-check text-brand-600"></i>
                            <span>महत्वपूर्ण भर्ती विवरण (Key Details)</span>
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @php
                            $factRows = [
                                ['भर्ती संस्था (Category)', $item->job_category, 'fa-building-columns'],
                                ['विभाग (Department)', $item->department, 'fa-building'],
                                ['स्रोत (Source/Authority)', $item->source, 'fa-landmark'],
                                ['पद प्रकार (Post Type)', $item->post_type, 'fa-briefcase'],
                                ['कुल पद (Vacancies)', $item->vacancies, 'fa-users'],
                                ['वेतनमान (Salary / Pay)', $item->salary, 'fa-money-bill-wave'],
                                ['शैक्षणिक योग्यता (Eligibility)', $item->eligibility, 'fa-graduation-cap'],
                                ['आयु सीमा (Age Limit)', $item->age_limit, 'fa-user-clock'],
                                ['आवेदन प्रारंभ तिथि', $item->application_start, 'fa-calendar-plus'],
                                ['आवेदन की अंतिम तिथि', $item->last_date, 'fa-calendar-xmark'],
                                ['परीक्षा तिथि (Exam Date)', $item->exam_date, 'fa-calendar-check'],
                                ['चयन प्रक्रिया (Selection)', $item->selection_process, 'fa-circle-check'],
                            ];
                            @endphp

                            @foreach($factRows as $fact)
                                @if(!empty($fact[1]))
                                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 flex items-start space-x-3">
                                        <div class="w-8 h-8 rounded-xl bg-white text-brand-600 flex items-center justify-center text-xs shadow-2xs flex-shrink-0 mt-0.5">
                                            <i class="fa-solid {{ $fact[2] }}"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <span class="text-[11px] font-bold text-slate-400 block uppercase tracking-wider">{{ $fact[0] }}</span>
                                            <span class="text-xs sm:text-sm font-black text-slate-800 block mt-0.5 break-words">{{ $fact[1] }}</span>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Detailed Rich Text Information -->
                @if($item->detailed_content)
                    <div class="space-y-4 pt-4 border-t border-slate-100">
                        <h2 class="text-lg font-black text-slate-900 flex items-center space-x-2">
                            <i class="fa-solid fa-align-left text-brand-600"></i>
                            <span>विस्तृत अधिसूचना विवरण (Detailed Notice)</span>
                        </h2>
                        <div class="prose prose-slate max-w-none text-sm sm:text-base leading-relaxed text-slate-700 space-y-4">
                            {!! strip_tags($item->detailed_content, '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote>') !!}
                        </div>
                    </div>
                @endif

                <!-- Action Buttons: Apply & Official Notification -->
                <div class="p-6 rounded-3xl bg-slate-900 text-white space-y-4 shadow-xl">
                    <div class="flex items-center space-x-3">
                        <span class="w-8 h-8 rounded-xl bg-brand-500 text-white flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-link"></i>
                        </span>
                        <div>
                            <h3 class="font-bold text-base">महत्वपूर्ण लिंक्स (Official Links)</h3>
                            <p class="text-xs text-slate-400">विभागीय पोर्टल से ऑनलाइन आवेदन व पीडीएफ डाउनलोड करें</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        @if($item->apply_url)
                            <a href="{{ $type === 'jobs' ? route('job.apply', $item->custom_id ?: $item->id) : $item->apply_url }}" target="_blank" rel="noopener" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-md transition flex items-center space-x-2">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>ऑनलाइन आवेदन करें (Apply Now) ↗</span>
                            </a>
                        @endif

                        @if($item->official_notification_url)
                            <a href="{{ $item->official_notification_url }}" target="_blank" rel="noopener" class="px-5 py-3 rounded-xl bg-brand-700 hover:bg-brand-600 text-white text-xs font-black shadow-md transition flex items-center space-x-2">
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>आधिकारिक विज्ञप्ति PDF ↗</span>
                            </a>
                        @endif

                        <a href="https://play.google.com/store" target="_blank" rel="noopener" class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/15 transition flex items-center space-x-2">
                            <i class="fa-brands fa-android text-emerald-400"></i>
                            <span>ऐप में सहेजें (Save in App)</span>
                        </a>
                    </div>
                </div>

            </article>

            <!-- Sidebar -->
            <aside class="lg:col-span-4 space-y-6">
                
                <!-- Related Jobs / Updates -->
                @if($related->count())
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                        <div class="flex items-center space-x-2 pb-3 border-b border-slate-100">
                            <i class="fa-solid fa-layer-group text-brand-600"></i>
                            <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider">
                                संबंधित {{ $type === 'jobs' ? 'भर्तियां' : 'अपडेट्स' }}
                            </h3>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @foreach($related as $r)
                                <a href="{{ route($type === 'current-affairs' ? 'current-affairs.show' : 'job.show', $r->custom_id ?: $r->id) }}" class="py-3 block group">
                                    <span class="text-[10px] font-bold text-brand-600 uppercase block mb-1">
                                        {{ $r->job_category ?: ($r->category ?: 'CGJobs') }}
                                    </span>
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-brand-600 transition line-clamp-2 leading-snug">
                                        {{ $r->title }}
                                    </h4>
                                    <span class="text-[10px] text-slate-400 mt-1 block">
                                        {{ $r->relative_time ?: 'हाल में' }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Android App Card Promo -->
                <div class="bg-gradient-to-br from-brand-900 to-slate-900 rounded-3xl p-6 text-white space-y-4 shadow-xl">
                    <div class="w-10 h-10 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-bold text-lg shadow-md">
                        <i class="fa-brands fa-android"></i>
                    </div>
                    <h3 class="text-lg font-black leading-tight">
                        CGJobs Android App डाउनलोड करें
                    </h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        नवीनतम भर्तियों और परीक्षा सूचनाओं के त्वरित पुश नोटिफिकेशन सीधे अपने मोबाइल पर प्राप्त करें।
                    </p>
                    <a href="https://play.google.com/store" target="_blank" rel="noopener" class="block text-center py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-900 font-extrabold text-xs shadow-md transition">
                        Google Play से पाएं
                    </a>
                </div>

            </aside>

        </div>
    </div>
</div>
@endsection
