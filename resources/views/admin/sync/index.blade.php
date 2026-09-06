@extends('layouts.admin')

@section('title', 'ऑटो न्यूज़ सिंक (Automated News Scraper)')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div>
        <h2 class="text-xl font-bold text-slate-900">ऑटो न्यूज़ सिंक व स्क्रैपर (NewsAPI / NewsData.io)</h2>
        <p class="text-xs text-slate-500 mt-0.5">वेब से छत्तीसगढ़ रोजगार, परीक्षा एवं करंट अफेयर्स समाचारों को स्वतः आयात करें</p>
    </div>

    <!-- Sync Trigger Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        
        <div class="flex items-center space-x-3 p-4 bg-teal-50 border border-teal-200 rounded-2xl">
            <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-lg flex-shrink-0">
                <i class="fa-solid fa-robot"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-teal-900">स्मार्ट ऑटो-कैटेगरी डिटेक्शन</h4>
                <p class="text-[11px] text-teal-700">स्क्रैपर लेखों के शीर्षक पढ़कर स्वतः 'CG Police', 'CG Vyapam', 'CGPSC' अथवा 'CG Education' श्रेणियों में वर्गीकृत करता है और डुप्लीकेट लेखों को छोड़ देता है।</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.sync.run') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    खोज कीवर्ड (Search Query for Scraper)
                </label>
                <input type="text" name="query" value="Chhattisgarh recruitment jobs" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none">
                <p class="text-[11px] text-slate-400 mt-1">सुझाव: <code>Chhattisgarh recruitment</code>, <code>CG Vyapam vacancy</code>, <code>CG Police Constable</code></p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3 bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm rounded-xl shadow-md transition transform active:scale-95 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>समाचार आयात प्रारंभ करें (Fetch & Import Now)</span>
                </button>
            </div>
        </form>

        <div class="border-t border-slate-100 pt-5">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">API कुंजी कॉन्फ़िगरेशन (.env)</h4>
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 font-mono text-xs text-slate-700 space-y-1">
                <div>NEWSDATA_KEY=your_newsdata_io_api_key</div>
                <div>NEWSAPI_KEY=your_newsapi_org_api_key</div>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">
                निःशुल्क API Key प्राप्त करने हेतु <a href="https://newsdata.io" target="_blank" class="text-teal-600 underline">newsdata.io</a> या <a href="https://newsapi.org" target="_blank" class="text-teal-600 underline">newsapi.org</a> पर रजिस्टर करें।
            </p>
        </div>

    </div>

</div>
@endsection
