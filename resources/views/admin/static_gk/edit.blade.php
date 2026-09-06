@extends('layouts.admin')

@section('title', 'Static GK कार्ड संपादित करें')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Static GK कार्ड संपादित करें</h2>
            <p class="text-xs text-slate-500 mt-0.5">ID: {{ $staticGk->custom_id ?: $staticGk->id }}</p>
        </div>
        <a href="{{ route('admin.static-gk.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-800">
            &larr; वापस सूची पर जाएं
        </a>
    </div>

    <form action="{{ route('admin.static-gk.update', $staticGk) }}" method="POST" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">कार्ड शीर्षक (Title) *</label>
                <input type="text" name="title" required value="{{ old('title', $staticGk->title) }}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">हिंदी शीर्षक</label>
                <input type="text" name="hindi_title" value="{{ old('hindi_title', $staticGk->hindi_title) }}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">GK श्रेणी (Category) *</label>
                <input type="text" list="category_presets" name="category" required value="{{ old('category', $staticGk->category) }}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <datalist id="category_presets">
                    <option value="छत्तीसगढ़ का इतिहास">
                    <option value="छत्तीसगढ़ का भूगोल व नदियां">
                    <option value="जनजातियां, तीज-त्योहार व लोक संस्कृति">
                    <option value="अर्थव्यवस्था, कृषि व खनिज संसाधन">
                    <option value="प्रशासनिक ढांचा व पंचायती राज">
                </datalist>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">श्रेणी उपनाम</label>
                <input type="text" name="category_hindi" value="{{ old('category_hindi', $staticGk->category_hindi) }}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">परीक्षा संदर्भ / वर्ष</label>
                <input type="text" name="year_exam_reference" value="{{ old('year_exam_reference', $staticGk->year_exam_reference) }}" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">वस्तुनिष्ठ प्रश्न</label>
                <textarea name="question" rows="2" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('question', $staticGk->question) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">सही उत्तर</label>
                <textarea name="answer" rows="2" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('answer', $staticGk->answer) }}</textarea>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">मुख्य बिंदु (Key Bullet Points - प्रति पंक्ति एक बिंदु)</label>
            <textarea name="key_points_raw" rows="4" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('key_points_raw', is_array($staticGk->key_points) ? implode("\n", $staticGk->key_points) : '') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">विस्तृत अध्ययन नोट्स</label>
            <textarea name="detailed_notes" rows="4" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('detailed_notes', $staticGk->detailed_notes) }}</textarea>
        </div>

        <div class="flex items-center gap-6 pt-2 border-t border-slate-100">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_verified" value="1" {{ old('is_verified', $staticGk->is_verified) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                <span class="text-sm font-medium text-slate-700">सत्यापित व प्रामाणिक जानकारी (Mark as Verified)</span>
            </label>

            <div class="flex items-center gap-2">
                <label class="text-xs text-slate-500">प्रदर्शन क्रम:</label>
                <input type="number" name="display_order" value="{{ old('display_order', $staticGk->display_order) }}" class="w-20 px-2 py-1 text-sm rounded-lg border border-slate-300">
            </div>
        </div>

        <div class="pt-4 flex justify-end gap-3">
            <a href="{{ route('admin.static-gk.index') }}" class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800">
                रद्द करें
            </a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="fa-solid fa-floppy-disk mr-1.5"></i> बदलाव सहेजें (Update)
            </button>
        </div>
    </form>
</div>
@endsection
