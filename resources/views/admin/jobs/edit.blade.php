@extends('layouts.admin')

@section('title', 'अधिसूचना संपादित करें (Edit Job/News)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">अधिसूचना संपादित करें</h2>
            <p class="text-xs text-slate-500 mt-0.5">ID: {{ $job->custom_id ?: $job->id }}</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}" class="px-3 py-2 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-xl hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> वापस जाएं
        </a>
    </div>

    <form method="POST" action="{{ route('admin.jobs.update', $job) }}" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        @csrf
        @method('PUT')

        <!-- Section & Post Type Selection -->
        <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    सेक्शन (Section) <span class="text-rose-500">*</span>
                </label>
                <select name="section" required class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="jobs" {{ old('section', $job->section ?: 'jobs') == 'jobs' ? 'selected' : '' }}>
                        💼 सरकारी भर्तियां (Jobs & Vacancies)
                    </option>
                    <option value="news" {{ old('section', $job->section) == 'news' ? 'selected' : '' }}>
                        📰 समसामयिकी व समाचार (Current Affairs / News)
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    पोस्ट का प्रकार (Post Type) <span class="text-rose-500">*</span>
                </label>
                <select name="post_type" class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl bg-white font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="job" {{ old('post_type', $job->post_type) == 'job' ? 'selected' : '' }}>नई भर्ती (Job Notification)</option>
                    <option value="news" {{ old('post_type', $job->post_type) == 'news' ? 'selected' : '' }}>दैनिक समाचार (News Article)</option>
                    <option value="admit_card" {{ old('post_type', $job->post_type) == 'admit_card' ? 'selected' : '' }}>प्रवेश पत्र (Admit Card)</option>
                    <option value="result" {{ old('post_type', $job->post_type) == 'result' ? 'selected' : '' }}>परीक्षा परिणाम (Result)</option>
                    <option value="syllabus" {{ old('post_type', $job->post_type) == 'syllabus' ? 'selected' : '' }}>पाठ्यक्रम (Syllabus & Pattern)</option>
                    <option value="answer_key" {{ old('post_type', $job->post_type) == 'answer_key' ? 'selected' : '' }}>उत्तर कुंजी (Answer Key)</option>
                </select>
            </div>
        </div>

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

        <!-- Vacancies, Salary, Eligibility, Age Limit -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">कुल रिक्तियां (Vacancies)</label>
                <input type="text" name="vacancies" value="{{ old('vacancies', $job->vacancies) }}" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">वेतनमान (Salary)</label>
                <input type="text" name="salary" value="{{ old('salary', $job->salary) }}" placeholder="उदा: ₹19,500 - ₹62,000/-" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
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
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    संक्षिप्त विवरण (Summary) <span class="text-rose-500">*</span>
                </label>
                <button type="button" onclick="autoFillWithGemini()" id="gemini_autofill_btn" class="text-[11px] font-bold text-purple-700 bg-purple-100 hover:bg-purple-200 px-2.5 py-1 rounded-lg transition flex items-center gap-1">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>✨ Gemini AI से दोबारा लिखें</span>
                </button>
            </div>
            <textarea name="summary" id="input_summary" rows="3" required class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('summary', $job->summary) }}</textarea>
        </div>

        <!-- Detailed Content -->
        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">विस्तृत विवरण (Detailed Content / Full Article)</label>
            <textarea name="detailed_content" id="input_detailed_content" rows="5" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">{{ old('detailed_content', $job->detailed_content) }}</textarea>
        </div>

        <!-- Exam Takeaway Box (Special for CGPSC & Vyapam Current Affairs) -->
        <div class="bg-amber-50/70 p-5 rounded-2xl border border-amber-200/80 space-y-2">
            <label class="block text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-graduation-cap text-amber-600"></i> परीक्षा उपयोगी तथ्य / मुख्य बिंदु (Exam Takeaway for CGPSC / CGSSB)
            </label>
            <textarea name="exam_takeaway" id="input_exam_takeaway" rows="2" placeholder="उदा: 1. छत्तीसगढ़ बजट 2026 में प्रावधान..." class="w-full px-3.5 py-2 text-sm border border-amber-300 rounded-xl bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">{{ old('exam_takeaway', $job->exam_takeaway) }}</textarea>
            <p class="text-[11px] text-amber-700">यह Inshorts कार्ड में 'परीक्षा उपयोगी तथ्य (Key Takeaway)' बॉक्स में छात्रों को सीधे दिखेगा।</p>
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

        <!-- Links & Source -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">मूल समाचार पोर्टल / स्रोत नाम (Source / Portal Name)</label>
                <input type="text" name="source" value="{{ old('source', $job->source) }}" placeholder="उदा: Dainik Bhaskar / Navbharat / CG Govt" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">मूल समाचार पोर्टल लिंक (Source Portal URL)</label>
                <input type="url" name="source_url" value="{{ old('source_url', $job->source_url) }}" placeholder="https://www.bhaskar.com/..." class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

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
                <span class="text-xs font-semibold text-slate-700">ब्रेकिंग / HOT Badge</span>
            </label>

            <label class="inline-flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_new" value="1" {{ old('is_new', $job->is_new) ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                <span class="text-xs font-semibold text-slate-700">New Badge</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 flex items-center justify-end space-x-3">
            <a href="{{ route('admin.jobs.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition">
                रद्द करें
            </a>
            <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm rounded-xl shadow-md transition transform active:scale-95">
                <i class="fa-solid fa-check mr-2"></i> बदलाव सहेजें (Update)
            </button>
        </div>

    </form>

</div>

<script>
function autoFillWithGemini() {
    const title = document.querySelector('input[name="title"]').value.trim();
    const content = document.getElementById('input_detailed_content').value.trim();
    const btn = document.getElementById('gemini_autofill_btn');

    if (!title && !content) {
        alert('कृपया पहले शीर्षक या विस्तृत समाचार पाठ दर्ज करें जिसे Gemini विश्लेषित कर सके।');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Gemini लिख रहा है...</span>';

    fetch("{{ route('admin.sync.gemini-preview') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            title: title || 'नवीन प्रतियोगी परीक्षा समाचार',
            content: content || title,
            source: 'एडमिन संपादकीय'
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> <span>✨ Gemini AI से दोबारा लिखें</span>';

        if (data.success && data.data) {
            const d = data.data;
            if (d.inshorts_summary) {
                document.getElementById('input_summary').value = d.inshorts_summary;
            }
            if (d.exam_takeaway) {
                document.getElementById('input_exam_takeaway').value = d.exam_takeaway;
            }
            if (d.cleaned_detailed_content && !content) {
                document.getElementById('input_detailed_content').value = d.cleaned_detailed_content;
            }
        } else {
            alert(data.message || 'Gemini से जवाब नहीं मिला। कृपया Gemini API कुंजी की जांच करें।');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> <span>✨ Gemini AI से दोबारा लिखें</span>';
        alert('त्रुटि: ' + err.message);
    });
}
</script>
@endsection
