@extends('layouts.admin')

@section('title', 'अलर्ट्स व सूचनाएं (Alerts & Notifications)')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">अलर्ट्स व तत्काल सूचना केंद्र</h2>
            <p class="text-xs text-slate-500 mt-0.5">एडमिट कार्ड, रिजल्ट, परीक्षा तिथि व ब्रेकिंग अपडेट्स जो सीधे मोबाइल पर दिखते हैं</p>
        </div>
        <a href="{{ route('admin.alerts.create') }}" class="inline-flex items-center px-4 py-2.5 bg-saffron-500 hover:bg-saffron-600 text-white font-semibold text-xs rounded-xl shadow-sm transition">
            <i class="fa-solid fa-paper-plane mr-2"></i> नया अलर्ट / पुश नोटिफिकेशन भेजें
        </a>
    </div>

    <!-- Alerts Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4 font-semibold">ID</th>
                        <th class="py-3 px-4 font-semibold">प्रकार (Type)</th>
                        <th class="py-3 px-4 font-semibold">विभाग</th>
                        <th class="py-3 px-4 font-semibold">शीर्षक व विवरण</th>
                        <th class="py-3 px-4 font-semibold">समय</th>
                        <th class="py-3 px-4 font-semibold">FCM स्टेटस</th>
                        <th class="py-3 px-4 font-semibold text-right">कार्रवाई</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($alerts as $alert)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 text-xs font-mono text-slate-400">
                                #{{ $alert->id }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @php
                                    $badgeClasses = match($alert->type) {
                                        'ADMIT_CARD' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'RESULT' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'EXAM_DATE' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'DEADLINE' => 'bg-purple-100 text-purple-800 border-purple-200',
                                        default => 'bg-rose-100 text-rose-800 border-rose-200',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-[11px] font-bold rounded-md border {{ $badgeClasses }}">
                                    {{ $alert->type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs font-medium text-slate-600">
                                {{ $alert->category }}
                            </td>
                            <td class="py-3.5 px-4 max-w-sm">
                                <div class="font-bold text-slate-900 text-xs line-clamp-1">{{ $alert->title }}</div>
                                <div class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $alert->short_description }}</div>
                                @if($alert->action_url)
                                    <a href="{{ $alert->action_url }}" target="_blank" class="text-[10px] text-blue-600 hover:underline mt-0.5 inline-block">
                                        <i class="fa-solid fa-link mr-0.5"></i> लिंक खोलें
                                    </a>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-400">
                                {{ $alert->time ?: $alert->created_at?->diffForHumans() }}
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                @if($alert->is_broadcasted)
                                    <span class="inline-flex items-center text-xs text-emerald-600 font-semibold">
                                        <i class="fa-solid fa-check-double mr-1"></i> Sent
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Saved Only</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-right text-xs">
                                <form method="POST" action="{{ route('admin.alerts.destroy', $alert) }}" onsubmit="return confirm('अलर्ट हटाएं?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                                अभी कोई अलर्ट नहीं है। <a href="{{ route('admin.alerts.create') }}" class="text-brand-600 underline">नया अलर्ट भेजें</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($alerts->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
