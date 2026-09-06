<!DOCTYPE html>
<html lang="hi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CGJobs Admin Portal') | छत्तीसगढ़ रोजगार व परीक्षा पोर्टल</title>
    
    <!-- Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef6ff',
                            100: '#d9ebff',
                            500: '#1e88e5',
                            600: '#1565c0',
                            700: '#0d47a1',
                            900: '#0a2d6c',
                        },
                        saffron: {
                            500: '#ff8f00',
                            600: '#e65100',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'Noto Sans Devanagari', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', 'Noto Sans Devanagari', sans-serif; }
    </style>
</head>
<body class="h-full flex flex-col bg-slate-100 text-slate-800">

    <!-- Top Navigation Bar -->
    <header class="bg-brand-700 text-white shadow-md z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center border border-white/20">
                    <i class="fa-solid fa-briefcase text-saffron-500 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight">CGJobs Admin</h1>
                    <p class="text-xs text-blue-200">छत्तीसगढ़ रोजगार व सूचना प्रबंधन पोर्टल (Laravel 11)</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ url('/api/health') }}" target="_blank" class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-200 border border-emerald-500/40">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                    API Active: /api/health
                </a>
                <div class="border-l border-white/20 pl-4 flex items-center space-x-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 rounded-full bg-saffron-500 flex items-center justify-center text-white font-bold text-sm shadow">
                            A
                        </div>
                        <span class="text-sm font-medium hidden md:inline">{{ session('admin_user', 'Admin') }}</span>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-500/90 hover:bg-rose-600 text-white transition shadow-sm" title="लॉगआउट करें">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>लॉगआउट (Logout)</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
        <!-- Sidebar Navigation -->
        <aside class="w-64 flex-shrink-0 hidden md:block">
            <nav class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-3 space-y-1.5 sticky top-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-gauge-high w-5 text-center text-brand-600"></i>
                    <span>डैशबोर्ड (Dashboard)</span>
                </a>

                <div class="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">भर्ती व समाचार (Content)</div>
                
                <a href="{{ route('admin.jobs.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.jobs.index') && !request('section') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-briefcase w-5 text-center text-brand-600"></i>
                    <span>सरकारी भर्तियां (Jobs)</span>
                </a>
                <a href="{{ route('admin.jobs.index', ['section' => 'news']) }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request('section') == 'news' ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-newspaper w-5 text-center text-blue-600"></i>
                    <span>समाचार व समसामयिकी</span>
                </a>
                <a href="{{ route('admin.jobs.create') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.jobs.create') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-circle-plus w-5 text-center text-emerald-600"></i>
                    <span>नई पोस्ट जोड़ें (Add Post)</span>
                </a>

                <div class="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">सामान्य ज्ञान व अध्ययन (Study)</div>

                <a href="{{ route('admin.static-gk.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.static-gk.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-book-open w-5 text-center text-indigo-600"></i>
                    <span>Static GK प्रबंधन</span>
                </a>

                <div class="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">अलर्ट्स व सूचना (Alerts)</div>

                <a href="{{ route('admin.alerts.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.alerts.index') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-bell w-5 text-center text-amber-500"></i>
                    <span>अलर्ट्स व सूचनाएं (Alerts)</span>
                </a>
                <a href="{{ route('admin.alerts.create') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.alerts.create') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-paper-plane w-5 text-center text-saffron-600"></i>
                    <span>पुश नोटिफिकेशन (FCM)</span>
                </a>

                <div class="pt-3 pb-1 px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider">सिस्टम व ऐप प्रबंधन</div>

                <a href="{{ route('admin.sections.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.sections.*') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-layer-group w-5 text-center text-purple-600"></i>
                    <span>ऐप सेक्शंस (Sections)</span>
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.categories.*') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-tags w-5 text-center text-teal-600"></i>
                    <span>श्रेणियां (Categories)</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-sliders w-5 text-center text-amber-500"></i>
                    <span>एप सेटिंग्स व लाइव टिकर</span>
                </a>
                <a href="{{ route('admin.sync.index') }}" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.sync.index') ? 'bg-brand-50 text-brand-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                    <i class="fa-solid fa-cloud-arrow-down w-5 text-center text-cyan-600"></i>
                    <span>ऑटो न्यूज़ सिंक (API)</span>
                </a>

                <form action="{{ route('admin.logout') }}" method="POST" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50 transition text-left">
                        <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center text-rose-500"></i>
                        <span>लॉगआउट (Logout)</span>
                    </button>
                </form>

                <div class="pt-4 mt-2 border-t border-slate-100 px-3">
                    <div class="bg-blue-50/70 rounded-xl p-3 border border-blue-100">
                        <div class="text-xs font-semibold text-brand-700 flex items-center gap-1.5 mb-1">
                            <i class="fa-solid fa-mobile-screen"></i>
                            <span>Android App Integration</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed">
                            App API URL:
                            <code class="block bg-white mt-1 px-2 py-1 rounded border text-[10px] text-slate-800 break-all select-all font-mono">
                                {{ url('/') }}/
                            </code>
                        </p>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 min-w-0">
            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-2xl flex items-center justify-between shadow-sm animate-fade-in">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if(session('info'))
                <div class="mb-5 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3.5 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-circle-info text-blue-500 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('info') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3.5 rounded-2xl shadow-sm">
                    <div class="flex items-center space-x-2 font-semibold text-sm mb-1">
                        <i class="fa-solid fa-triangle-exclamation text-rose-500"></i>
                        <span>कृपया फॉर्म में त्रुटियों की जांच करें:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-rose-700 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <footer class="bg-white border-t border-slate-200 mt-auto py-4 text-center text-xs text-slate-500">
        CGJobs Portal Backend (Laravel 11 & Jetpack Compose Android Client) &copy; {{ date('Y') }}
    </footer>

</body>
</html>
