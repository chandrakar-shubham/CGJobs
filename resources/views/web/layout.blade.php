<!doctype html>
<html lang="{{ request('lang', session('language', 'hi')) === 'en' ? 'en' : 'hi' }}" class="h-full bg-slate-50 scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0369a1">
    <meta name="robots" content="index,follow">
    @php($lang = request('lang', session('language', 'hi')) === 'en' ? 'en' : 'hi')
    <title>@yield('title', $lang === 'en' ? 'CGJobs — Government Jobs, Exams & Results Portal' : 'CGJobs — छत्तीसगढ़ सरकारी नौकरी, परीक्षा एवं परिणाम पोर्टल')</title>
    <meta name="description" content="{{ $lang === 'en' ? 'Official portal for Chhattisgarh government jobs, CGPSC, CGSSB/Vyapam, daily current affairs, static GK, admit cards and results.' : 'छत्तीसगढ़ सरकारी नौकरी, CGPSC, व्यापम (CGSSB), दैनिक समसामयिकी (Current Affairs), स्टैटिक GK, प्रवेश पत्र एवं परीक्षा परिणाम।' }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        accent: {
                            500: '#f97316',
                            600: '#ea580c',
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', '-apple-system', 'sans-serif'],
                        hindi: ['Tiro Devanagari Hindi', 'serif']
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts: Plus Jakarta Sans & Tiro Devanagari Hindi -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Tiro+Devanagari+Hindi:ital@0;1&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .hindi-font {
            font-family: 'Tiro Devanagari Hindi', serif;
        }
        /* Custom card micro-interactions */
        .job-card-hover {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .job-card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('head')
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased bg-slate-50 selection:bg-brand-600 selection:text-white">

    <!-- Top Notice Bar -->
    <div class="bg-slate-900 text-slate-300 text-xs py-1.5 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400 mr-1.5 animate-ping"></span>
                    {{ $lang === 'en' ? 'LIVE NOTICES' : 'लाइव सूचनाएं' }}
                </span>
                <span class="truncate hidden sm:inline text-slate-400">
                    {{ $lang === 'en' ? 'Chhattisgarh Public Service Commission, CGSSB & State Recruitment Alerts' : 'छत्तीसगढ़ लोक सेवा आयोग (CGPSC), व्यापम एवं राज्य भर्ती त्वरित अपडेट्स' }}
                </span>
            </div>
            <div class="flex items-center space-x-4 text-[11px]">
                <a href="{{ route('listing.current-affairs', ['lang' => $lang]) }}" class="hover:text-white transition">
                    <i class="fa-solid fa-bolt text-amber-400 mr-1"></i> {{ $lang === 'en' ? 'Exam Inshorts' : 'परीक्षा कैप्सूल' }}
                </a>
                <span class="text-slate-700">|</span>
                <a href="{{ route('admin.login') }}" class="text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-lock mr-1 text-[10px]"></i> {{ $lang === 'en' ? 'Admin Portal' : 'विभागीय लॉगिन' }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            
            <!-- Brand Logo -->
            <a href="{{ route('home', ['lang' => $lang]) }}" class="flex items-center space-x-3 group flex-shrink-0">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-900 via-brand-700 to-brand-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-brand-700/20 group-hover:scale-105 transition duration-200">
                    CG
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span class="font-black text-2xl tracking-tight text-slate-900 leading-none">CG<span class="text-brand-600">Jobs</span></span>
                        <span class="px-1.5 py-0.5 rounded-md bg-brand-50 text-brand-700 text-[10px] font-black uppercase tracking-wider border border-brand-200">Portal</span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500 tracking-wide block mt-1">
                        {{ $lang === 'en' ? 'Government Jobs & Exam Network' : 'सरकारी नौकरी • परीक्षा • बेहतर भविष्य' }}
                    </span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden lg:flex items-center space-x-1 text-sm font-bold text-slate-650">
                <a href="{{ route('home', ['lang' => $lang]) }}" class="px-3 py-2 rounded-xl text-slate-900 hover:bg-slate-100/80 transition {{ request()->routeIs('home') ? 'bg-brand-50 text-brand-700 font-extrabold' : '' }}">
                    <i class="fa-solid fa-house-chimney mr-1.5 text-xs text-brand-600"></i> {{ $lang === 'en' ? 'Home' : 'होम' }}
                </a>
                <a href="{{ route('listing.jobs', ['lang' => $lang]) }}" class="px-3 py-2 rounded-xl hover:bg-slate-100/80 transition {{ request()->routeIs('listing.jobs') ? 'bg-brand-50 text-brand-700 font-extrabold' : 'text-slate-700' }}">
                    <i class="fa-solid fa-briefcase mr-1.5 text-xs text-emerald-600"></i> {{ $lang === 'en' ? 'All Jobs' : 'सरकारी नौकरियां' }}
                </a>
                <a href="{{ route('listing.current-affairs', ['lang' => $lang]) }}" class="px-3 py-2 rounded-xl hover:bg-slate-100/80 transition {{ request()->routeIs('listing.current-affairs') ? 'bg-brand-50 text-brand-700 font-extrabold' : 'text-slate-700' }}">
                    <i class="fa-solid fa-newspaper mr-1.5 text-xs text-brand-600"></i> {{ $lang === 'en' ? 'Current Affairs' : 'समसामयिकी' }}
                </a>
                <a href="{{ route('listing.gk', ['lang' => $lang]) }}" class="px-3 py-2 rounded-xl hover:bg-slate-100/80 transition {{ request()->routeIs('listing.gk') ? 'bg-brand-50 text-brand-700 font-extrabold' : 'text-slate-700' }}">
                    <i class="fa-solid fa-book-open mr-1.5 text-xs text-purple-600"></i> Static GK
                </a>
                <a href="{{ route('notifications', ['lang' => $lang]) }}" class="px-3 py-2 rounded-xl hover:bg-slate-100/80 transition text-slate-700">
                    <i class="fa-solid fa-bell mr-1.5 text-xs text-amber-500"></i> {{ $lang === 'en' ? 'Alerts' : 'अलर्ट्स' }}
                </a>
            </nav>

            <!-- Right Utilities: Language Segmented Button & App Download -->
            <div class="flex items-center space-x-3">
                <!-- Language Toggle -->
                <div class="inline-flex p-1 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold">
                    <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['lang' => 'hi'])) }}" class="px-2.5 py-1 rounded-lg transition {{ $lang === 'hi' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                        हिंदी
                    </a>
                    <a href="{{ url()->current() }}?{{ http_build_query(array_merge(request()->query(), ['lang' => 'en'])) }}" class="px-2.5 py-1 rounded-lg transition {{ $lang === 'en' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900' }}">
                        EN
                    </a>
                </div>

                <!-- Android App CTA Button -->
                <a href="https://play.google.com/store" target="_blank" rel="noopener" class="hidden sm:inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-brand-700 to-brand-600 hover:from-brand-800 hover:to-brand-700 text-white text-xs font-extrabold shadow-md shadow-brand-700/25 transition">
                    <i class="fa-brands fa-android text-base text-emerald-300"></i>
                    <span>{{ $lang === 'en' ? 'Get Android App' : 'ऐप डाउनलोड करें' }}</span>
                </a>
            </div>
        </div>

        <!-- Mobile Horizontal Tab Bar -->
        <div class="lg:hidden border-t border-slate-100 px-4 py-2 overflow-x-auto flex items-center space-x-2 scrollbar-none text-xs font-bold text-slate-700">
            <a href="{{ route('home', ['lang' => $lang]) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('home') ? 'bg-brand-50 text-brand-700' : 'bg-slate-100' }}">
                <i class="fa-solid fa-house-chimney mr-1 text-[11px]"></i> होम
            </a>
            <a href="{{ route('listing.jobs', ['lang' => $lang]) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('listing.jobs') ? 'bg-brand-50 text-brand-700' : 'bg-slate-100' }}">
                <i class="fa-solid fa-briefcase mr-1 text-[11px]"></i> नौकरियां
            </a>
            <a href="{{ route('listing.current-affairs', ['lang' => $lang]) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('listing.current-affairs') ? 'bg-brand-50 text-brand-700' : 'bg-slate-100' }}">
                <i class="fa-solid fa-newspaper mr-1 text-[11px]"></i> समसामयिकी
            </a>
            <a href="{{ route('listing.gk', ['lang' => $lang]) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('listing.gk') ? 'bg-brand-50 text-brand-700' : 'bg-slate-100' }}">
                <i class="fa-solid fa-book-open mr-1 text-[11px]"></i> GK
            </a>
            <a href="{{ route('notifications', ['lang' => $lang]) }}" class="px-3 py-1.5 rounded-lg whitespace-nowrap bg-slate-100">
                <i class="fa-solid fa-bell mr-1 text-[11px]"></i> अलर्ट्स
            </a>
        </div>
    </header>

    <!-- Content Area -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Comprehensive Modern Footer -->
    <footer class="bg-slate-900 text-slate-300 pt-16 pb-12 border-t border-slate-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
                
                <!-- Brand & Mission Column -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-brand-400 flex items-center justify-center text-white font-black text-lg shadow-md">
                            CG
                        </div>
                        <span class="font-black text-2xl text-white tracking-tight">CG<span class="text-brand-400">Jobs</span></span>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                        {{ $lang === 'en' ? 'The trusted digital gateway for Chhattisgarh youth and aspirants. Up-to-date government recruitment notifications, examinations, daily exam-focused current affairs, and comprehensive static GK.' : 'छत्तीसगढ़ के युवाओं एवं प्रतियोगी परीक्षार्थियों हेतु समर्पित सूचना तंत्र। शासकीय भर्ती विज्ञप्तियां, परीक्षा तिथियां, समसामयिकी और सामान्य ज्ञान एक ही स्थान पर।' }}
                    </p>
                    <div class="pt-2 flex items-center space-x-3">
                        <a href="https://play.google.com/store" class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-bold transition">
                            <i class="fa-brands fa-google-play text-emerald-400"></i>
                            <span>Google Play</span>
                        </a>
                        <a href="{{ route('notifications', ['lang' => $lang]) }}" class="inline-flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-bold transition">
                            <i class="fa-solid fa-bell text-amber-400"></i>
                            <span>{{ $lang === 'en' ? 'Push Alerts' : 'पुश अलर्ट्स' }}</span>
                        </a>
                    </div>
                </div>

                <!-- Core Categories -->
                <div class="space-y-3">
                    <h4 class="text-white font-extrabold text-sm uppercase tracking-wider">{{ $lang === 'en' ? 'Job Categories' : 'प्रमुख श्रेणियां' }}</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('listing.jobs', ['lang' => $lang, 'job_category' => 'CGSSB']) }}" class="hover:text-brand-400 transition">CGSSB (व्यापम)</a></li>
                        <li><a href="{{ route('listing.jobs', ['lang' => $lang, 'job_category' => 'CGPSC']) }}" class="hover:text-brand-400 transition">CGPSC राज्य सेवा</a></li>
                        <li><a href="{{ route('listing.jobs', ['lang' => $lang, 'job_category' => 'Central Govt']) }}" class="hover:text-brand-400 transition">Central Govt (SSC/Rly)</a></li>
                        <li><a href="{{ route('listing.jobs', ['lang' => $lang, 'job_category' => 'Contractual']) }}" class="hover:text-brand-400 transition">संविदा एवं अस्थायी भर्ती</a></li>
                    </ul>
                </div>

                <!-- Exam Preparation -->
                <div class="space-y-3">
                    <h4 class="text-white font-extrabold text-sm uppercase tracking-wider">{{ $lang === 'en' ? 'Preparation' : 'परीक्षा तैयारी' }}</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('listing.current-affairs', ['lang' => $lang]) }}" class="hover:text-brand-400 transition">दैनिक समसामयिकी (News)</a></li>
                        <li><a href="{{ route('listing.gk', ['lang' => $lang]) }}" class="hover:text-brand-400 transition">छत्तीसगढ़ सामान्य ज्ञान (GK)</a></li>
                        <li><a href="{{ route('listing.jobs', ['lang' => $lang, 'sort' => 'closing']) }}" class="hover:text-brand-400 transition">शीघ्र बंद हो रही नौकरियां</a></li>
                        <li><a href="{{ route('notifications', ['lang' => $lang]) }}" class="hover:text-brand-400 transition">परीक्षा सूचनाएं (Alerts)</a></li>
                    </ul>
                </div>

                <!-- Official Sources & Legal -->
                <div class="space-y-3">
                    <h4 class="text-white font-extrabold text-sm uppercase tracking-wider">{{ $lang === 'en' ? 'Attribution & Portals' : 'आधिकारिक स्रोत व साभार' }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        विज्ञप्तियां आधिकारिक शासकीय वेबसाइटों (cgstate.gov.in, psc.cg.gov.in, vyapam.cgstate.gov.in) एवं DPRCG जनसंपर्क से साभार ली जाती हैं।
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('admin.login') }}" class="text-xs text-slate-500 hover:text-slate-300 transition block">
                            <i class="fa-solid fa-user-shield mr-1"></i> एडमिन लॉगिन
                        </a>
                    </div>
                </div>

            </div>

            <!-- Bottom Copyright & Disclaimer -->
            <div class="mt-12 pt-8 border-t border-slate-800 text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>© {{ date('Y') }} CGJobs Network. शैक्षणिक एवं प्रतियोगी परीक्षा सूचना संदर्भ हेतु।</p>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('sitemap') }}" class="hover:text-slate-400 transition">Sitemap</a>
                    <span>•</span>
                    <a href="{{ route('robots') }}" class="hover:text-slate-400 transition">Robots.txt</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
