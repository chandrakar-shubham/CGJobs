@extends('layouts.admin')

@section('title', 'डैशबोर्ड (Dashboard)')

@section('content')
<div class="space-y-8">

    <!-- Executive Hero Welcome Section -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 sm:p-10 shadow-xl border border-white/10">
        
        <!-- Subtle Glow Orbs -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl space-y-4">
            
            <!-- Live Status Pill -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-brand-200 border border-white/15 shadow-inner">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                </span>
                <span>CGJobs Live Control Center</span>
                <span class="text-white/40">•</span>
                <span class="text-slate-300">Hostinger Production Ready</span>
            </div>

            <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white leading-tight">
                छत्तीसगढ़ रोजगार एवं परीक्षा प्रबंधन पोर्टल
            </h1>

            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl font-normal">
                यहाँ से आप छत्तीसगढ़ लोक सेवा आयोग (CGPSC), व्यापम, पुलिस, शिक्षक भर्ती, दैनिक समसामयिकी (Current Affairs) एवं Static GK का संपूर्ण संचालन तथा मोबाइल ऐप यूज़र्स को लाइव नोटिफिकेशन प्रबंधित कर सकते हैं।
            </p>

            <!-- Quick Action Buttons -->
            <div class="pt-2 flex flex-wrap gap-3">
                <a href="{{ route('admin.jobs.create', ['section' => 'jobs']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-black shadow-lg shadow-brand-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-circle-plus"></i>
                    <span>+ नई भर्ती पोस्ट करें</span>
                </a>

                <a href="{{ route('admin.alerts.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>पुश अलर्ट भेजें</span>
                </a>

                <a href="{{ route('admin.ai-engine.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-purple-600/80 hover:bg-purple-600 text-white text-xs font-black border border-purple-400/30 transition">
                    <i class="fa-solid fa-robot"></i>
                    <span>AI कंटेंट इंजन</span>
                </a>

                <a href="{{ url('/') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold border border-white/15 transition">
                    <i class="fa-solid fa-arrow-up-right-from-square text-slate-300"></i>
                    <span>वेबसाइट देखें</span>
                </a>
            </div>

        </div>

        <div class="absolute right-6 bottom-6 hidden xl:block opacity-10 pointer-events-none">
            <i class="fa-solid fa-landmark text-[180px] text-white"></i>
        </div>
    </div>

    <!-- Live Ticker Controller Banner -->
    <div class="rounded-2xl p-4 bg-gradient-to-r from-amber-500 via-rose-500 to-amber-600 text-white shadow-md flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-hidden w-full sm:w-auto">
            <span class="px-2.5 py-1 rounded-lg bg-black/25 text-[10px] font-black uppercase tracking-wider whitespace-nowrap flex items-center gap-1.5 shadow-xs">
                <i class="fa-solid fa-bullhorn text-amber-200"></i>
                <span>LIVE TICKER</span>
            </span>
            <span class="text-xs sm:text-sm font-bold truncate">
                {{ $tickerText ?: 'कोई टिकर संदेश अभी सक्रिय नहीं है।' }}
            </span>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 self-end sm:self-auto">
            <span class="text-[11px] px-2.5 py-0.5 rounded-full font-bold {{ $tickerEnabled ? 'bg-emerald-950/40 text-emerald-200 border border-emerald-300/40' : 'bg-black/30 text-rose-200' }}">
                {{ $tickerEnabled ? '● Active in App & Web' : '○ Paused' }}
            </span>
            <a href="{{ route('admin.settings.index') }}" class="px-3 py-1.5 rounded-lg bg-white text-slate-900 hover:bg-slate-100 text-xs font-black shadow-sm transition">
                बदलें / Edit
            </a>
        </div>
    </div>

    <!-- Executive Metrics Grid (6 KPI Cards) -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple text-brand-600"></i>
                <span>सिस्टम आंकड़े (Executive Metrics)</span>
            </h2>
            <span class="text-xs text-slate-400 font-medium">Real-time database count</span>
        </div>

        @php
            $stats = [
                [
                    'label' => 'कुल सरकारी भर्तियां',
                    'sub' => 'Total Jobs',
                    'value' => $totalJobs,
                    'icon' => 'fa-briefcase',
                    'bg' => 'bg-blue-50',
                    'border' => 'border-blue-100',
                    'text' => 'text-blue-600',
                    'url' => route('admin.jobs.index', ['section' => 'jobs'])
                ],
                [
                    'label' => 'दैनिक समसामयिकी',
                    'sub' => 'Current Affairs',
                    'value' => $totalNews,
                    'icon' => 'fa-newspaper',
                    'bg' => 'bg-cyan-50',
                    'border' => 'border-cyan-100',
                    'text' => 'text-cyan-600',
                    'url' => route('admin.news.index')
                ],
                [
                    'label' => 'Static GK अध्ययन नोट्स',
                    'sub' => 'Study Notes',
                    'value' => $totalGk,
                    'icon' => 'fa-book-open',
                    'bg' => 'bg-indigo-50',
                    'border' => 'border-indigo-100',
                    'text' => 'text-indigo-600',
                    'url' => route('admin.static-gk.index')
                ],
                [
                    'label' => 'FCM ऐप डिवाइसेस',
                    'sub' => 'Android Users',
                    'value' => $totalDevices,
                    'icon' => 'fa-mobile-screen',
                    'bg' => 'bg-emerald-50',
                    'border' => 'border-emerald-100',
                    'text' => 'text-emerald-600',
                    'url' => route('admin.alerts.index')
                ],
                [
                    'label' => 'सक्रिय पुश अलर्ट्स',
                    'sub' => 'Push Alerts',
                    'value' => $totalAlerts,
                    'icon' => 'fa-bell',
                    'bg' => 'bg-amber-50',
                    'border' => 'border-amber-100',
                    'text' => 'text-amber-600',
                    'url' => route('admin.alerts.index')
                ],
                [
                    'label' => 'कंटेंट सेक्शंस',
                    'sub' => 'Sections',
                    'value' => $totalSections,
                    'icon' => 'fa-layer-group',
                    'bg' => 'bg-purple-50',
                    'border' => 'border-purple-100',
                    'text' => 'text-purple-600',
                    'url' => route('admin.categories.index')
                ],
            ];
        @endphp

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($stats as $stat)
                <a href="{{ $stat['url'] }}" class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md hover:-translate-y-1 transition group flex flex-col justify-between min-h-[140px]">
                    <div class="flex items-center justify-between">
                        <div class="w-11 h-11 rounded-2xl {{ $stat['bg'] }} {{ $stat['text'] }} flex items-center justify-center text-lg shadow-2xs group-hover:scale-110 transition">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider group-hover:text-brand-600 transition">
                            Open &rarr;
                        </span>
                    </div>

                    <div class="mt-3">
                        <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            {{ number_format($stat['value']) }}
                        </p>
                        <p class="text-[11px] font-bold text-slate-700 truncate mt-0.5">
                            {{ $stat['label'] }}
                        </p>
                        <p class="text-[10px] text-slate-400 font-semibold">
                            {{ $stat['sub'] }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Quick Action Launchpad (4 Elevated Cards) -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-500 flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i>
                <span>त्वरित कार्य हब (Quick Actions Launchpad)</span>
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.jobs.create', ['section' => 'jobs']) }}" class="group bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:border-brand-500 hover:shadow-md transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 group-hover:bg-brand-600 group-hover:text-white flex items-center justify-center text-xl shrink-0 transition">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-brand-600 transition truncate">
                        + नई भर्ती पोस्ट करें
                    </h3>
                    <p class="text-xs text-slate-500 truncate mt-0.5">Post Job Vacancy</p>
                </div>
            </a>

            <a href="{{ route('admin.jobs.create', ['section' => 'news']) }}" class="group bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:border-cyan-500 hover:shadow-md transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 group-hover:bg-cyan-600 group-hover:text-white flex items-center justify-center text-xl shrink-0 transition">
                    <i class="fa-solid fa-newspaper"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-cyan-600 transition truncate">
                        + समसामयिकी समाचार
                    </h3>
                    <p class="text-xs text-slate-500 truncate mt-0.5">Current Affairs Post</p>
                </div>
            </a>

            <a href="{{ route('admin.static-gk.create') }}" class="group bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:border-indigo-500 hover:shadow-md transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white flex items-center justify-center text-xl shrink-0 transition">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition truncate">
                        + Static GK कार्ड
                    </h3>
                    <p class="text-xs text-slate-500 truncate mt-0.5">Exam GK Study Notes</p>
                </div>
            </a>

            <a href="{{ route('admin.settings.index') }}" class="group bg-white p-5 rounded-3xl border border-slate-200/80 shadow-xs hover:border-amber-500 hover:shadow-md transition flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white flex items-center justify-center text-xl shrink-0 transition">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-amber-600 transition truncate">
                        लाइव टिकर व सेटिंग्स
                    </h3>
                    <p class="text-xs text-slate-500 truncate mt-0.5">System & Banners</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Intelligence & Operations Split -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left 8 Columns: Recent Recruitment Jobs Table -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-5">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center font-bold text-sm">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-black text-slate-900">हाल ही में प्रकाशित भर्तियां</h2>
                        <p class="text-[11px] text-slate-400">Recent Government Recruitment Releases</p>
                    </div>
                </div>

                <a href="{{ route('admin.jobs.index') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-brand-50 hover:text-brand-700 text-slate-700 text-xs font-bold transition">
                    सभी देखें &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-[11px] font-extrabold uppercase tracking-wider">
                            <th class="pb-3 pl-2">भर्ती शीर्षक व विभाग</th>
                            <th class="pb-3 px-3">पद संख्या</th>
                            <th class="pb-3 px-3">अंतिम तिथि</th>
                            <th class="pb-3 pr-2 text-right">कार्रवाई</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentJobs as $job)
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="py-4 pl-2 pr-4">
                                    <div class="flex items-start gap-3">
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('admin.jobs.edit', $job) }}" class="font-bold text-slate-900 group-hover:text-brand-600 transition line-clamp-1 text-xs sm:text-sm">
                                                {{ $job->title }}
                                            </a>
                                            <div class="flex flex-wrap items-center gap-2 mt-1.5">
                                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold">
                                                    {{ $job->category ?: 'CGSSB' }}
                                                </span>
                                                @if($job->department)
                                                    <span class="text-[11px] text-slate-500 truncate max-w-[200px]">
                                                        {{ $job->department }}
                                                    </span>
                                                @endif
                                                @if($job->is_breaking)
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-500 text-white animate-pulse">
                                                        HOT
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-3 whitespace-nowrap">
                                    <span class="text-xs font-bold text-slate-700">
                                        {{ $job->vacancies ?: 'अधिसूचना अनुसार' }}
                                    </span>
                                </td>

                                <td class="py-4 px-3 whitespace-nowrap">
                                    <span class="text-xs font-medium text-slate-600 flex items-center gap-1.5">
                                        <i class="fa-regular fa-calendar text-slate-400 text-[10px]"></i>
                                        <span>{{ $job->last_date ?: 'शीघ्र' }}</span>
                                    </span>
                                </td>

                                <td class="py-4 pr-2 pl-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('job.show', $job->custom_id ?: $job->id) }}" target="_blank" rel="noopener" class="p-2 rounded-xl text-slate-400 hover:text-brand-600 hover:bg-slate-100 transition" title="Preview on Website">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.jobs.edit', $job) }}" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit Post">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center text-xs text-slate-400">
                                    वर्तमान में कोई भर्ती उपलब्ध नहीं है।
                                    <a href="{{ route('admin.jobs.create') }}" class="text-brand-600 font-bold underline ml-1">
                                        पहली भर्ती जोड़ें
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Right 4 Columns: Push Alerts & Android App Station -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Recent Alerts Widget -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-bell"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-900">हालिया अलर्ट्स</h3>
                            <p class="text-[10px] text-slate-400">Recent FCM Push Broadcasts</p>
                        </div>
                    </div>

                    <a href="{{ route('admin.alerts.create') }}" class="px-2.5 py-1 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 text-xs font-bold transition">
                        + नया अलर्ट
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentAlerts as $alert)
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 hover:border-slate-300 transition space-y-1">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-extrabold uppercase text-brand-600">{{ $alert->category ?: 'General' }}</span>
                                <span class="text-slate-400">{{ $alert->time ?: 'हाल ही में' }}</span>
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 line-clamp-1 leading-snug">
                                {{ $alert->title }}
                            </h4>
                            <p class="text-[11px] text-slate-600 line-clamp-2">
                                {{ $alert->short_description }}
                            </p>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400 space-y-1">
                            <i class="fa-regular fa-bell-slash text-2xl text-slate-300 block mb-1"></i>
                            <p>कोई हालिया अलर्ट नहीं मिला।</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Android App Connection & API Status Station -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-3xl p-6 text-white space-y-4 shadow-xl border border-slate-800">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-extrabold uppercase tracking-wider">
                        <i class="fa-brands fa-android text-xs"></i>
                        <span>Android App API</span>
                    </span>

                    <a href="{{ url('/api/news') }}" target="_blank" rel="noopener" class="text-[11px] font-bold text-brand-400 hover:text-white transition flex items-center gap-1">
                        <span>API Test</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                    </a>
                </div>

                <div>
                    <h3 class="text-base font-bold text-white">मोबाइल ऐप व सर्वर इंटीग्रेशन</h3>
                    <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                        यह बैकएंड Android ऐप के साथ 100% सिंक्रनाइज़्ड है। ऐप में नया डेटा स्वतः लोड होता है।
                    </p>
                </div>

                <div class="space-y-2 pt-1">
                    <div class="bg-slate-800/80 p-3 rounded-2xl border border-slate-700/80">
                        <div class="flex items-center justify-between text-[11px] text-slate-300 font-semibold mb-1">
                            <span>📱 Production API Base:</span>
                            <span class="text-emerald-400 font-mono text-[10px]">Ready</span>
                        </div>
                        <div class="flex items-center justify-between bg-slate-950 px-2.5 py-1.5 rounded-xl border border-slate-800 text-[11px] font-mono text-slate-200">
                            <span class="truncate">{{ url('/') }}/api/</span>
                            <button onclick="navigator.clipboard.writeText('{{ url('/') }}/api/'); alert('API URL Copied!');" class="text-[10px] text-brand-400 hover:text-white font-bold ml-2">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex items-center justify-between text-[11px] text-slate-400">
                    <span>FCM Notifications</span>
                    <span class="text-emerald-400 font-bold">Enabled</span>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
