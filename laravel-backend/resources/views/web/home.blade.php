@extends('web.layout')

@section('title', $lang==='en' ? 'CGJobs — Government Jobs, Exams, Current Affairs & GK Portal' : 'CGJobs — छत्तीसगढ़ सरकारी नौकरी, परीक्षा, Current Affairs और Static GK')

@section('content')
@php
$categories = [
    ['name' => 'CGSSB', 'hindi' => 'व्यापम (CGSSB)', 'desc' => $lang==='en' ? 'Chhattisgarh Staff Selection Board (Vyapam)' : 'छत्तीसगढ़ व्यावसायिक परीक्षा मंडल व कर्मचारी चयन', 'icon' => 'fa-building-columns', 'color' => 'from-blue-600 to-cyan-500', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
    ['name' => 'CGPSC', 'hindi' => 'लोक सेवा आयोग (CGPSC)', 'desc' => $lang==='en' ? 'State Services, Assistant Professor, Engineering' : 'राज्य प्रशासनिक सेवा, सहायक प्राध्यापक एवं अन्य राजपत्रित पद', 'icon' => 'fa-award', 'color' => 'from-purple-600 to-indigo-500', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200'],
    ['name' => 'Central Govt', 'hindi' => 'केंद्र सरकार (SSC / Rly / Bank)', 'desc' => $lang==='en' ? 'SSC, Railways, Banking, Defense & UPSC' : 'कर्मचारी चयन आयोग, रेलवे भर्ती बोर्ड, बैंकिंग एवं रक्षा', 'icon' => 'fa-flag-checkered', 'color' => 'from-amber-500 to-orange-500', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
    ['name' => 'Contractual', 'hindi' => 'संविदा एवं अस्थायी भर्तियां', 'desc' => $lang==='en' ? 'NHM, Collectorate, Zila Panchayat & Walk-in' : 'राष्ट्रीय स्वास्थ्य मिशन, कलेक्टोरेट एवं वॉक-इन इंटरव्यू', 'icon' => 'fa-file-signature', 'color' => 'from-emerald-600 to-teal-500', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
];
$departments = ['Education', 'Police', 'Health', 'Forest', 'Revenue', 'PWD', 'Women & Child', 'Agriculture', 'Panchayat'];
$trending = $latest->take(6);
$hot = $jobs->filter(fn($j) => $j->is_breaking || $j->is_new)->take(5);
if ($hot->isEmpty()) $hot = $jobs->take(5);
@endphp

<!-- Hero Section with Gradient Mesh -->
<section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-brand-950 to-brand-900 text-white pt-12 pb-20 lg:pt-16 lg:pb-24">
    <!-- Subtle Background Glows -->
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left: Hero Headline & Search -->
            <div class="lg:col-span-7 space-y-6">
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-brand-200 text-xs font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>{{ $lang === 'en' ? 'Verified Government Recruitment Updates' : '100% सत्यापित शासकीय विज्ञप्तियां एवं परीक्षा सूचनाएं' }}</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight sm:leading-none">
                    {{ $lang === 'en' ? 'Your Government Career in Chhattisgarh Starts Here' : 'छत्तीसगढ़ में आपकी सरकारी नौकरी की शुरुआत यहीं से' }}
                </h1>

                <p class="text-slate-300 text-base sm:text-lg leading-relaxed max-w-2xl">
                    {{ $lang === 'en' ? 'One clean, fast portal for CGPSC, CGSSB (Vyapam), Police, Teacher, and Central vacancies — with instant syllabus, admit cards, and exam-focused current affairs.' : 'CGPSC, व्यापम (CGSSB), पुलिस, शिक्षक एवं केंद्रीय भर्ती की सटीक सूचना। सीधे ऑफिशियल नोटिफिकेशन, प्रवेश पत्र एवं दैनिक परीक्षा कैप्सूल।' }}
                </p>

                <!-- Search Box -->
                <form action="{{ route('listing.jobs', ['lang' => $lang]) }}" method="get" class="bg-white p-2 rounded-2xl shadow-2xl flex flex-col sm:flex-row items-center gap-2 max-w-2xl border border-white/20">
                    <div class="flex items-center px-4 py-2 w-full text-slate-800">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-lg mr-3"></i>
                        <input name="q" class="w-full bg-transparent border-0 outline-none text-sm sm:text-base font-semibold text-slate-900 placeholder-slate-400" placeholder="{{ $lang === 'en' ? 'Search jobs, post name, department (e.g. Police, Teacher)...' : 'पद, विभाग, योग्यता खोजें (उदा. पुलिस, शिक्षक, पटवारी)...' }}">
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-sm shadow-md transition flex items-center justify-center space-x-2 flex-shrink-0">
                        <span>{{ $lang === 'en' ? 'Search Jobs' : 'खोजें' }}</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <!-- Popular Search Chips -->
                <div class="flex flex-wrap items-center gap-2 pt-2 text-xs">
                    <span class="text-slate-400 font-semibold">{{ $lang === 'en' ? 'Popular:' : 'लोकप्रिय खोजें:' }}</span>
                    <a href="{{ route('listing.jobs', ['lang' => $lang, 'q' => 'Police']) }}" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-slate-200 border border-white/10 transition">पुलिस भर्ती</a>
                    <a href="{{ route('listing.jobs', ['lang' => $lang, 'job_category' => 'CGSSB']) }}" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-slate-200 border border-white/10 transition">CGSSB व्यापम</a>
                    <a href="{{ route('listing.jobs', ['lang' => $lang, 'job_category' => 'CGPSC']) }}" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-slate-200 border border-white/10 transition">CGPSC</a>
                    <a href="{{ route('listing.jobs', ['lang' => $lang, 'q' => 'Teacher']) }}" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-slate-200 border border-white/10 transition">शिक्षक भर्ती</a>
                    <a href="{{ route('listing.current-affairs', ['lang' => $lang]) }}" class="px-2.5 py-1 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/30 transition font-bold">
                        <i class="fa-solid fa-bolt text-[10px] mr-1"></i> दैनिक समसामयिकी
                    </a>
                </div>
            </div>

            <!-- Right: Live Today Highlights Box -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl p-6 shadow-2xl border border-slate-100 text-slate-900 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                                <i class="fa-solid fa-fire"></i>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-base text-slate-900 leading-none">{{ $lang === 'en' ? 'Important Today' : 'आज के महत्वपूर्ण अपडेट' }}</h3>
                                <span class="text-[11px] text-slate-500 font-medium">{{ $lang === 'en' ? 'Top recruitment notices' : 'ताज़ा जारी की गई विज्ञप्तियां' }}</span>
                            </div>
                        </div>
                        <a href="{{ route('listing.jobs', ['lang' => $lang]) }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">
                            {{ $lang === 'en' ? 'All' : 'सभी' }} →
                        </a>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($latest->take(5) as $item)
                            <a href="{{ route('job.show', $item->custom_id ?: $item->id, ['lang' => $lang]) }}" class="py-3 flex items-start space-x-3 group block hover:bg-slate-50/80 rounded-xl px-2 -mx-2 transition">
                                <span class="w-6 h-6 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center text-xs font-black flex-shrink-0 mt-0.5 group-hover:bg-brand-600 group-hover:text-white transition">
                                    {{ $loop->iteration }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-2 text-[10px] text-slate-500 font-semibold">
                                        <span class="text-brand-600 font-bold uppercase">{{ $item->job_category ?: ($item->category ?: 'CGJobs') }}</span>
                                        <span>•</span>
                                        <span>{{ $item->relative_time ?: ($item->published_at ?: 'आज') }}</span>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-2 leading-snug group-hover:text-brand-600 transition mt-0.5">
                                        {{ $item->title }}
                                    </h4>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-300 text-xs mt-2 group-hover:text-brand-600 group-hover:translate-x-0.5 transition"></i>
                            </a>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">
                                कोई सूचना उपलब्ध नहीं है।
                            </div>
                        @endforelse
                    </div>

                    <a href="{{ route('listing.jobs', ['lang' => $lang]) }}" class="block text-center py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-bold transition border border-slate-200/80">
                        {{ $lang === 'en' ? 'Explore All Job Notifications' : 'सभी भर्तियां देखें' }} →
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 4 Core Job Categories Section -->
<section class="py-14 bg-white border-b border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="text-xs font-extrabold uppercase tracking-wider text-brand-600 bg-brand-50 px-3 py-1 rounded-full border border-brand-200">
                {{ $lang === 'en' ? 'Choose Your Category' : 'अपनी श्रेणी चुनें' }}
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-3">
                {{ $lang === 'en' ? 'Four Main Job Portals' : 'चार प्रमुख भर्ती श्रेणियां' }}
            </h2>
            <p class="text-slate-500 text-sm mt-2">
                {{ $lang === 'en' ? 'Quickly access recruitment listings grouped strictly by commission and recruiting agency.' : 'भर्ती संस्था के अनुसार वर्गीकृत सटीक सूचना एवं ऑनलाइन आवेदन लिंक।' }}
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($categories as $cat)
                <a href="{{ route('listing.jobs', ['lang' => $lang, 'job_category' => $cat['name']]) }}" class="job-card-hover bg-slate-50 rounded-3xl p-6 border border-slate-200/80 flex flex-col justify-between group">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr {{ $cat['color'] }} text-white flex items-center justify-center text-xl shadow-md mb-4 group-hover:scale-110 transition duration-200">
                            <i class="fa-solid {{ $cat['icon'] }}"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 group-hover:text-brand-600 transition">
                            {{ $cat['name'] }}
                        </h3>
                        <span class="text-xs font-bold text-slate-500 block mt-0.5">{{ $cat['hindi'] }}</span>
                        <p class="text-xs text-slate-500 leading-relaxed mt-2.5">
                            {{ $cat['desc'] }}
                        </p>
                    </div>

                    <div class="pt-6 flex items-center justify-between text-xs font-extrabold text-brand-600 group-hover:translate-x-1 transition duration-200">
                        <span>{{ $lang === 'en' ? 'View Jobs' : 'भर्तियां देखें' }}</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Department Filter Strip -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold text-slate-500 mr-2"><i class="fa-solid fa-filter text-[10px]"></i> प्रमुख विभाग:</span>
            @foreach($departments as $dept)
                <a href="{{ route('listing.jobs', ['lang' => $lang, 'department' => $dept]) }}" class="px-3 py-1 rounded-xl bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 text-xs font-semibold transition border border-slate-200/60">
                    {{ $dept }}
                </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Trending & Featured Jobs Grid -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
            <div>
                <div class="inline-flex items-center space-x-1.5 text-xs font-bold text-rose-600 bg-rose-50 px-2.5 py-0.5 rounded-md border border-rose-200">
                    <i class="fa-solid fa-fire text-[10px]"></i>
                    <span>{{ $lang === 'en' ? 'TRENDING RECRUITMENTS' : 'सर्वाधिक लोकप्रिय भर्तियां' }}</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-2">
                    {{ $lang === 'en' ? 'Latest Open Vacancies' : 'हाल ही में जारी सरकारी विज्ञप्तियां' }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">
                    {{ $lang === 'en' ? 'Verified openings currently accepting applications.' : 'आवेदन हेतु सक्रिय पद, रिक्तियां व अंतिम तिथि।' }}
                </p>
            </div>

            <a href="{{ route('listing.jobs', ['lang' => $lang]) }}" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-800 text-xs font-extrabold shadow-sm transition">
                <span>{{ $lang === 'en' ? 'View All Jobs' : 'सभी नौकरियां देखें' }}</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        </div>

        <!-- Job Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($trending as $item)
                <article class="job-card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div class="space-y-4">
                        
                        <!-- Badges & Time -->
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center space-x-1.5">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $item->job_category ?: 'CGJobs' }}
                                </span>
                                @if($item->is_breaking)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-rose-500 text-white animate-pulse">
                                        HOT
                                    </span>
                                @endif
                                @if($item->is_new)
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-emerald-500 text-white">
                                        NEW
                                    </span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-400 font-medium">
                                <i class="fa-regular fa-clock mr-1 text-[10px]"></i> {{ $item->relative_time ?: ($item->published_at ?: 'हाल में') }}
                            </span>
                        </div>

                        <!-- Job Title -->
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 leading-snug line-clamp-2 hover:text-brand-600 transition">
                            <a href="{{ route('job.show', $item->custom_id ?: $item->id, ['lang' => $lang]) }}">
                                {{ $item->title }}
                            </a>
                        </h3>

                        <!-- Department & Source -->
                        <div class="text-xs text-slate-500 font-medium flex items-center space-x-1">
                            <i class="fa-regular fa-building text-slate-400 text-xs"></i>
                            <span class="truncate">{{ $item->department ?: ($item->category ?: 'शासकीय विभाग') }}</span>
                        </div>

                        <!-- Quick Highlights Grid -->
                        <div class="grid grid-cols-2 gap-2 pt-1 text-xs">
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-[10px] text-slate-400 font-bold block uppercase">{{ $lang === 'en' ? 'Vacancies' : 'कुल पद' }}</span>
                                <span class="font-black text-slate-800 mt-0.5 block truncate">
                                    <i class="fa-solid fa-users text-brand-600 mr-1 text-[10px]"></i> {{ $item->vacancies ?: 'विज्ञप्ति अनुसार' }}
                                </span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-rose-50/70 border border-rose-100">
                                <span class="text-[10px] text-rose-500 font-bold block uppercase">{{ $lang === 'en' ? 'Last Date' : 'अंतिम तिथि' }}</span>
                                <span class="font-black text-rose-700 mt-0.5 block truncate">
                                    <i class="fa-regular fa-calendar-xmark text-rose-500 mr-1 text-[10px]"></i> {{ $item->last_date ?: 'शीघ्र' }}
                                </span>
                            </div>
                        </div>

                    </div>

                    <!-- Bottom Action Button -->
                    <div class="pt-5 mt-5 border-t border-slate-100 flex items-center justify-between gap-3">
                        <a href="{{ route('job.show', $item->custom_id ?: $item->id, ['lang' => $lang]) }}" class="w-full py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-extrabold text-xs text-center shadow-md shadow-brand-600/20 transition flex items-center justify-center space-x-2">
                            <span>{{ $lang === 'en' ? 'View Details & Apply' : 'पूरा विवरण व आवेदन' }}</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-3 text-center py-12 text-slate-400 text-sm">
                    कोई रिक्तियां उपलब्ध नहीं हैं।
                </div>
            @endforelse
        </div>

    </div>
</section>

<!-- Current Affairs & Exam Prep Section -->
<section class="py-16 bg-white border-y border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-purple-600 bg-purple-50 px-3 py-1 rounded-full border border-purple-200">
                    <i class="fa-solid fa-bolt mr-1"></i> {{ $lang === 'en' ? 'Exam Oriented Current Affairs' : 'दैनिक समसामयिकी एवं परीक्षा दृष्टि' }}
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-3">
                    {{ $lang === 'en' ? 'Today\'s Current Affairs Capsules' : 'आज के प्रमुख करेंट अफेयर्स' }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">
                    {{ $lang === 'en' ? '60-word inshorts capsules designed for CGPSC, Vyapam, and State competitive exams.' : 'CGPSC एवं व्यापम भर्ती परीक्षाओं हेतु सटीक एवं परीक्षा उपयोगी बिंदु।' }}
                </p>
            </div>

            <a href="{{ route('listing.current-affairs', ['lang' => $lang]) }}" class="inline-flex items-center space-x-2 text-brand-600 hover:text-brand-700 font-extrabold text-xs">
                <span>{{ $lang === 'en' ? 'Explore All News' : 'सभी समसामयिकी देखें' }}</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($currentAffairs->take(6) as $art)
                <a href="{{ route('current-affairs.show', $art->custom_id ?: $art->id, ['lang' => $lang]) }}" class="job-card-hover bg-slate-50 rounded-3xl p-6 border border-slate-200/80 flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="px-2.5 py-1 rounded-lg bg-white text-purple-700 font-bold border border-slate-200/60 shadow-2xs">
                                {{ $art->category ?: 'समसामयिकी' }}
                            </span>
                            <span class="text-slate-400 font-medium text-[11px]">
                                {{ $art->relative_time ?: ($art->published_at ?: 'आज') }}
                            </span>
                        </div>

                        <h3 class="text-base font-extrabold text-slate-900 group-hover:text-brand-600 transition line-clamp-2 leading-snug">
                            {{ $art->title }}
                        </h3>

                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">
                            {{ $art->summary ?: 'परीक्षा उपयोगी संक्षेप पढ़ने के लिए क्लिक करें।' }}
                        </p>
                    </div>

                    @if(!empty($art->exam_takeaway))
                        <div class="mt-4 p-3 rounded-2xl bg-purple-50/70 border border-purple-100 text-[11px] text-purple-900">
                            <span class="font-extrabold block text-purple-700 text-[10px] uppercase tracking-wider mb-0.5">
                                📌 परीक्षा दृष्टि:
                            </span>
                            <p class="line-clamp-2 leading-relaxed">
                                {{ Str::limit(strip_tags($art->exam_takeaway), 110) }}
                            </p>
                        </div>
                    @endif

                    <div class="pt-4 mt-2 flex items-center justify-between text-xs font-extrabold text-brand-600">
                        <span>{{ $lang === 'en' ? 'Read 60-word capsule' : 'कैप्सूल पढ़ें' }}</span>
                        <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition"></i>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center py-10 text-slate-400 text-xs">
                    कोई समसामयिकी लेख उपलब्ध नहीं है।
                </div>
            @endforelse
        </div>

    </div>
</section>

<!-- Static GK Fundamentals Section -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                    <i class="fa-solid fa-book-open mr-1"></i> Static General Knowledge
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-3">
                    {{ $lang === 'en' ? 'Chhattisgarh Static GK & Exam Notes' : 'छत्तीसगढ़ सामान्य ज्ञान एवं तथ्य' }}
                </h2>
                <p class="text-slate-500 text-sm mt-1">
                    {{ $lang === 'en' ? 'High-yield revision notes for State Services, Vyapam and Sub-Inspector exams.' : 'छत्तीसगढ़ इतिहास, भूगोल, जनजाति, प्रशासन एवं कला-संस्कृति के परीक्षा उपयोगी तथ्य।' }}
                </p>
            </div>

            <a href="{{ route('listing.gk', ['lang' => $lang]) }}" class="inline-flex items-center space-x-2 text-emerald-700 hover:text-emerald-800 font-extrabold text-xs">
                <span>{{ $lang === 'en' ? 'Explore Full GK Archive' : 'सम्पूर्ण GK आर्काइव देखें' }}</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($gk->take(6) as $gItem)
                <a href="{{ route('gk.show', $gItem->custom_id ?: $gItem->id, ['lang' => $lang]) }}" class="job-card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between group">
                    <div class="space-y-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-base">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">
                            {{ $gItem->category ?: 'सामान्य ज्ञान' }}
                        </span>
                        <h3 class="text-base font-extrabold text-slate-900 group-hover:text-emerald-600 transition leading-snug">
                            {{ $gItem->title }}
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">
                            {{ Str::limit(strip_tags($gItem->answer ?: ($gItem->detailed_notes ?: '')), 120) }}
                        </p>
                    </div>

                    <div class="pt-4 mt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-emerald-600">
                        <span>{{ $lang === 'en' ? 'Read full note' : 'पूरा विवरण पढ़ें' }}</span>
                        <i class="fa-solid fa-arrow-right text-xs group-hover:translate-x-1 transition"></i>
                    </div>
                </a>
            @empty
                <div class="col-span-3 text-center py-10 text-slate-400 text-xs">
                    GK सामग्री जल्द उपलब्ध कराई जाएगी।
                </div>
            @endforelse
        </div>

    </div>
</section>

<!-- Android App CTA Banner -->
<section class="py-16 bg-gradient-to-tr from-brand-900 via-brand-800 to-slate-900 text-white relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="bg-white/10 backdrop-blur-md rounded-3xl p-8 sm:p-12 border border-white/15 flex flex-col lg:flex-row items-center justify-between gap-8 shadow-2xl">
            <div class="space-y-4 max-w-xl text-center lg:text-left">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                    <i class="fa-brands fa-android"></i>
                    <span>{{ $lang === 'en' ? 'Official Android Application' : 'ऑफिशियल एंड्रॉइड ऐप' }}</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight leading-tight">
                    {{ $lang === 'en' ? 'Never Miss an Important Exam or Job Alert' : 'परीक्षा या भर्ती का कोई भी महत्वपूर्ण अलर्ट न छूटे' }}
                </h2>
                <p class="text-slate-300 text-sm leading-relaxed">
                    {{ $lang === 'en' ? 'Download the CGJobs mobile app for instant push notifications, offline current affairs reading, and one-tap application access.' : 'तत्काल पुश नोटिफिकेशन, ऑफ़लाइन समसामयिकी अध्ययन एवं एक क्लिक में ऑनलाइन आवेदन की सुविधा अपने मोबाइल पर पाएं।' }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-4 flex-shrink-0">
                <a href="https://play.google.com/store" target="_blank" rel="noopener" class="px-6 py-3.5 rounded-2xl bg-white hover:bg-slate-100 text-slate-900 font-extrabold text-sm shadow-xl transition flex items-center space-x-3">
                    <i class="fa-brands fa-google-play text-xl text-emerald-600"></i>
                    <div class="text-left">
                        <span class="text-[10px] text-slate-500 font-medium block leading-none">GET IT ON</span>
                        <span class="font-black text-sm">Google Play</span>
                    </div>
                </a>
                <a href="{{ route('notifications', ['lang' => $lang]) }}" class="px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-extrabold text-sm border border-white/20 transition flex items-center space-x-2">
                    <i class="fa-solid fa-bell"></i>
                    <span>{{ $lang === 'en' ? 'Web Alerts' : 'वेब सूचनाएं' }}</span>
                </a>
            </div>
        </div>
    </div>
</section>

@endsection
