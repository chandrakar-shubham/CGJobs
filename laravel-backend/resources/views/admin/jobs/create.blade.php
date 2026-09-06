@extends('layouts.admin')

@section('title', 'नई पोस्ट जोड़ें (Add Post / Job)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">नई अधिसूचना प्रकाशित करें</h2>
            <p class="text-xs text-slate-500 mt-0.5">भर्ती या समसामयिकी समाचार जोड़ें, यह तुरंत Android ऐप में सिंक होगा</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> वापस जाएं
        </a>
    </div>

    <form method="POST" action="{{ route('admin.jobs.store') }}" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf

        <!-- Section & Post Type Selection -->
        <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    मुख्य सेक्शन चुनें (Section) <span class="text-rose-500">*</span>
                </label>
                <select name="section" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="jobs" {{ (old('section', $defaultSection ?? 'jobs') == 'jobs') ? 'selected' : '' }}>
                        💼 सरकारी भर्तियां (Jobs & Vacancies)
                    </option>
                    <option value="news" {{ (old('section', $defaultSection ?? '') == 'news') ? 'selected' : '' }}>
                        📰 समसामयिकी व समाचार (Current Affairs / News)
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    पोस्ट का प्रकार (Post Type) <span class="text-rose-500">*</span>
                </label>
                <select name="post_type" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="job">नई भर्ती (Job Notification)</option>
                    <option value="news">दैनिक समाचार (News Article)</option>
                    <option value="admit_card">प्रवेश पत्र (Admit Card)</option>
                    <option value="result">परीक्षा परिणाम (Result)</option>
                    <option value="syllabus">पाठ्यक्रम (Syllabus & Pattern)</option>
                    <option value="answer_key">उत्तर कुंजी (Answer Key)</option>
                </select>
            </div>
        </div>

        <!-- Title & Category -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    शीर्षक (Title) <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="उदा: CG Police Constable 2026 - 5,967 पदों पर भर्ती" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    विभाग / श्रेणी (Category) <span class="text-rose-500">*</span>
                </label>
                <select name="category" required class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ old('category') == $cat->name ? 'selected' : '' }}>
                            {{ $cat->hindi_name ?: $cat->name }} ({{ $cat->section_id ?: 'jobs' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Vacancies, Salary, Eligibility, Age Limit -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    कुल रिक्तियां (Vacancies)
                </label>
                <input type="text" name="vacancies" value="{{ old('vacancies') }}" placeholder="उदा: 242 पद" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    वेतनमान (Salary)
                </label>
                <input type="text" name="salary" value="{{ old('salary') }}" placeholder="उदा: ₹19,500 - ₹62,000/-" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    शैक्षणिक योग्यता (Eligibility)
                </label>
                <input type="text" name="eligibility" value="{{ old('eligibility') }}" placeholder="उदा: 10वीं/12वीं अथवा स्नातक" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    आयु सीमा (Age Limit)
                </label>
                <input type="text" name="age_limit" value="{{ old('age_limit') }}" placeholder="उदा: 18 से 35 वर्ष" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Summary -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                संक्षिप्त विवरण (Summary / Notification Intro) <span class="text-rose-500">*</span>
            </label>
            <textarea name="summary" rows="3" required placeholder="भर्ती या समाचार का मुख्य सार लिखें जो मोबाइल ऐप की पहली स्क्रीन पर दिखेगा..." class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('summary') }}</textarea>
        </div>

        <!-- Detailed Content -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                विस्तृत विवरण व चयन प्रक्रिया (Detailed Content / Selection Steps)
            </label>
            <textarea name="detailed_content" rows="4" placeholder="परीक्षा पैटर्न, शारीरिक दक्षता, मुख्य विषय या निर्देश..." class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('detailed_content') }}</textarea>
        </div>

        <!-- Important Dates Section -->
        <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-regular fa-calendar-days text-brand-600"></i> महत्वपूर्ण तिथियां (Important Dates)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">आवेदन शुरू (Start Date)</label>
                    <input type="text" name="application_start" value="{{ old('application_start', 'जारी है') }}" placeholder="उदा: 15 Sep 2026" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">अंतिम तिथि (Last Date)</label>
                    <input type="text" name="last_date" value="{{ old('last_date') }}" placeholder="उदा: 15 Oct 2026" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">परीक्षा तिथि (Exam Date)</label>
                    <input type="text" name="exam_date" value="{{ old('exam_date') }}" placeholder="उदा: Dec 2026" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg bg-white focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Links -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    आधिकारिक विज्ञापन PDF लिंक (Official Notification URL)
                </label>
                <input type="url" name="official_notification_url" value="{{ old('official_notification_url') }}" placeholder="https://vyapam.cgstate.gov.in/advt.pdf" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    ऑनलाइन आवेदन लिंक (Apply Online URL)
                </label>
                <input type="url" name="apply_url" value="{{ old('apply_url') }}" placeholder="https://vyapam.cgstate.gov.in/apply" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Options / Checkboxes -->
        <div class="pt-2 border-t border-slate-100 flex flex-wrap gap-6 items-center">
            <label class="inline-flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_breaking" value="1" {{ old('is_breaking') ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                <span class="text-xs font-semibold text-slate-700">ब्रेकिंग / मुख्य अलर्ट (HOT Badge)</span>
            </label>

            <label class="inline-flex items-center space-x-2 cursor-pointer bg-saffron-50 px-3 py-1.5 rounded-xl border border-saffron-200">
                <input type="checkbox" name="broadcast_push" value="1" checked class="w-4 h-4 text-saffron-600 rounded border-saffron-300 focus:ring-saffron-500">
                <span class="text-xs font-bold text-saffron-900">
                    <i class="fa-solid fa-paper-plane mr-1 text-saffron-600"></i> Android यूज़र्स को तुरंत पुश नोटिफिकेशन भेजें
                </span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.jobs.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                रद्द करें
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-md transition transform active:scale-95">
                <i class="fa-solid fa-check mr-2"></i> पोस्ट प्रकाशित करें (Publish Now)
            </button>
        </div>

    </form>

</div>
@endsection
