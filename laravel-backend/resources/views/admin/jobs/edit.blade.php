@extends('layouts.admin')

@section('title', 'भर्ती संपादित करें (Edit Job)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">भर्ती अधिसूचना संपादित करें</h2>
            <p class="text-xs text-slate-500 mt-0.5">अधिसूचना विवरण अपडेट करें (#{{ $job->id }})</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> वापस जाएं
        </a>
    </div>

    <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Title & Category -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    भर्ती शीर्षक (Job Title) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $job->title) }}" required class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    विभाग / श्रेणी (Category) <span class="text-rose-500">*</span>
                </label>
                <select name="category" required class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ old('category', $job->category) == $cat->name ? 'selected' : '' }}>
                            {{ $cat->hindi_name ?: $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Vacancies, Eligibility, Age Limit -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">कुल रिक्तियां (Vacancies)</label>
                <input type="text" name="vacancies" value="{{ old('vacancies', $job->vacancies) }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">शैक्षणिक योग्यता (Eligibility)</label>
                <input type="text" name="eligibility" value="{{ old('eligibility', $job->eligibility) }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">आयु सीमा (Age Limit)</label>
                <input type="text" name="age_limit" value="{{ old('age_limit', $job->age_limit) }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Summary -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                संक्षिप्त विवरण (Summary) <span class="text-rose-500">*</span>
            </label>
            <textarea name="summary" rows="3" required class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('summary', $job->summary) }}</textarea>
        </div>

        <!-- Detailed Content -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">विस्तृत विवरण (Detailed Content)</label>
            <textarea name="detailed_content" rows="4" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('detailed_content', $job->detailed_content) }}</textarea>
        </div>

        <!-- Important Dates Section -->
        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-regular fa-calendar-days text-brand-600"></i> महत्वपूर्ण तिथियां (Important Dates)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">आवेदन शुरू</label>
                    <input type="text" name="application_start" value="{{ old('application_start', $job->application_start) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">अंतिम तिथि</label>
                    <input type="text" name="last_date" value="{{ old('last_date', $job->last_date) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">परीक्षा तिथि</label>
                    <input type="text" name="exam_date" value="{{ old('exam_date', $job->exam_date) }}" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">आधिकारिक विज्ञापन PDF लिंक</label>
                <input type="url" name="official_notification_url" value="{{ old('official_notification_url', $job->official_notification_url) }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">ऑनलाइन आवेदन लिंक</label>
                <input type="url" name="apply_url" value="{{ old('apply_url', $job->apply_url) }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Options -->
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-6 items-center">
            <label class="inline-flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_breaking" value="1" {{ old('is_breaking', $job->is_breaking) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                <span class="text-xs font-semibold text-slate-700">ब्रेकिंग / मुख्य भर्ती (HOT Badge)</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.jobs.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                रद्द करें
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-md transition">
                <i class="fa-solid fa-floppy-disk mr-2"></i> परिवर्तन सहेजें (Update Job)
            </button>
        </div>

    </form>

</div>
@endsection
