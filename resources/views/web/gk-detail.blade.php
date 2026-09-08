@extends('web.layout')

@section('title', ($item->hindi_title ?: $item->title) . ' — CGJobs Static GK')

@push('head')
<meta property="og:title" content="{{ $item->hindi_title ?: $item->title }}">
<meta property="og:description" content="{{ $item->answer ?: $item->detailed_notes }}">
<meta property="og:type" content="article">
<meta property="og:url" content="{{ url()->current() }}">
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $item->hindi_title ?: $item->title,
    'description' => $item->answer ?: $item->detailed_notes,
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
            <a href="{{ route('listing.gk') }}" class="hover:text-brand-600 transition">
                Static GK
            </a>
            <span>/</span>
            <span class="text-slate-400 truncate max-w-xs">{{ Str::limit($item->hindi_title ?: $item->title, 40) }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Main Content Area -->
            <article class="lg:col-span-8 bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/80 shadow-sm space-y-6">
                
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                        <i class="fa-solid fa-graduation-cap mr-1"></i> {{ $item->category ?: 'General Knowledge' }}
                    </span>
                    @if($item->year_exam_reference)
                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            📌 {{ $item->year_exam_reference }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight">
                    {{ $item->hindi_title ?: $item->title }}
                </h1>

                @if($item->question)
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block mb-1">प्रश्न (Question):</span>
                        <p class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug">
                            {{ $item->question }}
                        </p>
                    </div>
                @endif

                @if($item->answer)
                    <div class="p-6 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-emerald-950 space-y-2">
                        <span class="text-xs font-black uppercase tracking-wider text-emerald-800 flex items-center space-x-1.5">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>उत्तर (Correct Answer & Fact):</span>
                        </span>
                        <div class="text-base sm:text-lg font-bold leading-relaxed">
                            {{ $item->answer }}
                        </div>
                    </div>
                @endif

                @if(!empty($item->key_points))
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                            <i class="fa-solid fa-list-check text-purple-600"></i>
                            <span>मुख्य बिंदु (Key Exam Takeaways)</span>
                        </h2>
                        <ul class="space-y-2 text-sm text-slate-700">
                            @foreach($item->key_points as $point)
                                <li class="flex items-start space-x-2">
                                    <i class="fa-solid fa-check text-emerald-600 mt-1 text-xs flex-shrink-0"></i>
                                    <span>{{ is_string($point) ? $point : json_encode($point, JSON_UNESCAPED_UNICODE) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($item->detailed_notes)
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                            <i class="fa-solid fa-book-open-reader text-brand-600"></i>
                            <span>विस्तृत व्याख्या (Detailed Analysis)</span>
                        </h2>
                        <div class="text-sm leading-relaxed text-slate-700 whitespace-pre-line bg-slate-50 p-5 rounded-2xl border border-slate-200/70">
                            {{ $item->detailed_notes }}
                        </div>
                    </div>
                @endif

                <div class="pt-6 border-t border-slate-100 flex flex-wrap gap-3">
                    <a href="https://play.google.com/store" target="_blank" rel="noopener" class="px-5 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white font-extrabold text-xs shadow-md transition flex items-center space-x-2">
                        <i class="fa-brands fa-android"></i>
                        <span>ऐप में रिवीजन हेतु सहेजें</span>
                    </a>
                    <a href="{{ route('listing.gk') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        ← अन्य GK प्रश्न देखें
                    </a>
                </div>

            </article>

            <!-- Sidebar -->
            <aside class="lg:col-span-4 space-y-6">
                @if($related->count())
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                        <h3 class="font-extrabold text-sm text-slate-900 uppercase tracking-wider pb-3 border-b border-slate-100">
                            संबंधित सामान्य ज्ञान प्रश्न
                        </h3>
                        <div class="divide-y divide-slate-100">
                            @foreach($related as $r)
                                <a href="{{ route('gk.show', $r->custom_id ?: $r->id) }}" class="py-3 block group">
                                    <span class="text-[10px] font-bold text-purple-600 uppercase block mb-1">
                                        {{ $r->category ?: 'Static GK' }}
                                    </span>
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-purple-600 transition leading-snug">
                                        {{ $r->hindi_title ?: $r->title }}
                                    </h4>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-gradient-to-br from-purple-900 to-slate-900 rounded-3xl p-6 text-white space-y-4 shadow-xl">
                    <div class="w-10 h-10 rounded-2xl bg-purple-600 text-white flex items-center justify-center font-bold text-lg shadow-md">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h3 class="text-lg font-black leading-tight">
                        परीक्षा तैयारी को बनाएं आसान
                    </h3>
                    <p class="text-xs text-purple-200 leading-relaxed">
                        CGJobs ऐप में दैनिक क्विज, छत्तीसगढ़ इतिहास, भूगोल और पंचायती राज के विशेष नोट्स उपलब्ध हैं।
                    </p>
                    <a href="https://play.google.com/store" target="_blank" rel="noopener" class="block text-center py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-900 font-extrabold text-xs shadow-md transition">
                        CGJobs App डाउनलोड करें
                    </a>
                </div>
            </aside>

        </div>
    </div>
</div>
@endsection
