@extends('web.layout')

@section('title', $lang === 'en' ? 'Alerts & Notifications — CGJobs' : 'नवीनतम भर्ती एवं परीक्षा सूचनाएं — CGJobs')

@section('content')
<div class="bg-slate-50 py-12 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <div class="space-y-2">
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-md bg-amber-50 text-amber-800 text-xs font-bold border border-amber-200">
                <i class="fa-solid fa-bell text-[10px]"></i>
                <span>{{ $lang === 'en' ? 'Official Broadcasts' : 'आधिकारिक विज्ञप्तियां' }}</span>
            </span>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">
                {{ $lang === 'en' ? 'Latest Alerts & Broadcasts' : 'नवीनतम सूचनाएं एवं अलर्ट्स' }}
            </h1>
            <p class="text-sm text-slate-500">
                {{ $lang === 'en' ? 'Recruitment notifications, exam date changes, admit card releases, and key results.' : 'CGJobs पर प्रकाशित नई भर्ती, प्रवेश पत्र एवं परीक्षा परिणाम की ताज़ा सूचनाएं।' }}
            </p>
        </div>

        <div class="space-y-4">
            @forelse($alerts as $alert)
                <a href="{{ $alert->action_url ?: ($alert->article_id ? route('job.show', $alert->article_id) : route('listing.jobs')) }}" class="job-card-hover bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm block group">
                    <div class="flex items-center justify-between gap-3 text-xs mb-3">
                        <span class="px-2.5 py-1 rounded-lg bg-brand-50 text-brand-700 font-extrabold uppercase tracking-wider border border-brand-200">
                            {{ $alert->category ?: 'सूचना' }}
                        </span>
                        <span class="text-slate-400 font-medium text-[11px]">
                            <i class="fa-regular fa-clock mr-1"></i> {{ $alert->created_at?->diffForHumans() }}
                        </span>
                    </div>

                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900 group-hover:text-brand-600 transition leading-snug">
                        {{ $alert->title }}
                    </h3>

                    @if($alert->short_description)
                        <p class="text-xs sm:text-sm text-slate-600 mt-2 leading-relaxed">
                            {{ $alert->short_description }}
                        </p>
                    @endif

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-brand-600">
                        <span>{{ $lang === 'en' ? 'View Details' : 'पूरा विवरण देखें' }}</span>
                        <i class="fa-solid fa-arrow-right text-[10px] group-hover:translate-x-1 transition"></i>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-3xl p-12 text-center border border-slate-200/80 text-slate-400 space-y-2">
                    <i class="fa-regular fa-bell-slash text-3xl"></i>
                    <p class="text-sm font-medium">अभी कोई नई सूचना उपलब्ध नहीं है।</p>
                </div>
            @endforelse
        </div>

        @if($alerts->hasPages())
            <div class="pt-6 flex justify-center">
                {{ $alerts->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
