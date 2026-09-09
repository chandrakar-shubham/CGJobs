@extends('layouts.admin')

@section('title', 'सभी सरकारी भर्तियां (All Jobs)')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Action Row -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                सभी सरकारी भर्तियां (Jobs Inventory)
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                समस्त भर्ती विज्ञप्तियों का प्रबंधन, संपादन, प्रकाशन व रियल-टाइम मोबाइल सिंक।
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('admin.jobs.scraper-console') }}" class="px-4 py-2.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold transition shadow-2xs flex items-center gap-1.5">
                <i class="fa-solid fa-cloud-arrow-down text-amber-600"></i>
                <span>भर्ती स्क्रैपर (Console)</span>
            </a>
            <a href="{{ route('admin.jobs.dashboard') }}" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center gap-1.5">
                <i class="fa-solid fa-chart-pie text-brand-600"></i>
                <span>Analytics</span>
            </a>
            <a href="{{ route('admin.jobs.create') }}" class="px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-black shadow-md shadow-brand-600/20 transition flex items-center gap-1.5">
                <i class="fa-solid fa-circle-plus"></i>
                <span>+ नई भर्ती जोड़ें</span>
            </a>
        </div>
    </div>

    <!-- Status Filter Pills -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
        @php
            $statusTabs = [
                '' => ['All Jobs', 'bg-slate-400', $jobs->total()],
                'published' => ['Published', 'bg-emerald-500', null],
                'draft' => ['Drafts', 'bg-amber-400', null],
                'scheduled' => ['Scheduled', 'bg-blue-500', null],
                'archived' => ['Archived', 'bg-slate-500', null],
            ];
        @endphp
        @foreach($statusTabs as $val => $tab)
            <a href="{{ route('admin.jobs.index', array_merge(request()->query(), ['section' => 'jobs', 'workflow_status' => $val])) }}" class="rounded-2xl border p-3.5 bg-white text-center hover:bg-slate-50 transition shadow-2xs {{ request('workflow_status', '') === $val ? 'ring-2 ring-brand-500 border-brand-500' : 'border-slate-200' }}">
                <div class="flex items-center justify-center gap-2 text-xs font-black text-slate-700">
                    <span class="h-2.5 w-2.5 rounded-full {{ $tab[1] }}"></span>
                    <span>{{ $tab[0] }}</span>
                    @if($tab[2] !== null)
                        <span class="text-slate-400 font-semibold">({{ $tab[2] }})</span>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    <!-- Filter & Search Console -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.jobs.index') }}" class="space-y-4">
            <input type="hidden" name="section" value="jobs">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3.5 text-xs">
                
                <div class="xl:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">खोजें (Search)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="शीर्षक, विभाग, पद या स्रोत खोजें..." class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">मुख्य श्रेणी (Category)</label>
                    <select name="job_category" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                        <option value="">सभी श्रेणियां</option>
                        @foreach($jobCategories as $c)
                            <option value="{{ $c }}" @selected(request('job_category') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">विभाग (Department)</label>
                    <select name="department" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                        <option value="">सभी विभाग</option>
                        @foreach($departments as $d)
                            <option value="{{ $d }}" @selected(request('department') === $d)>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">पोस्ट प्रकार (Post Type)</label>
                    <select name="post_type" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                        <option value="">सभी प्रकार</option>
                        @foreach($postTypes as $v => $label)
                            <option value="{{ $v }}" @selected(request('post_type') === $v)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">अंतिम तिथि (Deadline)</label>
                    <select name="deadline" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:outline-none bg-white">
                        <option value="">सभी तिथियां</option>
                        <option value="open" @selected(request('deadline') === 'open')>सक्रिय (>2 दिन)</option>
                        <option value="closing" @selected(request('deadline') === 'closing')>समाप्ति निकट (≤2 दिन)</option>
                        <option value="expired" @selected(request('deadline') === 'expired')>समाप्त (Expired)</option>
                    </select>
                </div>

            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <a href="{{ route('admin.jobs.index', ['section' => 'jobs']) }}" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition">
                    रीसेट (Reset)
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm">
                    फ़िल्टर लागू करें (Apply)
                </button>
            </div>
        </form>
    </div>

    <!-- Data Table Card with Bulk Action Controls -->
    <form method="POST" action="{{ route('admin.jobs.bulk-action') }}" id="bulkForm">
        @csrf

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            
            <!-- Bulk Action Bar -->
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3 font-semibold text-slate-700">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500 h-4 w-4">
                        <span>सभी चुनें</span>
                    </label>
                    <span class="text-slate-400">•</span>
                    <span class="text-slate-500">{{ $jobs->total() }} कुल भर्तियां</span>
                </div>

                <div class="flex items-center gap-2">
                    <select name="bulk_action" class="px-3 py-1.5 border border-slate-200 rounded-xl bg-white text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        <option value="">बल्क एक्शन चुनें...</option>
                        <option value="publish">प्रकाशित करें (Publish)</option>
                        <option value="draft">ड्राफ्ट में बदलें (Draft)</option>
                        <option value="archive">आर्काइव करें (Archive)</option>
                        <option value="hot">Mark HOT</option>
                        <option value="new">Mark NEW</option>
                        <option value="notify">पुश नोटिफिकेशन भेजें</option>
                        <option value="delete">हटाएं (Delete)</option>
                    </select>

                    <button type="submit" class="px-4 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition" onclick="return confirm('क्या आप चयनित भर्तियों पर यह कार्रवाई करना चाहते हैं?')">
                        लागू करें
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1000px]">
                    <thead class="bg-white border-b border-slate-100 text-slate-400 text-[10px] font-extrabold uppercase tracking-wider">
                        <tr>
                            <th class="p-4 w-10">✓</th>
                            <th class="p-4 w-24">थंबनेल</th>
                            <th class="p-4 min-w-[280px]">भर्ती शीर्षक व विवरण</th>
                            <th class="p-4">श्रेणी व विभाग</th>
                            <th class="p-4">स्थिति</th>
                            <th class="p-4">पद व दृश्य</th>
                            <th class="p-4">अंतिम तिथि</th>
                            <th class="p-4 text-right">कार्रवाई</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($jobs as $job)
                            @php
                                $image = $job->image_url ?: ($job->poster_url ?? null);
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition group align-top">
                                
                                <td class="p-4">
                                    <input type="checkbox" name="job_ids[]" value="{{ $job->id }}" class="jobCheck rounded border-slate-300 text-brand-600 focus:ring-brand-500 h-4 w-4">
                                </td>

                                <td class="p-4">
                                    <div class="w-20 h-14 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center shrink-0">
                                        @if($image)
                                            <img src="{{ $image }}" alt="{{ $job->title }}" loading="lazy" class="w-full h-full object-cover" onerror="this.style.display='none'">
                                        @else
                                            <i class="fa-solid fa-briefcase text-slate-300 text-base"></i>
                                        @endif
                                    </div>
                                </td>

                                <td class="p-4">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        @if($job->is_breaking)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-500 text-white animate-pulse">HOT</span>
                                        @endif
                                        @if($job->is_new)
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-brand-500 text-white">NEW</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('admin.jobs.edit', $job) }}" class="font-bold text-slate-900 group-hover:text-brand-600 transition line-clamp-2 text-xs sm:text-sm">
                                        {{ $job->title }}
                                    </a>
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        स्रोत: <span class="text-slate-600 font-medium">{{ $job->source ?: 'शासकीय पोर्टल' }}</span>
                                    </div>
                                </td>

                                <td class="p-4 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-lg bg-brand-50 text-brand-700 text-xs font-bold block w-max">
                                        {{ $job->job_category ?: 'CGSSB' }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-1 truncate max-w-[160px]">
                                        {{ $job->department ?: 'विभाग विवरण' }}
                                    </span>
                                </td>

                                <td class="p-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ ($job->workflow_status ?: 'published') === 'published' ? 'bg-emerald-100 text-emerald-800' : (($job->workflow_status === 'draft') ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                        {{ ucfirst($job->workflow_status ?: 'published') }}
                                    </span>
                                </td>

                                <td class="p-4 whitespace-nowrap text-xs">
                                    <div class="font-bold text-slate-800">
                                        {{ $job->vacancies ?: '—' }} <span class="font-normal text-slate-400 text-[11px]">पद</span>
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ number_format($job->views_count) }} व्यूज
                                    </div>
                                </td>

                                <td class="p-4 whitespace-nowrap text-xs">
                                    <div class="font-bold text-slate-800">
                                        {{ $job->last_date ?: '—' }}
                                    </div>
                                </td>

                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('job.show', $job->custom_id ?: $job->id) }}" target="_blank" rel="noopener" class="p-2 rounded-xl text-slate-500 hover:text-brand-600 hover:bg-slate-100 transition" title="Preview">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.jobs.edit', $job) }}" class="p-2 rounded-xl text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <button type="submit" form="delete-job-{{ $job->id }}" class="p-2 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete" onclick="return confirm('क्या आप इस भर्ती को हटाना चाहते हैं?')">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-xs text-slate-400">
                                    कोई भर्ती नहीं मिली।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($jobs->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $jobs->links() }}
                </div>
            @endif

        </div>

    </form>

    <!-- Hidden Individual Delete Forms -->
    @foreach($jobs as $job)
        <form id="delete-job-{{ $job->id }}" method="POST" action="{{ route('admin.jobs.destroy', $job) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

</div>

<script>
    const selectAll = document.getElementById('selectAll');
    selectAll?.addEventListener('change', () => {
        document.querySelectorAll('.jobCheck').forEach(cb => cb.checked = selectAll.checked);
    });
</script>
@endsection
