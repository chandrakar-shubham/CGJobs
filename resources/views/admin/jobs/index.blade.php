@extends('layouts.admin')

@section('title', request('section') == 'news' ? 'समसामयिकी व समाचार' : 'भर्तियां व रोजगार')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">
                {{ request('section') == 'news' ? 'दैनिक समसामयिकी व समाचार (News)' : 'भर्ती व रोजगार अधिसूचनाएं (Jobs)' }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                {{ request('section') == 'news' ? 'छत्तीसगढ़ व राष्ट्रीय करंट अफेयर्स का प्रबंधन करें' : 'छत्तीसगढ़ के सभी विभागों की रिक्तियों को देखें, संपादित करें या हटाएं' }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Section Filter Tabs -->
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold">
                <a href="{{ route('admin.jobs.index') }}" class="px-3 py-1.5 rounded-lg transition {{ !request('section') ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    सभी पोस्ट
                </a>
                <a href="{{ route('admin.jobs.index', ['section' => 'jobs']) }}" class="px-3 py-1.5 rounded-lg transition {{ request('section') == 'jobs' ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    💼 Jobs
                </a>
                <a href="{{ route('admin.jobs.index', ['section' => 'news']) }}" class="px-3 py-1.5 rounded-lg transition {{ request('section') == 'news' ? 'bg-white text-brand-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    📰 News
                </a>
            </div>

            <a href="{{ route('admin.jobs.create', ['section' => request('section', 'jobs')]) }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow-sm transition">
                <i class="fa-solid fa-plus mr-1.5"></i> नई पोस्ट जोड़ें
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <form method="GET" action="{{ route('admin.jobs.index') }}" class="flex-1 flex flex-wrap gap-3 items-center w-full">
            @if(request('section'))
                <input type="hidden" name="section" value="{{ request('section') }}">
            @endif

            <div class="relative flex-1 min-w-[220px]">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="शीर्षक, पद या विवरण खोजें..." class="w-full pl-9 pr-3 py-2 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>

            <div class="w-44">
                <select name="category" onchange="this.form.submit()" class="w-full py-2 px-3 text-xs border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    <option value="All">सभी श्रेणियां (All)</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->name }}" {{ request('category') == $cat->name ? 'selected' : '' }}>
                            {{ $cat->hindi_name ?: $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl transition">
                फिल्टर
            </button>
            @if(request('search') || (request('category') && request('category') !== 'All'))
                <a href="{{ route('admin.jobs.index', ['section' => request('section')]) }}" class="px-3 py-2 text-xs text-rose-600 hover:underline">
                    रीसेट
                </a>
            @endif
        </form>
    </div>

    <!-- Jobs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 font-semibold">ID</th>
                        <th class="py-3 px-4 font-semibold">शीर्षक व विवरण</th>
                        <th class="py-3 px-4 font-semibold">सेक्शन व श्रेणी</th>
                        <th class="py-3 px-4 font-semibold">पद / वेतन</th>
                        <th class="py-3 px-4 font-semibold">अंतिम तिथि</th>
                        <th class="py-3 px-4 font-semibold text-right">कार्रवाई</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($jobs as $job)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-xs font-mono text-slate-400">
                                #{{ $job->id }}
                            </td>
                            <td class="py-3.5 px-4 max-w-md">
                                <div class="flex items-center gap-1.5">
                                    @if($job->post_type && $job->post_type !== 'job')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">
                                            {{ $job->post_type }}
                                        </span>
                                    @endif
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1">{{ $job->title }}</div>
                                </div>
                                <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $job->summary }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($job->is_breaking)
                                        <span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 text-[10px] font-bold rounded">HOT</span>
                                    @endif
                                    @if($job->official_notification_url)
                                        <a href="{{ $job->official_notification_url }}" target="_blank" class="text-[11px] text-blue-600 hover:underline">
                                            <i class="fa-regular fa-file-pdf mr-0.5"></i> PDF
                                        </a>
                                    @endif
                                    @if($job->apply_url)
                                        <a href="{{ $job->apply_url }}" target="_blank" class="text-[11px] text-emerald-600 hover:underline">
                                            <i class="fa-solid fa-arrow-up-right-from-square mr-0.5"></i> Apply
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="text-xs font-semibold text-slate-800">{{ $job->category }}</div>
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.2 rounded {{ $job->section === 'news' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $job->section ?: 'jobs' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs font-semibold text-slate-700">
                                <div>{{ $job->vacancies ?: '—' }}</div>
                                @if($job->salary)
                                    <div class="text-[11px] text-slate-400 font-normal">{{ $job->salary }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-600">
                                {{ $job->last_date ?: ($job->published_at ?: 'हाल ही में') }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-right text-xs">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('admin.jobs.edit', $job) }}" class="p-1.5 rounded-lg text-brand-600 hover:bg-brand-50 transition" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('क्या आप इस अधिसूचना को हटाना चाहते हैं?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition" title="Delete">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                कोई अधिसूचना नहीं मिली। <a href="{{ route('admin.jobs.create') }}" class="text-brand-600 underline font-bold">नई पोस्ट जोड़ें</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
