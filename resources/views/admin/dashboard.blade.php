@extends('layouts.admin')

@section('title', 'डैशबोर्ड (Dashboard)')

@section('content')
<div class="space-y-6">

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-brand-700 via-brand-600 to-indigo-700 rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <span class="inline-block px-3 py-1 bg-white/15 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-saffron-300 mb-3">
                <i class="fa-solid fa-bolt mr-1"></i> Live Control Center
            </span>
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight mb-2">
                स्वागत है, CGJobs एडमिन पैनल में
            </h2>
            <p class="text-blue-100 text-sm leading-relaxed">
                यहाँ से आप छत्तीसगढ़ के सरकारी विभागों (व्यापम, CGPSC, पुलिस, शिक्षक) की नई भर्तियों, प्रवेश पत्र, रिजल्ट व ब्रेकिंग न्यूज़ को तुरंत प्रकाशित और मोबाइल यूज़र्स तक पहुंचा सकते हैं।
            </p>

            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('admin.jobs.create') }}" class="inline-flex items-center px-4 py-2.5 bg-saffron-500 hover:bg-saffron-600 text-white font-semibold text-sm rounded-xl shadow-md transition transform active:scale-95">
                    <i class="fa-solid fa-plus mr-2"></i> नई भर्ती पोस्ट करें
                </a>
                <a href="{{ route('admin.alerts.create') }}" class="inline-flex items-center px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white font-semibold text-sm rounded-xl backdrop-blur-md transition">
                    <i class="fa-solid fa-paper-plane mr-2"></i> पुश अलर्ट भेजें
                </a>
            </div>
        </div>

        <div class="absolute right-0 bottom-0 top-0 w-1/3 opacity-10 flex items-center justify-center pointer-events-none">
            <i class="fa-solid fa-landmark text-9xl"></i>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-brand-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">कुल प्रकाशित भर्तियां</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $totalJobs }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">सूचनाएं व अलर्ट्स</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $totalAlerts }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">रजिस्टर्ड मोबाइल फोन</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $totalDevices }}</h3>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-fire"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">ब्रेकिंग / हॉट भर्तियां</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $breakingJobs }}</h3>
            </div>
        </div>
    </div>

    <!-- Android App Integration Connection Guide Box -->
    <div class="bg-white rounded-2xl border border-blue-200 shadow-sm p-5 sm:p-6">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                    <i class="fa-brands fa-android mr-1.5 text-emerald-600"></i> Android App Live Connection
                </span>
                <h3 class="text-base font-bold text-slate-800">मोबाइल ऐप को इस Laravel बैकएंड से कैसे जोड़ें</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-xl">
                    Android ऐप के <b>Server Settings</b> में जाकर नीचे दिया गया URL पेस्ट करें।
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/api/news') }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                    <i class="fa-solid fa-code mr-1"></i> View JSON API
                </a>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <p class="text-xs font-semibold text-slate-700">📱 Android Emulator यूज़र्स हेतु:</p>
                <div class="flex items-center justify-between mt-1.5 bg-white px-3 py-2 rounded-lg border text-xs font-mono text-slate-800">
                    <span>http://10.0.2.2:8000/</span>
                    <span class="text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">Pre-configured</span>
                </div>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <p class="text-xs font-semibold text-slate-700">🌐 Cloud / Production सर्वर URL:</p>
                <div class="flex items-center justify-between mt-1.5 bg-white px-3 py-2 rounded-lg border text-xs font-mono text-slate-800">
                    <span class="truncate">{{ url('/') }}/</span>
                    <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-medium">Ready</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Jobs & Alerts Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Recent Jobs (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-brand-600"></i>
                    हाल ही में प्रकाशित भर्तियां
                </h3>
                <a href="{{ route('admin.jobs.index') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-800">
                    सभी देखें (View all) &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider">
                            <th class="pb-3 font-semibold">शीर्षक व विभाग</th>
                            <th class="pb-3 font-semibold">पद</th>
                            <th class="pb-3 font-semibold">अंतिम तिथि</th>
                            <th class="pb-3 font-semibold text-right">कार्रवाई</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentJobs as $job)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3.5 pr-3">
                                    <div class="font-medium text-slate-900 line-clamp-1">{{ $job->title }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-block px-2 py-0.5 bg-slate-100 text-slate-600 text-[11px] rounded font-medium">
                                            {{ $job->category }}
                                        </span>
                                        @if($job->is_breaking)
                                            <span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-700 text-[10px] rounded font-bold uppercase">
                                                HOT
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-2 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $job->vacancies ?: 'अधिसूचना अनुसार' }}
                                </td>
                                <td class="py-3.5 px-2 text-xs text-slate-500 whitespace-nowrap">
                                    {{ $job->last_date ?: 'शीघ्र' }}
                                </td>
                                <td class="py-3.5 pl-3 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.jobs.edit', $job) }}" class="text-brand-600 hover:text-brand-800 text-xs font-semibold mr-3">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-6 text-center text-xs text-slate-400">
                                    अभी कोई भर्ती उपलब्ध नहीं है। <a href="{{ route('admin.jobs.create') }}" class="text-brand-600 underline">नई भर्ती जोड़ें</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Recent Alerts (1 col) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-bullhorn text-amber-500"></i>
                    हालिया अलर्ट्स
                </h3>
                <a href="{{ route('admin.alerts.create') }}" class="text-xs font-semibold text-saffron-600 hover:text-saffron-700">
                    + नया अलर्ट
                </a>
            </div>

            <div class="space-y-3">
                @forelse($recentAlerts as $alert)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-slate-200 transition">
                        <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                            <span class="font-semibold text-brand-600">{{ $alert->category }}</span>
                            <span>{{ $alert->time ?: 'हाल ही में' }}</span>
                        </div>
                        <h4 class="text-xs font-bold text-slate-800 leading-snug">{{ $alert->title }}</h4>
                        <p class="text-[11px] text-slate-600 mt-1 line-clamp-2">{{ $alert->short_description }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">कोई अलर्ट नहीं भेजा गया है।</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
