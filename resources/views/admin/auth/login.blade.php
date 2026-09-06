<!DOCTYPE html>
<html lang="hi" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>एडमिन लॉगिन | CGJobs Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body { font-family: 'Inter', 'Noto Sans Devanagari', sans-serif; }</style>
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-100">

    <div class="max-w-md w-full">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-blue-600 text-white mx-auto flex items-center justify-center text-3xl shadow-lg mb-3">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">CGJobs Admin Portal</h1>
            <p class="text-xs text-slate-500 mt-1">छत्तीसगढ़ रोजगार व सूचना प्रबंधन एडमिन पैनल (Laravel)</p>
        </div>

        <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-6">
            
            @if(session('success'))
                <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl font-medium">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">यूजरनेम (Username)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-user text-xs"></i>
                        </span>
                        <input type="text" name="username" value="{{ old('username', 'admin') }}" required class="w-full pl-9 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">पासवर्ड (Password)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                        <input type="password" name="password" value="admin123" required class="w-full pl-9 pr-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow-md transition transform active:scale-95 flex items-center justify-center space-x-2">
                        <span>लॉगिन करें (Sign In)</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            <div class="pt-4 border-t border-slate-100 text-center text-xs text-slate-400">
                डिफ़ॉल्ट लॉगिन: <code>admin</code> / <code>admin123</code> (.env में बदल सकते हैं)
            </div>

        </div>

    </div>

</body>
</html>
