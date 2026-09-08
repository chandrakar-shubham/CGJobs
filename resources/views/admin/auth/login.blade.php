<!DOCTYPE html>
<html lang="hi" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>एडमिन लॉगिन | CGJobs Control Center</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
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
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-950 relative overflow-hidden text-slate-100">

    <!-- Ambient background glow effects -->
    <div class="absolute -top-40 -right-40 w-96 h-96 bg-brand-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10 space-y-6">
        
        <!-- Logo & Branding -->
        <div class="text-center space-y-2">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 via-indigo-600 to-brand-500 text-white mx-auto flex items-center justify-center text-2xl shadow-xl shadow-brand-500/25">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">CGJobs Control Center</h1>
            <p class="text-xs text-slate-400">छत्तीसगढ़ रोजगार व सूचना प्रबंधन एडमिन पोर्टल</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl p-8 rounded-3xl border border-slate-800 shadow-2xl space-y-6">
            
            @if(session('success'))
                <div class="p-3.5 bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs rounded-2xl font-medium flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="p-3.5 bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs rounded-2xl font-medium flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        यूजरनेम (Username)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-user text-xs"></i>
                        </span>
                        <input type="text" name="username" value="{{ old('username', 'admin') }}" required autofocus class="w-full pl-10 pr-3.5 py-3 text-sm bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        पासवर्ड (Password)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                        <input type="password" id="loginPassword" name="password" value="admin123" required class="w-full pl-10 pr-10 py-3 text-sm bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:outline-none transition">
                        <button type="button" onclick="const p=document.getElementById('loginPassword'); p.type=p.type==='password'?'text':'password';" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                            <i class="fa-solid fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-brand-600 to-indigo-600 hover:from-brand-500 hover:to-indigo-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-brand-600/30 transition transform active:scale-98 flex items-center justify-center space-x-2">
                        <span>लॉगिन करें (Sign In)</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
                <span>Default: <code class="text-slate-300 bg-slate-800 px-1.5 py-0.5 rounded">admin</code> / <code class="text-slate-300 bg-slate-800 px-1.5 py-0.5 rounded">admin123</code></span>
                <a href="{{ url('/') }}" class="text-brand-400 hover:text-white transition flex items-center gap-1">
                    <span>Web Portal</span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                </a>
            </div>

        </div>

    </div>

</body>
</html>
