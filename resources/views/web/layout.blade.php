<!doctype html>
<html lang="hi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'CGJobs — छत्तीसगढ़ सरकारी नौकरी, करंट अफेयर्स और GK')</title>
    <meta name="description" content="छत्तीसगढ़ सरकारी नौकरी, भर्ती, करंट अफेयर्स और परीक्षा उपयोगी GK — एक ही जगह।">
    <meta name="theme-color" content="#111827">
    <link rel="canonical" href="{{ url()->current() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body{font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#f8fafc;color:#0f172a}
        .container{max-width:1180px}
        .glass{background:rgba(255,255,255,.82);backdrop-filter:blur(14px)}
        .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    </style>
    @stack('head')
</head>
<body>
<header class="sticky top-0 z-40 border-b border-slate-200/80 glass">
  <div class="container mx-auto px-4">
    <div class="h-16 flex items-center justify-between gap-4">
      <a href="{{ route('home') }}" class="flex items-center gap-3">
        <div class="h-10 w-10 rounded-xl bg-slate-950 text-white grid place-items-center font-black">CG</div>
        <div><div class="font-black tracking-tight text-lg">CGJobs</div><div class="text-[10px] text-slate-500 -mt-1">Jobs • Exams • Knowledge</div></div>
      </a>
      <nav class="hidden md:flex items-center gap-1 text-sm font-semibold">
        <a class="px-3 py-2 rounded-lg hover:bg-slate-100" href="{{ route('listing','jobs') }}">सरकारी नौकरियां</a>
        <a class="px-3 py-2 rounded-lg hover:bg-slate-100" href="{{ route('listing','current-affairs') }}">करंट अफेयर्स</a>
        <a class="px-3 py-2 rounded-lg hover:bg-slate-100" href="{{ route('listing','gk') }}">Static GK</a>
      </nav>
      <a href="https://play.google.com/store" class="hidden sm:inline-flex bg-slate-950 text-white px-4 py-2.5 rounded-xl text-sm font-bold">📱 App में पढ़ें</a>
    </div>
  </div>
</header>
<main>@yield('content')</main>
<footer class="mt-16 border-t bg-white">
  <div class="container mx-auto px-4 py-10 grid md:grid-cols-3 gap-8">
    <div><div class="font-black text-xl">CGJobs</div><p class="text-sm text-slate-500 mt-2">छत्तीसगढ़ की सरकारी नौकरी, परीक्षा और ज्ञान की तेज़, भरोसेमंद जानकारी।</p></div>
    <div><div class="font-bold mb-3">Explore</div><div class="space-y-2 text-sm text-slate-600"><a class="block hover:text-slate-950" href="{{ route('listing','jobs') }}">Jobs</a><a class="block hover:text-slate-950" href="{{ route('listing','current-affairs') }}">Current Affairs</a><a class="block hover:text-slate-950" href="{{ route('listing','gk') }}">GK</a></div></div>
    <div><div class="font-bold mb-3">CGJobs Android App</div><p class="text-sm text-slate-500">हर अपडेट को ऐप में भी तुरंत पढ़ें और सेव करें।</p><a class="inline-block mt-3 text-sm font-bold underline" href="https://play.google.com/store">Get the App →</a></div>
  </div>
  <div class="border-t py-4 text-center text-xs text-slate-400">© {{ date('Y') }} CGJobs. Built for aspirants.</div>
</footer>
</body>
</html>