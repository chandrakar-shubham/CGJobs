@extends('layouts.admin')

@section('title', 'पुश नोटिफिकेशन भेजें (Broadcast Push Alert)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">नया अलर्ट व पुश नोटिफिकेशन भेजें</h2>
            <p class="text-xs text-slate-500 mt-0.5">Firebase Cloud Messaging (FCM) द्वारा सभी एक्टिव Android फोन्स पर तुरंत नोटिफिकेशन भेजें</p>
        </div>
        <a href="{{ route('admin.alerts.index') }}" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> वापस जाएं
        </a>
    </div>

    <form method="POST" action="{{ route('admin.alerts.store') }}" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf

        <!-- Title -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                अलर्ट शीर्षक (Notification Title) <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="उदा: व्यापम शिक्षक पात्रता परीक्षा TET 2026 एडमिट कार्ड जारी!" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
        </div>

        <!-- Category & Type -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    विभाग (Department / Category) <span class="text-rose-500">*</span>
                </label>
                <select name="category" required class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="CG Vyapam">CG Vyapam (व्यापम)</option>
                    <option value="CGPSC">CGPSC (लोक सेवा आयोग)</option>
                    <option value="CG Police">CG Police (पुलिस भर्ती)</option>
                    <option value="CG Education">CG Education (शिक्षक भर्ती)</option>
                    <option value="CG Health">CG Health (स्वास्थ्य विभाग)</option>
                    <option value="सूचना">सामान्य सूचना (General Alert)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    अलर्ट प्रकार (Alert Type) <span class="text-rose-500">*</span>
                </label>
                <select name="type" required class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="BREAKING">BREAKING (ताजा खबर)</option>
                    <option value="ADMIT_CARD">ADMIT_CARD (प्रवेश पत्र)</option>
                    <option value="RESULT">RESULT (परीक्षा परिणाम)</option>
                    <option value="EXAM_DATE">EXAM_DATE (परीक्षा तिथि)</option>
                    <option value="DEADLINE">DEADLINE (अंतिम तिथि चेतावनी)</option>
                    <option value="RECRUITMENT">RECRUITMENT (नई भर्ती)</option>
                </select>
            </div>
        </div>

        <!-- Description -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                संदेश विवरण (Notification Body / Message) <span class="text-rose-500">*</span>
            </label>
            <textarea name="short_description" rows="3" required placeholder="परीक्षार्थी अपने रोल नंबर से व्यापम पोर्टल पर प्रवेश पत्र डाउनलोड कर सकते हैं..." class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('short_description') }}</textarea>
        </div>

        <!-- Action URL & Linked Article -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    कार्रवाई लिंक (Direct URL on Tap)
                </label>
                <input type="url" name="action_url" value="{{ old('action_url') }}" placeholder="https://vyapam.cgstate.gov.in" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    संबंधित भर्ती (Link to Job - Optional)
                </label>
                <select name="article_id" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="">कोई नहीं (None)</option>
                    @foreach($recentJobs as $job)
                        <option value="{{ $job->id }}">{{ $job->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Push Broadcast Toggle -->
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-center space-x-3">
            <input type="checkbox" name="broadcast_now" id="broadcast_now" value="1" checked class="w-5 h-5 text-saffron-600 rounded border-amber-300 focus:ring-saffron-500">
            <label for="broadcast_now" class="cursor-pointer">
                <span class="block text-xs font-bold text-amber-900">
                    <i class="fa-solid fa-tower-broadcast mr-1 text-saffron-600"></i> तत्काल सभी मोबाइल यूज़र्स को पुश नोटिफिकेशन भेजें (FCM Broadcast)
                </span>
                <span class="block text-[11px] text-amber-700 mt-0.5">
                    Android ऐप के डिवाइस टोकन्स तथा topic <code>/topics/all_users</code> पर तुरंत नोटिफिकेशन प्रेषित किया जाएगा।
                </span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.alerts.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                रद्द करें
            </a>
            <button type="submit" class="px-6 py-2.5 bg-saffron-500 hover:bg-saffron-600 text-white font-bold text-sm rounded-xl shadow-md transition transform active:scale-95">
                <i class="fa-solid fa-paper-plane mr-2"></i> अलर्ट प्रसारित करें (Broadcast Alert)
            </button>
        </div>

    </form>

</div>
@endsection
