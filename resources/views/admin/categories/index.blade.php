@extends('layouts.admin')

@section('title', 'श्रेणियां (Categories)')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">श्रेणियां व विभाग (Categories Management)</h2>
            <p class="text-xs text-slate-500 mt-0.5">Android ऐप के Jobs, News और Static GK सेक्शन के फ़िल्टर चिप्स</p>
        </div>
        
        <!-- Section Filter Tabs -->
        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl text-xs font-semibold">
            <a href="{{ route('admin.categories.index') }}" class="px-3 py-1.5 rounded-lg transition {{ !request('section') ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                सभी (All)
            </a>
            <a href="{{ route('admin.categories.index', ['section' => 'jobs']) }}" class="px-3 py-1.5 rounded-lg transition {{ request('section') == 'jobs' ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Jobs
            </a>
            <a href="{{ route('admin.categories.index', ['section' => 'news']) }}" class="px-3 py-1.5 rounded-lg transition {{ request('section') == 'news' ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                News
            </a>
            <a href="{{ route('admin.categories.index', ['section' => 'static_gk']) }}" class="px-3 py-1.5 rounded-lg transition {{ request('section') == 'static_gk' ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Static GK
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Category List (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                    सक्रिय श्रेणियां ({{ $categories->count() }})
                </h3>
                @if(request('section'))
                    <span class="text-xs font-bold text-brand-600 uppercase">{{ request('section') }}</span>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4 font-semibold">नाम (English)</th>
                            <th class="py-3 px-4 font-semibold">हिंदी नाम</th>
                            <th class="py-3 px-4 font-semibold">सेक्शन (Section)</th>
                            <th class="py-3 px-4 font-semibold text-right">हटाएं</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-bold text-xs text-slate-800">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full mr-1.5" style="background-color: {{ $cat->color ?: '#1565C0' }}"></span>
                                    {{ $cat->name }}
                                </td>
                                <td class="py-3 px-4 text-xs text-slate-600">
                                    {{ $cat->hindi_name ?: '—' }}
                                </td>
                                <td class="py-3 px-4 text-xs">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $cat->section_id === 'news' ? 'bg-blue-50 text-blue-700' : ($cat->section_id === 'static_gk' ? 'bg-indigo-50 text-indigo-700' : 'bg-emerald-50 text-emerald-700') }}">
                                        {{ $cat->section_id ?: 'jobs' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('क्या आप इस श्रेणी को हटाना चाहते हैं?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1 rounded text-rose-500 hover:bg-rose-50">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-xs text-slate-400">कोई श्रेणी नहीं मिली।</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Add Category Form (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 space-y-4">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-folder-plus text-brand-600"></i> नई श्रेणी जोड़ें
            </h3>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">सेक्शन चुनें (Section) *</label>
                    <select name="section_id" required class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="jobs" {{ request('section') == 'jobs' ? 'selected' : '' }}>Jobs & Vacancies (सरकारी नौकरियां)</option>
                        <option value="news" {{ request('section') == 'news' ? 'selected' : '' }}>News & Current Affairs (समाचार व समसामयिकी)</option>
                        <option value="static_gk" {{ request('section') == 'static_gk' ? 'selected' : '' }}>Static GK & Study (सामान्य ज्ञान)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">नाम (English) *</label>
                    <input type="text" name="name" required placeholder="उदा: CG Forest / National Affairs" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">हिंदी नाम</label>
                    <input type="text" name="hindi_name" placeholder="उदा: वन विभाग भर्ती" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">कलर कोड</label>
                    <input type="text" name="color" value="#1565C0" placeholder="#1565C0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">प्रदर्शन क्रम (Display Order)</label>
                    <input type="number" name="display_order" value="0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none">
                </div>

                <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    + श्रेणी सहेजें (Save Category)
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
