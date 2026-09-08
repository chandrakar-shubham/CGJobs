@extends('layouts.admin')

@section('title', 'ऑटो न्यूज़ व करंट अफेयर्स स्क्रैपर (Exam News Sync)')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h2 class="text-xl font-bold text-slate-900">ऑटो करंट अफेयर्स व परीक्षा समाचार स्क्रैपर</h2>
        <p class="text-xs text-slate-500 mt-0.5">DPRCG, PIB India एवं प्रामाणिक समाचार माध्यमों से CGPSC व व्यापम उपयोगी लेख स्वतः आयात करें</p>
    </div>

    <!-- Live Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center space-x-3 text-emerald-700">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">परीक्षा दृष्टि (Exam Takeaways)</h4>
                    <p class="text-[11px] text-slate-500">हर लेख के साथ 60-शब्द त्वरित कैप्सूल एवं आयोग प्रश्नों के मुख्य बिंदु स्वतः निर्मित होते हैं</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center space-x-3 text-purple-700">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="text-xs font-bold text-slate-900">Gemini 3.5 Flash</h4>
                        @if($geminiConfigured)
                            <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">सक्रिय (Active)</span>
                        @else
                            <span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">नियम-आधारित (Fallback)</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500">स्मार्ट परीक्षा प्रासंगिकता, इनशॉर्ट्स कैप्सूल व मुख्य बिंदु निर्माण</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center space-x-3 text-brand-700">
                <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-link"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-900">स्रोत साभार (Source Citation)</h4>
                    <p class="text-[11px] text-slate-500">प्रत्येक लेख में मूल प्रकाशक (DPRCG, PIB, भास्कर) का सत्यापित लिंक और वेबसाइट पेज लिंक शामिल रहता है</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gemini Configuration Card -->
    <div class="bg-gradient-to-br from-purple-50 via-white to-indigo-50/40 rounded-3xl border border-purple-200/70 p-6 sm:p-7 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center text-sm shadow">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Google Gemini API कॉन्फ़िगरेशन (Exam AI Engine)</h3>
                    <p class="text-[11px] text-slate-500">मॉडल: <span class="font-mono font-bold text-purple-700">{{ $geminiModel }}</span></p>
                </div>
            </div>
            @if($geminiConfigured)
                <div class="text-[11px] text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 flex items-center gap-1.5 font-medium">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>API कुंजी सक्रिय (Key: {{ $maskedKey }})</span>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.sync.gemini-key') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Gemini API Key (Google AI Studio)
                </label>
                <div class="flex gap-2">
                    <input type="password" name="gemini_api_key" placeholder="AIzaSy..." value="{{ $maskedKey ? '' : '' }}" class="flex-1 px-3.5 py-2 text-sm border border-purple-200 rounded-xl bg-white focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition">
                        कुंजी सहेजें (Save Key)
                    </button>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Google AI Studio (<a href="https://aistudio.google.com" target="_blank" class="text-purple-600 underline">aistudio.google.com</a>) से निशुल्क API कुंजी प्राप्त कर यहां सहेजें। खाली छोड़ कर सहेजने पर की हट जाएगी।
                </p>
            </div>
        </form>
    </div>

    <!-- Sync Trigger Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-6">
        
        <form method="POST" action="{{ route('admin.sync.run') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="sync_type" value="exam_news">

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    फोकस कीवर्ड (वैकल्पिक)
                </label>
                <input type="text" name="query" value="Chhattisgarh government schemes CGPSC" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <p class="text-[11px] text-slate-400 mt-1">डिफ़ॉल्ट रूप से यह जनसंपर्क विभाग (DPRCG) और PIB India से नवीनतम अधिसूचनाएं प्राप्त करता है।</p>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition transform active:scale-95 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-cloud-arrow-down"></i>
                    <span>दैनिक करंट अफेयर्स आयात करें (Fetch & Import Exam News)</span>
                </button>
            </div>
        </form>

        <div class="border-t border-slate-100 pt-5 space-y-3">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">सक्रिय स्रोत फ़ीड (Configured News Sources)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-800">DPRCG Chhattisgarh</div>
                        <div class="text-[10px] text-slate-500">जनसंपर्क विभाग छत्तीसगढ़</div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">सक्रिय</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-800">PIB India (Hindi)</div>
                        <div class="text-[10px] text-slate-500">पत्र सूचना कार्यालय, भारत सरकार</div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">सक्रिय</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-800">दैनिक भास्कर (रायपुर/बस्तर)</div>
                        <div class="text-[10px] text-slate-500">राज्य स्तरीय समसामयिकी</div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold">सक्रिय</span>
                </div>
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="font-bold text-slate-800">NewsData.io API (वैकल्पिक)</div>
                        <div class="text-[10px] text-slate-500">व्यापक राष्ट्रीय समाचार संग्रह</div>
                    </div>
                    <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-[10px] font-bold">बैकअप</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Interactive Gemini AI Sandbox / Live Test -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-5">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-base font-bold">
                <i class="fa-solid fa-flask"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Gemini 3.5 Flash लाइव टेस्ट लैब (Live Testing Sandbox)</h3>
                <p class="text-[11px] text-slate-500">किसी भी समाचार को यहां पेस्ट करके देखें कि Gemini AI किस तरह 60-शब्द कैप्सूल व परीक्षा उपयोगी तथ्य बनाता है</p>
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">समाचार शीर्षक (News Title)</label>
                <input type="text" id="test_title" placeholder="उदा: छत्तीसगढ़ में 5 नवीन औद्योगिक पार्कों की स्थापना को कैबिनेट की हरी झंडी" class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">विस्तृत समाचार लेख / कच्चा पाठ (Raw News Text)</label>
                <textarea id="test_content" rows="4" placeholder="रायपुर में मुख्यमंत्री की अध्यक्षता में आयोजित कैबिनेट बैठक में राज्य के युवाओं के लिए रोजगार सृजन व स्थानीय उत्पादों के मूल्य संवर्धन हेतु 5 नवीन पार्कों के निर्माण को स्वीकृति प्रदान की गई..." class="w-full px-3.5 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
            </div>

            <button type="button" onclick="runGeminiTest()" id="test_btn" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Gemini 3.5 Flash से प्रोसेस करें (Generate Preview)</span>
            </button>
        </div>

        <!-- Result Box -->
        <div id="test_result" class="hidden border border-purple-200 bg-purple-50/40 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-purple-100 pb-3">
                <span class="text-xs font-bold text-purple-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Gemini AI आउटपुट परिणाम
                </span>
                <span id="res_badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold"></span>
            </div>

            <div class="space-y-3">
                <div>
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">60-शब्द इनशॉर्ट्स कैप्सूल (Inshorts Summary):</span>
                    <p id="res_summary" class="text-xs text-slate-800 bg-white p-3 rounded-xl border border-slate-200 mt-1 leading-relaxed font-medium"></p>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1">
                        <i class="fa-solid fa-graduation-cap"></i> परीक्षा दृष्टि (Exam Takeaways):
                    </span>
                    <pre id="res_takeaway" class="text-xs text-amber-950 bg-amber-50 p-3 rounded-xl border border-amber-200 mt-1 whitespace-pre-wrap font-sans leading-relaxed"></pre>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">वर्गीकृत श्रेणी (Category):</span>
                    <span id="res_category" class="ml-2 inline-block px-2.5 py-1 rounded-lg bg-indigo-100 text-indigo-800 text-xs font-bold"></span>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function runGeminiTest() {
    const title = document.getElementById('test_title').value.trim();
    const content = document.getElementById('test_content').value.trim();
    const btn = document.getElementById('test_btn');
    const resultBox = document.getElementById('test_result');

    if (!title || !content) {
        alert('कृपया परीक्षण हेतु शीर्षक और कच्चा समाचार पाठ दोनों दर्ज करें।');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Gemini विश्लेषण कर रहा है...</span>';

    fetch("{{ route('admin.sync.gemini-preview') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            title: title,
            content: content,
            source: 'परीक्षण पोर्टल'
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> <span>Gemini 3.5 Flash से प्रोसेस करें</span>';
        resultBox.classList.remove('hidden');

        if (data.success && data.data) {
            const d = data.data;
            document.getElementById('res_badge').className = d.is_exam_relevant 
                ? 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800'
                : 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800';
            document.getElementById('res_badge').innerText = d.is_exam_relevant ? '✓ परीक्षा उपयोगी (Relevant)' : '✗ गैर-परीक्षा समाचार (Discarded)';

            document.getElementById('res_summary').innerText = d.inshorts_summary || 'उपलब्ध नहीं';
            document.getElementById('res_takeaway').innerText = d.exam_takeaway || 'उपलब्ध नहीं';
            document.getElementById('res_category').innerText = d.category || 'सामान्य';
        } else {
            alert(data.message || 'Gemini प्रोसेसिंग में त्रुटि हुई। कृपया API Key की जांच करें।');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> <span>Gemini 3.5 Flash से प्रोसेस करें</span>';
        alert('नेटवर्क या सर्वर त्रुटि: ' + err.message);
    });
}
</script>
@endsection
