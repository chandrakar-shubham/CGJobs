<!DOCTYPE html>
<html lang="hi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'छत्तीसगढ़ समसामयिकी व करंट अफेयर्स | CGJobs')</title>
    <meta name="description" content="@yield('meta_description', 'CGPSC, CG Vyapam/CGSSB, SSC एवं प्रतियोगी परीक्षाओं हेतु दैनिक करंट अफेयर्स व समसामयिकी')">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        saffron: {
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Inter & Rozha / Tiro Devanagari Hindi -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Tiro+Devanagari+Hindi:ital@0;1&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .hindi-font {
            font-family: 'Tiro Devanagari Hindi', serif;
        }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    <!-- Header / Navbar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 backdrop-blur-md bg-white/90">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <a href="{{ url('/') }}" class="flex items-center space-x-2.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 via-brand-600 to-saffron-500 flex items-center justify-center text-white font-black text-lg shadow-md">
                        CG
                    </div>
                    <div>
                        <span class="font-extrabold text-lg tracking-tight text-slate-900 block leading-tight">CG<span class="text-brand-600">Jobs</span> Portal</span>
                        <span class="text-[10px] font-semibold text-slate-500 tracking-wider uppercase block">Current Affairs & Jobs</span>
                    </div>
                </a>
            </div>

            <nav class="hidden md:flex items-center space-x-6 text-sm font-semibold">
                <a href="{{ url('/') }}" class="text-slate-600 hover:text-brand-600 transition">
                    <i class="fa-solid fa-house-chimney mr-1.5"></i> होम
                </a>
                <a href="{{ url('/jobs') }}" class="text-slate-600 hover:text-brand-600 transition">
                    <i class="fa-solid fa-briefcase mr-1.5"></i> सरकारी नौकरियां
                </a>
                <a href="{{ url('/current-affairs') }}" class="text-brand-600 border-b-2 border-brand-600 pb-1">
                    <i class="fa-solid fa-newspaper mr-1.5"></i> समसामयिकी (Current Affairs)
                </a>
                <a href="{{ url('/gk') }}" class="text-slate-600 hover:text-brand-600 transition">
                    <i class="fa-solid fa-book-open mr-1.5"></i> Static GK
                </a>
                <a href="{{ url('/notifications') }}" class="text-slate-600 hover:text-brand-600 transition">
                    <i class="fa-solid fa-bell mr-1.5 text-amber-500"></i> सूचनाएं
                </a>
            </nav>

            <div class="flex items-center space-x-2">
                <a href="{{ url('/current-affairs') }}" class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-xl bg-brand-50 text-brand-700 text-xs font-bold border border-brand-200 hover:bg-brand-100 transition">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span class="hidden sm:inline">CGPSC / Vyapam Exam Prep</span>
                    <span class="sm:hidden">Exam Prep</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <!-- Footer with Clear Source Citations & Policy Notice -->
    <footer class="bg-slate-900 text-slate-400 py-10 border-t border-slate-800 text-xs mt-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="space-y-3">
                <div class="flex items-center space-x-2">
                    <div class="w-7 h-7 rounded-lg bg-brand-500 text-white flex items-center justify-center font-bold text-sm">CG</div>
                    <span class="text-white font-bold text-base">CG Jobs & Current Affairs</span>
                </div>
                <p class="text-slate-400 leading-relaxed">
                    छत्तीसगढ़ लोक सेवा आयोग (CGPSC), व्यापम (CGSSB), पुलिस भर्ती एवं केंद्रीय प्रतियोगी परीक्षाओं के अभ्यर्थियों हेतु समर्पित शैक्षिक समसामयिकी मंच।
                </p>
            </div>

            <div class="space-y-2">
                <h4 class="text-white font-bold text-sm uppercase tracking-wider">स्रोत साभार (News Sources & Attribution)</h4>
                <p class="leading-relaxed">
                    समस्त समसामयिकी लेख आधिकारिक शासकीय विज्ञप्तियों (जैसे जनसंपर्क विभाग छत्तीसगढ़ DPRCG, PIB India) एवं प्रतिष्ठित राष्ट्रीय समाचार पत्रों से संदर्भ हेतु उद्धृत किए जाते हैं।
                </p>
                <p class="text-[11px] text-slate-500">
                    प्रत्येक लेख के अंत में मूल समाचार पोर्टल का सीधा लिंक (Source Citation) सम्मिलित है।
                </p>
            </div>

            <div class="space-y-2 md:text-right">
                <h4 class="text-white font-bold text-sm uppercase tracking-wider">नेविगेशन लिंक</h4>
                <div class="flex flex-col md:items-end space-y-1.5">
                    <a href="{{ url('/current-affairs') }}" class="hover:text-white transition">दैनिक समसामयिकी (Current Affairs)</a>
                    <a href="{{ url('/admin/login') }}" class="hover:text-white transition">एडमिन पोर्टल (Admin Login)</a>
                    <a href="{{ url('/api/health') }}" target="_blank" class="text-emerald-400 hover:underline">REST API Status</a>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 pt-6 border-t border-slate-800 text-center text-[11px] text-slate-500">
            © {{ date('Y') }} CGJobs Portal. All Rights Reserved. शैक्षणिक एवं प्रतियोगी परीक्षा संदर्भ उद्देश्य हेतु।
        </div>
    </footer>

</body>
</html>
