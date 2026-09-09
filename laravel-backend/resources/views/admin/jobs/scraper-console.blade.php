@extends('layouts.admin')

@section('title', 'स्मार्ट भर्ती स्क्रैपर व एआई कंसोल (Job Scraper & AI Console)')

@section('content')
<div class="space-y-6">

    <!-- Top Header & Status Row -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center shadow-md shadow-amber-500/20">
                    <i class="fa-solid fa-bolt text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>भर्ती स्क्रैपर व एआई कंट्रोल कंसोल</span>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-200">Dual-Mode</span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">
                        CGPSC, CGSSB (व्यापम), संविदा (E-Rojgar), Central Govt व Jobskind से स्मार्ट स्क्रैपिंग, जेमिनी 1-क्लिक बैच नॉर्मलाइजेशन व ड्राफ्टिंग।
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Gemini Status Pill -->
            <div class="px-3 py-1.5 rounded-xl border text-xs font-bold flex items-center gap-2 {{ $isGeminiReady ? 'bg-emerald-50 border-emerald-200 text-emerald-700' : 'bg-rose-50 border-rose-200 text-rose-700' }}">
                <span class="w-2 h-2 rounded-full {{ $isGeminiReady ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                <span>Gemini 3.8 Flash: {{ $isGeminiReady ? 'Active & Ready' : 'API Key Missing' }}</span>
            </div>

            <!-- Go to Main Jobs Dashboard -->
            <a href="{{ route('admin.jobs.dashboard') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center gap-2 shadow-2xs">
                <i class="fa-solid fa-table-columns text-slate-300"></i>
                <span>Jobs Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Mode Selector & Scraper Filters (Panel) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-black text-slate-800 flex items-center gap-2 uppercase tracking-wide">
                    <i class="fa-solid fa-sliders text-brand-600"></i>
                    <span>Mode 2: Interactive Smart Scraper (मल्टी-सोर्स व कस्टम फिल्टर)</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    conducting agency, post type, जिला या डेट रेंज चुनकर तुरंत आधिकारिक पोर्टल्स से लाइव विज्ञप्तियां फेच करें।
                </p>
            </div>

            <!-- Automation badge for Mode 1 -->
            <div class="text-[11px] font-semibold bg-indigo-50 border border-indigo-200 text-indigo-700 px-3 py-1.5 rounded-xl flex items-center gap-2">
                <i class="fa-solid fa-clock text-indigo-500"></i>
                <span>Mode 1 (Fully Auto Cron): <code>php artisan jobs:scrape-batch --auto-gemini</code></span>
            </div>
        </div>

        <form action="{{ route('admin.jobs.scraper-console.scrape') }}" method="POST" class="p-5 space-y-5">
            @csrf

            <!-- 1. Conducting Agency (Category) Multi-Select -->
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                    1. Conducting Agency (भर्ती परीक्षा प्राधिकरण / Category):
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <!-- CGPSC -->
                    <label class="relative flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition shadow-2xs">
                        <input type="checkbox" name="sources[]" value="cgpsc" checked class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div class="ml-3">
                            <span class="text-xs font-black text-slate-900 block">CGPSC</span>
                            <span class="text-[10px] text-slate-500 block leading-tight">psc.cg.gov.in</span>
                        </div>
                    </label>

                    <!-- CGSSB (Vyapam) -->
                    <label class="relative flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition shadow-2xs">
                        <input type="checkbox" name="sources[]" value="vyapam" checked class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div class="ml-3">
                            <span class="text-xs font-black text-slate-900 block">CGSSB (Vyapam)</span>
                            <span class="text-[10px] text-slate-500 block leading-tight">व्यापम भर्ती व परीक्षाएं</span>
                        </div>
                    </label>

                    <!-- CONTRACTUAL (E-Rojgar) -->
                    <label class="relative flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition shadow-2xs">
                        <input type="checkbox" name="sources[]" value="erojgar" checked class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div class="ml-3">
                            <span class="text-xs font-black text-slate-900 block">CONTRACTUAL (संविदा)</span>
                            <span class="text-[10px] text-slate-500 block leading-tight">erojgar.cg.gov.in (33 जिले)</span>
                        </div>
                    </label>

                    <!-- CENTRAL GOVT -->
                    <label class="relative flex items-center p-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition shadow-2xs">
                        <input type="checkbox" name="sources[]" value="central_govt" checked class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                        <div class="ml-3">
                            <span class="text-xs font-black text-slate-900 block">CENTRAL GOVT</span>
                            <span class="text-[10px] text-slate-500 block leading-tight">SSC, Railway, Defence</span>
                        </div>
                    </label>

                    <!-- Jobskind (Secondary Fallback) -->
                    <label class="relative flex items-center p-3 rounded-xl border border-amber-200 bg-amber-50/40 hover:bg-amber-50 cursor-pointer transition shadow-2xs">
                        <input type="checkbox" name="sources[]" value="jobskind" checked class="w-4 h-4 text-amber-600 rounded border-amber-300 focus:ring-amber-500">
                        <div class="ml-3">
                            <span class="text-xs font-black text-amber-900 block">Jobskind.com</span>
                            <span class="text-[10px] text-amber-700 block leading-tight">CG Backup (Auto-Masked)</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. Filters Grid (Post Type, District, Date Range) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                <!-- Post Types -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                        Post Type (प्रकार):
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/60 text-xs font-semibold text-slate-800 cursor-pointer hover:bg-white">
                            <input type="checkbox" name="post_types[]" value="job" checked class="w-3.5 h-3.5 text-brand-600 rounded border-slate-300">
                            <span>नई भर्ती (Vacancies)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/60 text-xs font-semibold text-slate-800 cursor-pointer hover:bg-white">
                            <input type="checkbox" name="post_types[]" value="admit_card" class="w-3.5 h-3.5 text-brand-600 rounded border-slate-300">
                            <span>प्रवेश पत्र (Admit Card)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/60 text-xs font-semibold text-slate-800 cursor-pointer hover:bg-white">
                            <input type="checkbox" name="post_types[]" value="answer_key" class="w-3.5 h-3.5 text-brand-600 rounded border-slate-300">
                            <span>उत्तर कुंजी (Answer Key)</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-slate-50/60 text-xs font-semibold text-slate-800 cursor-pointer hover:bg-white">
                            <input type="checkbox" name="post_types[]" value="result" class="w-3.5 h-3.5 text-brand-600 rounded border-slate-300">
                            <span>परिणाम (Results)</span>
                        </label>
                    </div>
                </div>

                <!-- District Dropdown (for E-Rojgar / Contractual) -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                        छत्तीसगढ़ जिला (E-Rojgar / District Filter):
                    </label>
                    <select name="district" class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-brand-500 focus:outline-none">
                        @foreach($districts as $key => $name)
                            <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-slate-400 mt-1 block">33 जिलों के कलेक्टोरेट व जिला रोजगार कार्यालय</span>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                        विज्ञप्ति दिनांक सीमा (Date Range):
                    </label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input type="date" name="from" value="{{ date('Y-m-d', strtotime('-30 days')) }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <span class="text-[9px] text-slate-400 block mt-0.5">From Date</span>
                        </div>
                        <div>
                            <input type="date" name="to" value="{{ date('Y-m-d') }}" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <span class="text-[9px] text-slate-400 block mt-0.5">To Date</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button Row -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <div class="text-xs text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-shield-halved text-emerald-600"></i>
                    <span>सोर्स मास्किंग सक्रिय: Jobskind/FreeJobAlert का नाम कभी भी पब्लिक वेबसाइट या ऐप में नहीं दिखाया जाएगा।</span>
                </div>

                <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-black shadow-md shadow-brand-600/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>Run Scraper Now (स्क्रैपिंग शुरू करें)</span>
                </button>
            </div>
        </form>
    </div>

    <!-- INTERMEDIATE STAGING GRID (Draft Scraped Notices - Pre-Gemini Review) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center font-black text-sm">
                        {{ $stagedItems->total() }}
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                            <span>इंटरमीडिएट ड्राफ्ट ग्रिड (Staging Review Area)</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Ready for Selection</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            स्क्रैप की गई कच्ची विज्ञप्तियां। जिन्हें रखना है उन्हें टिक करें, अवांछित को डिलीट करें, और 1-क्लिक में जेमिनी बैच में भेजें।
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Controls for Staging Grid -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Delete Selected Form Trigger -->
                <button type="button" onclick="submitBatchAction('{{ route('admin.jobs.scraper-console.delete-staged') }}', 'delete')" class="px-3.5 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Delete Selected</span>
                </button>

                <!-- Clear All Staged -->
                <form action="{{ route('admin.jobs.scraper-console.delete-staged') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all staged items?')">
                    @csrf
                    <input type="hidden" name="clear_all" value="1">
                    <button type="submit" class="px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 text-xs font-bold transition">
                        Clear All Staged
                    </button>
                </form>

                <!-- SEND TO GEMINI BATCH (1 SINGLE API CALL) -->
                <button type="button" onclick="submitBatchAction('{{ route('admin.jobs.scraper-console.batch-gemini') }}', 'gemini')" class="px-5 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-black shadow-md shadow-purple-600/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i>
                    <span>Send Selected to Gemini (1 Batch API Call)</span>
                </button>
            </div>
        </div>

        @if($stagedItems->count() > 0)
            <form id="stagingForm" method="POST">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50/80 border-b border-slate-100 text-slate-500 uppercase tracking-wider font-extrabold text-[10px]">
                            <tr>
                                <th class="p-4 w-12 text-center">
                                    <input type="checkbox" id="selectAllStaged" onclick="toggleSelectAll(this)" class="w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                                </th>
                                <th class="py-4 px-3">प्राधिकरण (Category)</th>
                                <th class="py-4 px-3 w-2/5">विज्ञप्ति शीर्षक व विवरण (Raw Scraped Title)</th>
                                <th class="py-4 px-3">दिनांक / जिला</th>
                                <th class="py-4 px-3">कच्चा सोर्स व लिंक</th>
                                <th class="py-4 px-3 text-right">कार्रवाई</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($stagedItems as $item)
                                <tr class="hover:bg-slate-50/60 transition group">
                                    <td class="p-4 text-center">
                                        <input type="checkbox" name="selected_ids[]" value="{{ $item->id }}" class="staged-checkbox w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                                    </td>
                                    <td class="py-4 px-3">
                                        @php
                                            $catBadge = match($item->job_category) {
                                                'CGPSC' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                                'CGSSB' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                'CONTRACTUAL' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                'CENTRAL GOVT' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                default => 'bg-slate-100 text-slate-800 border-slate-200'
                                            };
                                        @endphp
                                        <span class="inline-block px-2.5 py-1 rounded-lg text-[10px] font-black border {{ $catBadge }}">
                                            {{ $item->job_category ?: 'CGSSB' }}
                                        </span>
                                        <span class="block text-[10px] text-slate-400 mt-1 font-semibold">
                                            {{ $item->published_by ?: 'CG Govt' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-3">
                                        <div class="font-bold text-slate-900 line-clamp-2">
                                            {{ $item->title }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-1 line-clamp-2">
                                            {{ $item->summary }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-3">
                                        <div class="font-semibold text-slate-800">
                                            {{ $item->published_at ? $item->published_at->format('d M Y') : '—' }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            {{ $item->raw_payload['district'] ?? 'All CG' }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-3">
                                        @if(!empty($item->external_url))
                                            <a href="{{ $item->external_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-brand-600 hover:text-brand-700 font-bold">
                                                <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                                <span>View Notice Link</span>
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-[10px]">No direct URL</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-3 text-right">
                                        <button type="button" onclick="deleteSingleStaged({{ $item->id }})" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </form>

            <div class="p-4 border-t border-slate-100 bg-slate-50/40 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    Showing {{ $stagedItems->firstItem() }} to {{ $stagedItems->lastItem() }} of {{ $stagedItems->total() }} staged notices
                </div>
                <div>
                    {{ $stagedItems->appends(request()->except('staged_page'))->links() }}
                </div>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-xl">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <h3 class="text-sm font-black text-slate-800">स्टेजिंग ग्रिड खाली है (No Staged Notices)</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    ऊपर दिए गए "Run Scraper Now" बटन पर क्लिक करके आधिकारिक पोर्टलों से नई विज्ञप्तियां फेच करें।
                </p>
            </div>
        @endif
    </div>

    <!-- GEMINI PROCESSED DRAFTS & READY QUEUE -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 flex items-center justify-center font-black text-sm">
                    {{ $draftJobs->total() }}
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>जेमिनी द्वारा तैयार ड्राफ्ट्स (Ready Drafts)</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Ready to Publish</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        60 शब्दों का ऐप कैप्सूल, फुल वेब आर्टिकल (HTML), विभाग व पात्रता स्वतः जनरेटेड। त्वरित एडिट या पब्लिश करें।
                    </p>
                </div>
            </div>

            <a href="{{ route('admin.jobs.index', ['workflow_status' => 'draft']) }}" class="text-xs text-brand-600 font-bold hover:underline flex items-center gap-1">
                <span>View all Drafts in Jobs Manager</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        @if($draftJobs->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($draftJobs as $job)
                    <div class="p-5 hover:bg-slate-50/50 transition flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $job->job_category ?: 'CGSSB' }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $job->department ?: $job->category }}
                                </span>
                                @if($job->vacancies)
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-users text-[9px] mr-1"></i>{{ $job->vacancies }} पद
                                    </span>
                                @endif
                                @if($job->last_date)
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-regular fa-clock text-[9px] mr-1"></i>अंतिम तिथि: {{ $job->last_date }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-sm font-black text-slate-900 leading-snug">
                                {{ $job->title }}
                            </h3>

                            <!-- 60 words app summary capsule preview -->
                            <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200/60 leading-relaxed font-normal">
                                <span class="font-bold text-slate-800 mr-1">📱 60-Word App Capsule:</span>
                                {{ $job->summary }}
                            </div>

                            <div class="flex flex-wrap items-center gap-4 text-[11px] text-slate-500">
                                <span><strong>पात्रता:</strong> {{ $job->eligibility ?: 'विस्तृत विज्ञापन देखें' }}</span>
                                @if($job->salary)
                                    <span><strong>वेतनमान:</strong> {{ $job->salary }}</span>
                                @endif
                                <span><strong>मास्क्ड सोर्स:</strong> {{ $job->source }}</span>
                            </div>
                        </div>

                        <!-- Action buttons for Draft Job -->
                        <div class="flex flex-wrap lg:flex-col items-end gap-2 shrink-0">
                            <!-- Quick Publish Button Form -->
                            <form action="{{ route('admin.jobs.scraper-console.publish', $job) }}" method="POST" class="inline-flex items-center gap-2">
                                @csrf
                                <label class="text-[10px] font-bold text-slate-500 flex items-center gap-1 cursor-pointer mr-1">
                                    <input type="checkbox" name="send_notification" value="1" checked class="w-3.5 h-3.5 text-brand-600 rounded">
                                    <span>Push to App</span>
                                </label>
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>Publish Live</span>
                                </button>
                            </form>

                            <div class="flex items-center gap-1.5">
                                <!-- Edit Full Post -->
                                <a href="{{ route('admin.jobs.edit', $job) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition flex items-center gap-1">
                                    <i class="fa-solid fa-pen-to-square text-slate-400"></i>
                                    <span>Edit</span>
                                </a>

                                <!-- Schedule Trigger -->
                                <button type="button" onclick="openScheduleModal({{ $job->id }}, '{{ addslashes($job->title) }}')" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition flex items-center gap-1">
                                    <i class="fa-solid fa-calendar-plus text-purple-500"></i>
                                    <span>Schedule</span>
                                </button>

                                <!-- Delete Draft -->
                                <form action="{{ route('admin.jobs.scraper-console.delete-draft', $job) }}" method="POST" onsubmit="return confirm('Delete this draft?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete Draft">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-slate-100 bg-slate-50/40">
                {{ $draftJobs->appends(request()->except('drafts_page'))->links() }}
            </div>
        @else
            <div class="p-8 text-center text-xs text-slate-500">
                कोई तैयार ड्राफ्ट नहीं है। ऊपर स्टेजिंग ग्रिड से विज्ञप्तियां चुनकर "Send Selected to Gemini" पर क्लिक करें।
            </div>
        @endif
    </div>

</div>

<!-- Schedule Modal -->
<div id="scheduleModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-calendar-check text-purple-600"></i>
                <span>भर्ती पोस्ट शेड्यूल करें (Schedule Job)</span>
            </h3>
            <button type="button" onclick="closeScheduleModal()" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        <p id="scheduleJobTitle" class="text-xs text-slate-600 font-semibold line-clamp-2"></p>

        <form id="scheduleForm" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">
                    प्रकाशन दिनांक व समय (Publish At):
                </label>
                <input type="datetime-local" name="scheduled_at" required class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeScheduleModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-black shadow-md shadow-purple-600/20">
                    Save Schedule
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.staged-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
}

function submitBatchAction(url, type) {
    const checked = document.querySelectorAll('.staged-checkbox:checked');
    if (checked.length === 0) {
        alert('कृपया कम से कम एक विज्ञप्ति चुनें। (Please select at least one staged item)');
        return;
    }

    if (type === 'delete' && !confirm(`क्या आप वाकई चयनित ${checked.length} विज्ञप्तियों को हटाना चाहते हैं?`)) {
        return;
    }

    if (type === 'gemini') {
        const btn = event.currentTarget;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Processing batch with Gemini AI...';
    }

    const form = document.getElementById('stagingForm');
    form.action = url;
    form.submit();
}

function deleteSingleStaged(id) {
    if (!confirm('Delete this notice?')) return;
    const form = document.getElementById('stagingForm');
    form.action = "{{ route('admin.jobs.scraper-console.delete-staged') }}";
    
    // Uncheck all, check only this ID
    document.querySelectorAll('.staged-checkbox').forEach(cb => cb.checked = false);
    const cb = document.querySelector(`.staged-checkbox[value="${id}"]`);
    if (cb) cb.checked = true;
    form.submit();
}

function openScheduleModal(jobId, jobTitle) {
    const modal = document.getElementById('scheduleModal');
    const form = document.getElementById('scheduleForm');
    const titleElem = document.getElementById('scheduleJobTitle');
    
    titleElem.innerText = jobTitle;
    form.action = `/admin/jobs/scraper-console/schedule/${jobId}`;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeScheduleModal() {
    const modal = document.getElementById('scheduleModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
