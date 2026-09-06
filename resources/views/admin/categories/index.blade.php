@extends('layouts.admin')

@section('title', 'श्रेणियां (Categories)')

@section('content')
<div class="space-y-6">

    <div>
        <h2 class="text-xl font-bold text-slate-900">भर्ती श्रेणियां व विभाग</h2>
        <p class="text-xs text-slate-500 mt-0.5">Android ऐप के होम स्क्रीन पर दिखने वाली श्रेणियां (Filter Chips)</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Category List (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">सक्रिय श्रेणियां</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4 font-semibold">नाम (English)</th>
                            <th class="py-3 px-4 font-semibold">हिंदी नाम</th>
                            <th class="py-3 px-4 font-semibold">Slug (API ID)</th>
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
                                <td class="py-3 px-4 text-xs font-mono text-slate-400">
                                    {{ $cat->slug }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('admin.categories.destroy', $cat) }}" onsubmit="return confirm('श्रेणी हटाएं?');" class="inline">
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
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">नाम (English) *</label>
                    <input type="text" name="name" required placeholder="उदा: CG Forest" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">हिंदी नाम</label>
                    <input type="text" name="hindi_name" placeholder="उदा: वन विभाग भर्ती" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">कलर कोड</label>
                    <input type="text" name="color" value="#1565C0" placeholder="#1565C0" class="w-full px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none">
                </div>

                <button type="submit" class="w-full py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
                    + श्रेणी जोड़ें (Save Category)
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
