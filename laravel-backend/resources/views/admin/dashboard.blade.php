@extends('layouts.admin')

@section('title', 'डैशबोर्ड (Dashboard)')

@section('content')
<div class="space-y-8">

    <!-- Welcome Banner -->
    <section class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6 shadow-md relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-flex items-center px-3 py-1 bg-blue-100 rounded-full text-xs font-semibold uppercase tracking-wider text-blue-600 mb-3">
                <i class="fa-solid fa-bolt mr-1" aria-hidden="true"></i> Live Control Center
            </span>
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2 text-gray-900">स्वागत है, CGJobs एडमिन पैनल में</h2>
            <p class="text-gray-600 text-sm leading-relaxed">
                यहाँ से आप छत्तीसगढ़ के सरकारी विभागों (व्यापम, CGPSC, पुलिस, शिक्षक) की नई भर्तियों, प्रवेश पत्र, रिजल्ट व ब्रेकिंग न्यूज़ को तुरंत प्रकाशित और मोबाइल यूज़र्स तक पहुंचा सकते हैं।
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('admin.jobs.create') }}" class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-white font-bold text-sm rounded-xl shadow-md transition">
                    <i class="fa-solid fa-plus mr-2" aria-hidden="true"></i> नई भर्ती पोस्ट करें
                </a>
                <a href="{{ route('admin.alerts.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 text-white font-bold text-sm rounded-xl shadow-md transition">
                    <i class="fa-solid fa-paper-plane mr-2" aria-hidden="true"></i> पुश अलर्ट भेजें
                </a>
            </div>
        </div>
        <div class="absolute right-0 bottom-0 top-0 w-1/3 opacity-10 flex items-center justify-center pointer-events-none" aria-hidden="true">
            <i class="fa-solid fa-landmark text-9xl"></i>
        </div>
    </section>

    <!-- Live Breaking Ticker Preview Bar -->
    <section class="bg-gradient-to-r from-rose-500 via-amber-500 to-rose-600 rounded-2xl p-3.5 text-white shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3" aria-label="Live ticker">
        <div class="flex items-center gap-2.5 overflow-hidden w-full sm:w-auto">
            <span class="bg-white/20 px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-wider whitespace-nowrap flex items-center gap-1.5">
                <i class="fa-solid fa-bullhorn text-amber-200" aria-hidden="true"></i> LIVE TICKER
            </span>
            <span class="text-sm font-semibold truncate">{{ $tickerText ?: 'कोई टिकर संदेश सक्रिय नहीं है।' }}</span>
        </div>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <span class="text-[10px] px-2 py-0.5 rounded-full {{ $tickerEnabled ? 'bg-emerald-400/30 text-white border border-emerald-300/40' : 'bg-rose-900/40 text-rose-100' }}">
                {{ $tickerEnabled ? '● Active in App' : '○ Paused' }}
            </span>
            <a href="{{ route('admin.settings.index') }}" class="px-3 py-1.5 bg-white text-slate-900 hover:bg-slate-100 rounded-lg text-xs font-bold transition shadow-sm">बदलें / Edit</a>
        </div>
    </section>

    <!-- Stats Grid -->
    <section aria-labelledby="dashboard-stats-heading">
        <h2 id="dashboard-stats-heading" class="sr-only">सिस्टम आंकड़े</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                $stats = [
                    ['label' => 'भर्तियां (Jobs)', 'value' => $totalJobs, 'icon' => 'fa-briefcase', 'iconBg' => 'bg-blue-50', 'iconText' => 'text-blue-700'],
                    ['label' => 'समाचार (News)', 'value' => $totalNews, 'icon' => 'fa-newspaper', 'iconBg' => 'bg-cyan-50', 'iconText' => 'text-cyan-700'],
                    ['label' => 'Static GK', 'value' => $totalGk, 'icon' => 'fa-book-open', 'iconBg' => 'bg-indigo-50', 'iconText' => 'text-indigo-700'],
                    ['label' => 'सेक्शंस (Sections)', 'value' => $totalSections, 'icon' => 'fa-layer-group', 'iconBg' => 'bg-purple-50', 'iconText' => 'text-purple-700'],
                    ['label' => 'अलर्ट्स (Alerts)', 'value' => $totalAlerts, 'icon' => 'fa-bell', 'iconBg' => 'bg-amber-50', 'iconText' => 'text-amber-700'],
                    ['label' => 'यूज़र्स (FCM)', 'value' => $totalDevices, 'icon' => 'fa-mobile-screen', 'iconBg' => 'bg-emerald-50', 'iconText' => 'text-emerald-700'],
                ];
            @endphp
            @foreach($stats as $stat)
                <article class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all min-h-[132px] flex flex-col justify-between">
                    <div class="flex items-center justify-between gap-2">
                        <div class="w-10 h-10 rounded-xl {{ $stat['iconBg'] }} {{ $stat['iconText'] }} flex items-center justify-center text-base" aria-hidden="true">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">Total</span>
                    </div>
                    <div class="mt-3">
                        <p class="text-[11px] font-semibold text-slate-700 uppercase tracking-wide">{{ $stat['label'] }}</p>
                        <p class="text-2xl font-extrabold text-blue-700 leading-tight">{{ $stat['value'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <!-- Quick Management Action Tiles -->
    <section aria-labelledby="quick-actions-heading">
        <div class="flex items-center justify-between mb-4">
            <h2 id="quick-actions-heading" class="text-base font-bold text-slate-900">त्वरित कार्य</h2>
            <span class="text-xs text-slate-500">Quick Actions</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.jobs.create', ['section' => 'jobs']) }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-blue-300 hover:shadow-md transition flex items-center gap-3 min-h-[78px] focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-blue-50 text-blue-700 group-hover:bg-blue-100 flex items-center justify-center text-lg" aria-hidden="true"><i class="fa-solid fa-briefcase"></i></span>
                <span><span class="block text-sm font-bold text-slate-900">+ नई भर्ती पोस्ट करें</span><span class="block text-[11px] text-slate-500 mt-0.5">Add Job Vacancy</span></span>
            </a>
            <a href="{{ route('admin.jobs.create', ['section' => 'news']) }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-blue-300 hover:shadow-md transition flex items-center gap-3 min-h-[78px] focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-cyan-50 text-cyan-700 group-hover:bg-cyan-100 flex items-center justify-center text-lg" aria-hidden="true"><i class="fa-solid fa-newspaper"></i></span>
                <span><span class="block text-sm font-bold text-slate-900">+ समाचार / समसामयिकी</span><span class="block text-[11px] text-slate-500 mt-0.5">Current Affairs</span></span>
            </a>
            <a href="{{ route('admin.static-gk.create') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-indigo-300 hover:shadow-md transition flex items-center gap-3 min-h-[78px] focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-indigo-50 text-indigo-700 group-hover:bg-indigo-100 flex items-center justify-center text-lg" aria-hidden="true"><i class="fa-solid fa-book-open"></i></span>
                <span><span class="block text-sm font-bold text-slate-900">+ Static GK कार्ड</span><span class="block text-[11px] text-slate-500 mt-0.5">Study Notes</span></span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="group bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-amber-300 hover:shadow-md transition flex items-center gap-3 min-h-[78px] focus:outline-none focus:ring-2 focus:ring-amber-500">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-amber-50 text-amber-700 group-hover:bg-amber-100 flex items-center justify-center text-lg" aria-hidden="true"><i class="fa-solid fa-sliders"></i></span>
                <span><span class="block text-sm font-bold text-slate-900">लाइव टिकर व सेटिंग्स</span><span class="block text-[11px] text-slate-500 mt-0.5">App Ticker & Links</span></span>
            </a>
        </div>
    </section>

    <!-- Android App Integration Connection Guide Box -->
    <section class="bg-white rounded-2xl border border-blue-200 shadow-sm p-5 sm:p-6" aria-labelledby="android-heading">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                    <i class="fa-brands fa-android mr-1.5 text-emerald-600" aria-hidden="true"></i> Android App Live Connection
                </span>
                <h2 id="android-heading" class="text-base font-bold text-slate-900">मोबाइल ऐप को इस Laravel बैकएंड से कैसे जोड़ें</h2>
                <p class="text-xs text-slate-600 mt-1 max-w-xl">Android ऐप के <b>Server Settings</b> में जाकर नीचे दिया गया URL पेस्ट करें।</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/api/news') }}" target="_blank" rel="noopener" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fa-solid fa-code mr-1" aria-hidden="true"></i> View JSON API
                </a>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <p class="text-xs font-semibold text-slate-800">📱 Android Emulator यूज़र्स हेतु:</p>
                <div class="flex items-center justify-between mt-1.5 bg-white px-3 py-2 rounded-lg border text-xs font-mono text-slate-800"><span>http://10.0.2.2:8000/</span><span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">Pre-configured</span></div>
            </div>
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <p class="text-xs font-semibold text-slate-800">🌐 Cloud / Production सर्वर URL:</p>
                <div class="flex items-center justify-between mt-1.5 bg-white px-3 py-2 rounded-lg border text-xs font-mono text-slate-800"><span class="truncate">{{ url('/') }}/</span><span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-medium">Ready</span></div>
            </div>
        </div>
    </section>

    <!-- Recent Jobs & Alerts Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-5" aria-labelledby="recent-jobs-heading">
            <div class="flex items-center justify-between mb-4">
                <h2 id="recent-jobs-heading" class="text-base font-bold text-slate-900 flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-blue-700" aria-hidden="true"></i> हाल ही में प्रकाशित भर्तियां</h2>
                <a href="{{ route('admin.jobs.index') }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded">सभी देखें (View all) &rarr;</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead><tr class="border-b border-slate-200 text-slate-600 text-xs uppercase tracking-wider"><th class="pb-3 font-bold">शीर्षक व विभाग</th><th class="pb-3 font-bold">पद</th><th class="pb-3 font-bold">अंतिम तिथि</th><th class="pb-3 font-bold text-right">कार्रवाई</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentJobs as $job)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 pr-3"><div class="font-semibold text-slate-900 line-clamp-1">{{ $job->title }}</div><div class="flex items-center gap-2 mt-1"><span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-700 text-[11px] rounded font-medium">{{ $job->category }}</span>@if($job->is_breaking)<span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-700 text-[10px] rounded font-bold uppercase">HOT</span>@endif</div></td>
                                <td class="py-3.5 px-2 text-xs font-semibold text-slate-700 whitespace-nowrap">{{ $job->vacancies ?: 'अधिसूचना अनुसार' }}</td>
                                <td class="py-3.5 px-2 text-xs text-slate-600 whitespace-nowrap">{{ $job->last_date ?: 'शीघ्र' }}</td>
                                <td class="py-3.5 pl-3 text-right whitespace-nowrap"><a href="{{ route('admin.jobs.edit', $job) }}" class="text-blue-700 hover:text-blue-900 text-xs font-semibold mr-3 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded" aria-label="Edit {{ $job->title }}"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-xs text-slate-500">अभी कोई भर्ती उपलब्ध नहीं है। <a href="{{ route('admin.jobs.create') }}" class="text-blue-700 font-semibold underline">नई भर्ती जोड़ें</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5" aria-labelledby="recent-alerts-heading">
            <div class="flex items-center justify-between mb-4"><h2 id="recent-alerts-heading" class="text-base font-bold text-slate-900 flex items-center gap-2"><i class="fa-solid fa-bullhorn text-amber-600" aria-hidden="true"></i> हालिया अलर्ट्स</h2><a href="{{ route('admin.alerts.create') }}" class="text-xs font-semibold text-amber-700 hover:text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500 rounded">+ नया अलर्ट</a></div>
            <div class="space-y-3">
                @forelse($recentAlerts as $alert)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 hover:border-slate-300 transition"><div class="flex items-center justify-between text-[11px] text-slate-500 mb-1"><span class="font-semibold text-blue-700">{{ $alert->category }}</span><span>{{ $alert->time ?: 'हाल ही में' }}</span></div><h4 class="text-xs font-bold text-slate-900 leading-snug">{{ $alert->title }}</h4><p class="text-[11px] text-slate-600 mt-1 line-clamp-2">{{ $alert->short_description }}</p></div>
                @empty
                    <p class="text-xs text-slate-500 text-center py-6">कोई अलर्ट नहीं भेजा गया है।</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
