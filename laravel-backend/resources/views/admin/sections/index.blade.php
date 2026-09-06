@extends('layouts.admin')

@section('title', 'सेक्शन व खंड प्रबंधन')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
                <i class="fa-solid fa-layer-group text-brand-600"></i>
                <span>ऐप सेक्शंस व श्रेणी पदानुक्रम (App Sections Hierarchy)</span>
            </h2>
            <p class="text-sm text-slate-500 mt-1">Android ऐप के मुख्य 3 सेक्शन (Jobs, News, Static GK) और उनके तहत श्रेणियों का प्रबंधन करें</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-semibold transition">
                <i class="fa-solid fa-tags mr-1.5"></i> सभी श्रेणियां देखें
            </a>
        </div>
    </div>

    <!-- Sections Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($sections as $sec)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-5 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center font-bold text-lg">
                            <i class="fa-solid fa-{{ $sec->icon ?: 'folder' }}"></i>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $sec->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                            {{ $sec->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900">{{ $sec->hindi_name ?: $sec->name }}</h3>
                    <div class="text-xs font-mono text-slate-400 mb-2">Key: {{ $sec->section_key }}</div>
                    <p class="text-xs text-slate-500 mb-4">{{ $sec->description }}</p>

                    <div class="border-t border-slate-100 pt-3">
                        <div class="text-xs font-bold text-slate-700 mb-2 flex items-center justify-between">
                            <span>संबद्ध श्रेणियां (Categories):</span>
                            <span class="text-brand-600">{{ $sec->categories->count() }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto">
                            @forelse($sec->categories as $cat)
                                <span class="px-2 py-1 bg-slate-100 text-slate-700 text-[11px] rounded-lg font-medium">
                                    {{ $cat->hindi_name ?: $cat->name }}
                                </span>
                            @empty
                                <span class="text-slate-400 text-xs italic">कोई श्रेणी नहीं जुड़ी</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('admin.categories.index', ['section' => $sec->section_key]) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-800">
                        + श्रेणियां प्रबंधित करें &rarr;
                    </a>
                    <form action="{{ route('admin.sections.toggle', $sec) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-medium {{ $sec->is_active ? 'text-rose-600 hover:text-rose-700' : 'text-emerald-600 hover:text-emerald-700' }}">
                            {{ $sec->is_active ? 'अक्षम करें' : 'सक्रिय करें' }}
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Section Card -->
    <div class="bg-slate-50 rounded-2xl p-6 border border-dashed border-slate-300">
        <h3 class="text-sm font-bold text-slate-800 mb-3">नया कस्टम सेक्शन बनाएं (Create Custom Section)</h3>
        <form action="{{ route('admin.sections.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @csrf
            <div>
                <input type="text" name="section_key" placeholder="Key (e.g. syllabus)" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300">
            </div>
            <div>
                <input type="text" name="name" placeholder="अंग्रेजी नाम (e.g. Syllabus)" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300">
            </div>
            <div>
                <input type="text" name="hindi_name" placeholder="हिंदी नाम (उदा. पाठ्यक्रम)" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold transition">
                    + सेक्शन जोड़ें
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
