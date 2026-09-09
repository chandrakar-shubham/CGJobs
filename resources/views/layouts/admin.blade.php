<!DOCTYPE html>
<html lang="hi" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') | CGJobs Control Center</title>

    <!-- Google Fonts: Inter & Noto Sans Devanagari -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Noto Sans Devanagari', 'system-ui', 'sans-serif'],
                    },
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
                        dark: {
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', 'Noto Sans Devanagari', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Custom Modern Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.05);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.25);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.45);
        }

        #admin-sidebar {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #admin-backdrop {
            transition: opacity 0.2s ease-in-out;
        }

        @media (min-width: 1024px) {
            #admin-sidebar.admin-collapsed {
                width: 0;
                overflow: hidden;
                transform: translateX(-100%);
            }
        }

        @media (max-width: 1023px) {
            #admin-sidebar {
                position: fixed;
                z-index: 50;
                left: 0;
                top: 0;
                bottom: 0;
                width: 18rem;
                max-width: 85vw;
                transform: translateX(-100%);
            }
            #admin-sidebar.admin-open {
                transform: translateX(0);
            }
            #admin-backdrop.admin-open {
                display: block;
                opacity: 1;
            }
        }
    </style>
</head>
<body class="h-full flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-brand-500 selection:text-white">

    @php
    $safeRoute = function(string $name, array $params = [], string $fallback = '#'): string {
        return \Illuminate\Support\Facades\Route::has($name) ? route($name, $params) : (str_starts_with($fallback, '/') ? url($fallback) : $fallback);
    };

    $jobLinks = [
        ['admin.jobs.dashboard', 'Jobs Analytics', 'fa-chart-pie'],
        ['admin.jobs.index', 'All Jobs Directory', 'fa-briefcase'],
        ['admin.jobs.create', 'Create New Job', 'fa-circle-plus'],
        ['admin.jobs.templates', 'Job Templates', 'fa-file-lines'],
        ['admin.jobs.categories', 'Categories & Depts', 'fa-sitemap'],
        ['admin.jobs.notifications', 'Notification Center', 'fa-bell'],
        ['admin.jobs.import-sync', 'Import / Sync Hub', 'fa-cloud-arrow-down'],
        ['admin.jobs.quality', 'Quality Checker', 'fa-circle-check'],
        ['admin.jobs.duplicates', 'Duplicate Detection', 'fa-copy'],
    ];

    $newsLinks = [
        ['admin.news.dashboard', 'News Overview', 'fa-chart-pie'],
        ['admin.news.index', 'All News / Inbox', 'fa-newspaper'],
        ['admin.news.index', 'AI Processed News', 'fa-robot', 'processed'],
        ['admin.news.index', 'Published Archive', 'fa-circle-check', 'all'],
        ['admin.news.create', 'Create News Post', 'fa-circle-plus'],
    ];
    @endphp

    <!-- Backdrop for Mobile Sidebar -->
    <div id="admin-backdrop" class="hidden fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs opacity-0 lg:hidden" aria-hidden="true"></div>

    <div class="min-h-screen flex flex-col">
        
        <div class="flex-1 flex w-full">

            <!-- Sleek Modern Dark Sidebar -->
            <aside id="admin-sidebar" class="w-72 shrink-0 bg-slate-900 text-slate-300 flex flex-col border-r border-slate-800 shadow-2xl z-40">
                
                <!-- Brand Header -->
                <div class="h-16 px-5 flex items-center justify-between border-b border-slate-800/80 bg-slate-950/40">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-600 to-brand-500 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-brand-500/25 group-hover:scale-105 transition">
                            <i class="fa-solid fa-briefcase text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="text-base font-extrabold text-white tracking-tight leading-none">CGJobs</span>
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-brand-500/20 text-brand-300 border border-brand-500/30">Admin</span>
                            </div>
                            <span class="text-[11px] font-medium text-slate-400 block truncate mt-0.5">Control Center Pro</span>
                        </div>
                    </a>

                    <!-- Close button on mobile -->
                    <button id="admin-sidebar-close" type="button" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Live System Indicator -->
                <div class="px-5 py-2.5 bg-slate-950/20 border-b border-slate-800/60 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[11px] font-semibold text-slate-300">Live Server Online</span>
                    </div>
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="text-[11px] font-bold text-brand-400 hover:text-brand-300 flex items-center gap-1 transition">
                        <span>Web Portal</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                    </a>
                </div>

                <!-- Navigation Links Container -->
                <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-5 text-sm">

                    <!-- Section: Overview -->
                    <div class="space-y-1">
                        <div class="px-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            Overview
                        </div>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <i class="fa-solid fa-gauge-high w-5 text-center text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-brand-400' }}"></i>
                            <span>डैशबोर्ड (Dashboard)</span>
                        </a>
                        <a href="{{ $safeRoute('admin.jobs.analytics', [], '/admin/jobs') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.jobs.analytics') ? 'bg-slate-800 text-brand-300' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                            <i class="fa-solid fa-chart-line w-5 text-center text-xs text-indigo-400"></i>
                            <span>उन्नत आंकड़े (Analytics)</span>
                        </a>
                    </div>

                    <!-- Section: Job Recruitment Management -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between px-3 pb-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Recruitment</span>
                            <a href="{{ route('admin.jobs.create') }}" class="text-[10px] font-bold text-brand-400 hover:text-white" title="Quick Add Job">+ Add</a>
                        </div>

                        <a href="{{ $safeRoute('admin.jobs.index', ['section'=>'jobs'], '/admin/jobs') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('admin.jobs.index') && !request()->query('workflow_status') && !request()->query('deadline') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-briefcase w-5 text-center text-sm {{ request()->routeIs('admin.jobs.index') && !request()->query('workflow_status') ? 'text-white' : 'text-emerald-400' }}"></i>
                                <span>सभी भर्तियां (All Jobs)</span>
                            </div>
                        </a>

                        <a href="{{ route('admin.jobs.create') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.jobs.create') ? 'bg-slate-800 text-brand-300' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                            <i class="fa-solid fa-circle-plus w-5 text-center text-xs text-emerald-400"></i>
                            <span>+ नई भर्ती पोस्ट करें</span>
                        </a>

                        <a href="{{ route('admin.jobs.scraper-console') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.jobs.scraper-console') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-cloud-arrow-down w-5 text-center text-xs text-amber-400"></i>
                                <span>भर्ती स्क्रैपर (Scraper Console)</span>
                            </div>
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300">Smart AI</span>
                        </a>

                        <!-- Workflow Sub-filters -->
                        <div class="pl-4 pr-1 py-1 space-y-0.5 border-l border-slate-800 ml-5">
                            <a href="{{ $safeRoute('admin.jobs.index', ['section'=>'jobs', 'workflow_status'=>'draft'], '/admin/jobs?workflow_status=draft') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs {{ request()->query('workflow_status')==='draft' ? 'bg-amber-500/20 text-amber-300 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-file-pen text-[10px] text-amber-400"></i> ड्राफ्ट्स (Drafts)</span>
                            </a>
                            <a href="{{ $safeRoute('admin.jobs.index', ['section'=>'jobs', 'workflow_status'=>'scheduled'], '/admin/jobs?workflow_status=scheduled') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs {{ request()->query('workflow_status')==='scheduled' ? 'bg-purple-500/20 text-purple-300 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-calendar-days text-[10px] text-purple-400"></i> शेड्यूल्ड (Scheduled)</span>
                            </a>
                            <a href="{{ $safeRoute('admin.jobs.index', ['section'=>'jobs', 'deadline'=>'closing'], '/admin/jobs?deadline=closing') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs {{ request()->query('deadline')==='closing' ? 'bg-rose-500/20 text-rose-300 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-fire text-[10px] text-rose-400"></i> अंतिम तिथि निकट</span>
                            </a>
                            <a href="{{ $safeRoute('admin.jobs.index', ['section'=>'jobs', 'deadline'=>'expired'], '/admin/jobs?deadline=expired') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs {{ request()->query('deadline')==='expired' ? 'bg-slate-800 text-slate-200 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                                <span class="flex items-center gap-2"><i class="fa-solid fa-clock-rotate-left text-[10px] text-slate-400"></i> समाप्त (Expired)</span>
                            </a>
                        </div>

                        <a href="{{ $safeRoute('admin.jobs.categories', [], '/admin/categories') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold transition text-slate-400 hover:bg-slate-800/60 hover:text-slate-200">
                            <i class="fa-solid fa-sitemap w-5 text-center text-xs text-teal-400"></i>
                            <span>श्रेणी व विभाग (Categories)</span>
                        </a>
                    </div>

                    <!-- Section: Content & AI Engine -->
                    <div class="space-y-1">
                        <div class="px-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            Content & AI
                        </div>

                        <a href="{{ $safeRoute('admin.ai-engine.index', [], '/admin/ai-engine') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('admin.ai-engine.*') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-robot w-5 text-center text-sm {{ request()->routeIs('admin.ai-engine.*') ? 'text-white' : 'text-purple-400' }}"></i>
                                <span>AI Content Engine</span>
                            </div>
                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-purple-400/20 text-purple-300 uppercase tracking-wide">Gemini</span>
                        </a>

                        <a href="{{ $safeRoute('admin.news.index', [], '/admin/news') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.news.*') ? 'bg-slate-800 text-cyan-300' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                            <i class="fa-solid fa-newspaper w-5 text-center text-xs text-cyan-400"></i>
                            <span>समसामयिकी / समाचार (News)</span>
                        </a>

                        <a href="{{ $safeRoute('admin.static-gk.index', [], '/admin/static-gk') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.static-gk.*') ? 'bg-slate-800 text-indigo-300' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                            <i class="fa-solid fa-book-open w-5 text-center text-xs text-indigo-400"></i>
                            <span>Static GK नोट्स</span>
                        </a>
                    </div>

                    <!-- Section: Automation & Alerts -->
                    <div class="space-y-1">
                        <div class="px-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            Automation & Delivery
                        </div>

                        <a href="{{ $safeRoute('admin.sync.index', [], '/admin/sync') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.sync.*') ? 'bg-slate-800 text-cyan-300' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                            <i class="fa-solid fa-cloud-arrow-down w-5 text-center text-xs text-cyan-400"></i>
                            <span>News Sync / Scraper</span>
                        </a>

                        <a href="{{ $safeRoute('admin.job-sources.index', [], '/admin/job-sources') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.job-sources.*') ? 'bg-slate-800 text-cyan-300' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                            <i class="fa-solid fa-plug w-5 text-center text-xs text-emerald-400"></i>
                            <span>Jobs API Sources</span>
                        </a>

                        <a href="{{ $safeRoute('admin.alerts.index', [], '/admin/alerts') }}" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.alerts.*') ? 'bg-slate-800 text-amber-300' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-bell w-5 text-center text-xs text-amber-400"></i>
                                <span>पुश अलर्ट्स (Push Alerts)</span>
                            </div>
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-400/20 text-amber-300">FCM</span>
                        </a>
                    </div>

                    <!-- Section: System Settings -->
                    <div class="space-y-1">
                        <div class="px-3 pb-1 text-[10px] font-extrabold uppercase tracking-wider text-slate-400">
                            System
                        </div>

                        <a href="{{ $safeRoute('admin.settings.index', [], '/admin/settings') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800/60 hover:text-slate-200' }}">
                            <i class="fa-solid fa-sliders w-5 text-center text-sm text-amber-400"></i>
                            <span>सेटिंग्स व लाइव टिकर</span>
                        </a>
                    </div>

                </nav>

                <!-- Admin Profile Footer in Sidebar -->
                <div class="p-4 border-t border-slate-800 bg-slate-950/50 flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-slate-800 text-white flex items-center justify-center font-bold text-xs border border-slate-700">
                            AD
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-white block truncate">Super Admin</span>
                            <span class="text-[10px] text-slate-400 block truncate">admin@cgjobs.in</span>
                        </div>
                    </div>

                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition">
                            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                        </button>
                    </form>
                </div>

            </aside>

            <!-- Main App Body Canvas -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                
                <!-- Modern Top Glass Header -->
                <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-2xs">
                    <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                        
                        <!-- Left: Sidebar Toggle & Page Breadcrumb -->
                        <div class="flex items-center gap-3 min-w-0">
                            <button id="admin-menu-toggle" type="button" aria-label="Toggle Sidebar" class="w-10 h-10 inline-flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none focus:ring-2 focus:ring-brand-500">
                                <i id="admin-menu-icon" class="fa-solid fa-bars-staggered text-base"></i>
                            </button>

                            <div class="hidden sm:flex items-center space-x-2 text-xs font-semibold text-slate-500">
                                <span>Admin</span>
                                <span>/</span>
                                <span class="text-slate-900 font-bold truncate">@yield('title', 'Dashboard')</span>
                            </div>
                        </div>

                        <!-- Right: Actions & Tools -->
                        <div class="flex items-center gap-2 sm:gap-3">

                            <!-- View Public Site -->
                            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="hidden md:inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-slate-300 text-slate-600 hover:text-slate-900 text-xs font-bold transition bg-white shadow-2xs">
                                <i class="fa-solid fa-globe text-brand-600"></i>
                                <span>लाइव वेबसाइट ↗</span>
                            </a>

                            <!-- Quick Add Job Button -->
                            <a href="{{ route('admin.jobs.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm shadow-brand-600/20 transition">
                                <i class="fa-solid fa-plus"></i>
                                <span class="hidden sm:inline">नई भर्ती</span>
                            </a>

                            <!-- Logout -->
                            <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-700 hover:text-rose-600 text-xs font-bold transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-power-off text-xs"></i>
                                    <span class="hidden sm:inline">Logout</span>
                                </button>
                            </form>

                        </div>
                    </div>
                </header>

                <!-- Page Content Area -->
                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 overflow-y-auto">
                    
                    <!-- Flash Message Notifications -->
                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800">सफलतापूर्वक पूर्ण (Success)</h4>
                                    <p class="text-xs sm:text-sm font-medium text-emerald-900 mt-0.5">{{ session('success') }}</p>
                                </div>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 p-1 text-sm">&times;</button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="mb-6 p-4 rounded-2xl bg-brand-50 border border-brand-200 text-brand-900 flex items-start justify-between gap-3 shadow-xs">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fa-solid fa-circle-info"></i>
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-brand-800">सूचना (Information)</h4>
                                    <p class="text-xs sm:text-sm font-medium text-brand-900 mt-0.5">{{ session('info') }}</p>
                                </div>
                            </div>
                            <button onclick="this.parentElement.remove()" class="text-brand-700 hover:text-brand-900 p-1 text-sm">&times;</button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 shadow-xs">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </span>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-800">कृपया त्रुटियों को सुधारें (Errors)</h4>
                            </div>
                            <ul class="list-disc list-inside text-xs space-y-1 pl-11 text-rose-950">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')

                </main>

                <!-- Clean Modern Admin Footer -->
                <footer class="bg-white border-t border-slate-200/80 px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800">CGJobs Portal Pro</span>
                        <span>•</span>
                        <span>Backend Version 2.5</span>
                    </div>
                    <div>
                        © {{ date('Y') }} छत्तीसगढ़ रोजगार एवं परीक्षा प्रबंधन प्रणाली। All Rights Reserved.
                    </div>
                </footer>

            </div>
        </div>

    </div>

    <!-- Sidebar Collapse & Responsive JS -->
    <script>
        (function() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('admin-backdrop');
            const toggle = document.getElementById('admin-menu-toggle');
            const closeBtn = document.getElementById('admin-sidebar-close');

            if (!sidebar || !toggle) return;

            const isMobile = () => window.matchMedia('(max-width: 1023px)').matches;

            function setSidebarState(open) {
                if (isMobile()) {
                    sidebar.classList.toggle('admin-open', open);
                    backdrop.classList.toggle('hidden', !open);
                    backdrop.classList.toggle('admin-open', open);
                    document.body.classList.toggle('overflow-hidden', open);
                } else {
                    sidebar.classList.toggle('admin-collapsed', !open);
                    localStorage.setItem('cgjobs-sidebar-collapsed', open ? '0' : '1');
                }
            }

            const isSavedCollapsed = localStorage.getItem('cgjobs-sidebar-collapsed') === '1';
            if (!isMobile()) {
                setSidebarState(!isSavedCollapsed);
            }

            toggle.addEventListener('click', () => {
                const isOpen = isMobile() 
                    ? sidebar.classList.contains('admin-open') 
                    : !sidebar.classList.contains('admin-collapsed');
                setSidebarState(!isOpen);
            });

            if (closeBtn) {
                closeBtn.addEventListener('click', () => setSidebarState(false));
            }

            if (backdrop) {
                backdrop.addEventListener('click', () => setSidebarState(false));
            }

            window.addEventListener('resize', () => {
                if (!isMobile()) {
                    backdrop.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                    setSidebarState(localStorage.getItem('cgjobs-sidebar-collapsed') !== '1');
                }
            });
        })();
    </script>
</body>
</html>
