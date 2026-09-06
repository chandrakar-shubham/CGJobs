@extends('layouts.admin')

@section('title', 'एप सेटिंग्स व लाइव टिकर')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80">
        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2.5">
            <i class="fa-solid fa-sliders text-amber-500"></i>
            <span>एप सेटिंग्स व लाइव ब्रेकिंग टिकर (App Settings & Ticker)</span>
        </h2>
        <p class="text-sm text-slate-500 mt-1">ऐप के शीर्ष पर चलने वाला लाइव ब्रेकिंग न्यूज़ टिकर, सोशल लिंक्स व संपर्क विवरण प्रबंधित करें</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Live Breaking News Ticker -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-bullhorn text-rose-500"></i>
                        <span>लाइव ब्रेकिंग न्यूज़ टिकर (Live Marquee Ticker)</span>
                    </h3>
                    <p class="text-xs text-slate-500">Android ऐप के होम स्क्रीन पर सबसे ऊपर लाल/सोनेरी पट्टी में स्क्रॉल होने वाला संदेश</p>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="ticker_enabled" value="1" {{ $settings['ticker_enabled'] === '1' ? 'checked' : '' }} class="w-4 h-4 text-brand-600 rounded border-slate-300">
                    <span class="text-xs font-bold text-slate-700">टिकर सक्षम रखें</span>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">टिकर संदेश (Ticker Headline Text)</label>
                <textarea name="ticker_text" rows="3" placeholder="उदा. CGPSC 2026 प्रारंभिक परीक्षा की तिथि जारी | व्यापम शिक्षक पात्रता परीक्षा प्रवेश पत्र डाउनलोड करें" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">{{ $settings['ticker_text'] }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">आप पाइप (|) चिन्ह का उपयोग करके कई घोषणाओं को अलग कर सकते हैं।</p>
            </div>
        </div>

        <!-- 2. Social & Community Links -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-share-nodes text-emerald-600"></i>
                <span>कम्युनिटी व सोशल मीडिया लिंक्स (App Drawer Links)</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">WhatsApp ग्रुप / चैनल URL</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-emerald-600">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </span>
                        <input type="url" name="whatsapp_url" value="{{ $settings['whatsapp_url'] }}" placeholder="https://chat.whatsapp.com/..." class="w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Telegram चैनल URL</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-blue-500">
                            <i class="fa-brands fa-telegram text-lg"></i>
                        </span>
                        <input type="url" name="telegram_url" value="{{ $settings['telegram_url'] }}" placeholder="https://t.me/..." class="w-full pl-10 pr-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Support & Contact Info -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-headset text-indigo-600"></i>
                <span>सहायता व संपर्क जानकारी (Help & Support)</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">सपोर्ट ईमेल (Support Email)</label>
                    <input type="email" name="support_email" value="{{ $settings['support_email'] }}" placeholder="support@cgjobs.info" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">हेल्पलाइन फोन नंबर (Phone)</label>
                    <input type="text" name="support_phone" value="{{ $settings['support_phone'] }}" placeholder="+91 771 2432100" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
            </div>
        </div>

        <!-- 4. App Maintenance & Versioning -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200/80 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-wrench text-rose-600"></i>
                        <span>रखरखाव मोड (Maintenance Mode)</span>
                    </h3>
                    <p class="text-xs text-slate-500">चालू करने पर उपयोगकर्ताओं को ऐप में रखरखाव संदेश दिखाई देगा</p>
                </div>
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] === '1' ? 'checked' : '' }} class="w-4 h-4 text-rose-600 rounded border-slate-300">
                    <span class="text-xs font-bold text-rose-700">रखरखाव मोड चालू करें</span>
                </label>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">रखरखाव संदेश (Maintenance Notice)</label>
                <input type="text" name="maintenance_message" value="{{ $settings['maintenance_message'] }}" placeholder="सर्वर रखरखाव कार्य प्रगति पर है। कृपया थोड़ी देर बाद देखें।" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300">
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                <i class="fa-solid fa-check mr-2"></i> सभी सेटिंग्स सहेजें (Save Settings)
            </button>
        </div>
    </form>
</div>
@endsection
