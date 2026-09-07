@extends('layouts.admin')

@section('title','All Jobs')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">All Jobs</h2>
            <p class="text-xs text-slate-500 mt-0.5">सभी प्रकाशित भर्ती रिकॉर्ड — खोजें, फ़िल्टर करें, संशोधित करें, दोबारा प्रकाशित करें या हटाएं।</p>
        </div>
        <a href="{{ route('admin.jobs.create',['section'=>'jobs']) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs rounded-xl shadow-sm transition"><i class="fa-solid fa-plus mr-1.5"></i> Create New Job</a>
    </div>

    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('admin.jobs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-3">
            <input type="hidden" name="section" value="jobs">
            <div class="xl:col-span-2"><label class="label">Search</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Title, vacancy, source..." class="input"></div>
            <div><label class="label">Main Category</label><select name="job_category" class="input"><option value="">All Categories</option>@foreach($jobCategories as $c)<option value="{{ $c }}" @selected(request('job_category')===$c)>{{ $c }}</option>@endforeach</select></div>
            <div><label class="label">Department</label><select name="department" class="input"><option value="">All Departments</option>@foreach($departments as $d)<option value="{{ $d }}" @selected(request('department')===$d)>{{ $d }}</option>@endforeach</select></div>
            <div><label class="label">Source</label><select name="source" class="input"><option value="">All Sources</option>@foreach($sources as $source)<option value="{{ $source }}" @selected(request('source')===$source)>{{ $source }}</option>@endforeach</select></div>
            <div><label class="label">Post Type</label><select name="post_type" class="input"><option value="">All Types</option>@foreach(['job'=>'Job Notification','admit_card'=>'Admit Card','result'=>'Result','syllabus'=>'Syllabus','answer_key'=>'Answer Key'] as $v=>$label)<option value="{{ $v }}" @selected(request('post_type')===$v)>{{ $label }}</option>@endforeach</select></div>
            <div><label class="label">Deadline</label><select name="deadline" class="input"><option value="">All</option><option value="open" @selected(request('deadline')==='open')>Open / More than 2 days</option><option value="closing" @selected(request('deadline')==='closing')>Closing within 2 days</option><option value="expired" @selected(request('deadline')==='expired')>Expired</option></select></div>
            <div><label class="label">Published From</label><input type="date" name="published_from" value="{{ request('published_from') }}" class="input"></div>
            <div><label class="label">Published To</label><input type="date" name="published_to" value="{{ request('published_to') }}" class="input"></div>
            <div><label class="label">Sort</label><select name="sort" class="input"><option value="latest" @selected(request('sort','latest')==='latest')>Newest</option><option value="oldest" @selected(request('sort')==='oldest')>Oldest</option><option value="title" @selected(request('sort')==='title')>Title A–Z</option></select></div>
            <div class="flex items-end gap-2"><button type="submit" class="flex-1 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl">Apply Filters</button><a href="{{ route('admin.jobs.index',['section'=>'jobs']) }}" class="px-3 py-2.5 text-xs font-semibold text-rose-600 border border-rose-100 rounded-xl hover:bg-rose-50">Clear</a></div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3"><div><h3 class="text-sm font-bold text-slate-800">Published Jobs</h3><p class="text-[11px] text-slate-400 mt-0.5">{{ $jobs->total() }} matching job records</p></div><span class="text-[11px] text-slate-500">Main Category → Department</span></div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 text-[11px] uppercase tracking-wider"><tr><th class="py-3 px-4">ID</th><th class="py-3 px-4">Job</th><th class="py-3 px-4">Category → Department</th><th class="py-3 px-4">Posts / Salary</th><th class="py-3 px-4">Closing Date</th><th class="py-3 px-4">Source</th><th class="py-3 px-4 text-right">Actions</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($jobs as $job)
                    <tr class="hover:bg-slate-50/80 transition align-top">
                        <td class="py-3.5 px-4 text-xs font-mono text-slate-400">#{{ $job->id }}</td>
                        <td class="py-3.5 px-4 min-w-[280px] max-w-md"><div class="flex items-center gap-1.5">@if($job->post_type&&$job->post_type!=='job')<span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 text-[9px] font-bold uppercase">{{ $job->post_type }}</span>@endif<div class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-2">{{ $job->title }}</div></div><div class="text-[11px] text-slate-500 line-clamp-1 mt-1">{{ $job->summary }}</div><div class="flex items-center gap-2 mt-1.5">@if($job->is_breaking)<span class="px-1.5 py-0.5 bg-rose-100 text-rose-700 text-[9px] font-bold rounded">HOT</span>@endif @if($job->official_notification_url)<a href="{{ $job->official_notification_url }}" target="_blank" class="text-[10px] text-blue-600 hover:underline">PDF</a>@endif @if($job->apply_url)<a href="{{ $job->apply_url }}" target="_blank" class="text-[10px] text-emerald-600 hover:underline">Apply</a>@endif</div></td>
                        <td class="py-3.5 px-4 min-w-[180px]"><div class="text-xs font-bold text-brand-700">{{ $job->job_category ?: 'CGSSB' }}</div><div class="text-[11px] text-slate-600 mt-1">{{ $job->department ?: 'Other Departments' }}</div></td>
                        <td class="py-3.5 px-4 whitespace-nowrap text-xs font-semibold text-slate-700"><div>{{ $job->vacancies ?: '—' }}</div>@if($job->salary)<div class="text-[10px] text-slate-400 font-normal mt-0.5">{{ $job->salary }}</div>@endif</td>
                        <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-600"><div>{{ $job->last_date ?: '—' }}</div>@if($job->last_date)<div class="text-[9px] text-slate-400 mt-0.5">Deadline reminder automated</div>@endif</td>
                        <td class="py-3.5 px-4 min-w-[120px]"><div class="text-[11px] font-semibold text-slate-700">{{ $job->source ?: 'CGJobs' }}</div><div class="text-[9px] text-slate-400 mt-0.5">Published {{ $job->published_at ?: '—' }}</div></td>
                        <td class="py-3.5 px-4 whitespace-nowrap text-right"><div class="flex items-center justify-end gap-1.5"><a href="{{ route('admin.jobs.edit',$job) }}" class="px-2 py-1.5 rounded-lg text-brand-600 hover:bg-brand-50 text-[11px] font-semibold" title="Modify"><i class="fa-solid fa-pen-to-square mr-1"></i>Edit</a><form method="POST" action="{{ route('admin.jobs.republish',$job) }}" onsubmit="return confirm('इस भर्ती को फिर से प्रकाशित करके Android push notification भेजें?');" class="inline">@csrf<button class="px-2 py-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 text-[11px] font-semibold" title="Republish"><i class="fa-solid fa-rotate mr-1"></i>Republish</button></form><form method="POST" action="{{ route('admin.jobs.destroy',$job) }}" onsubmit="return confirm('क्या आप इस भर्ती को हटाना चाहते हैं?');" class="inline">@csrf @method('DELETE')<button class="px-2 py-1.5 rounded-lg text-rose-500 hover:bg-rose-50 text-[11px] font-semibold" title="Delete"><i class="fa-solid fa-trash-can mr-1"></i>Delete</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-400 text-xs">कोई job नहीं मिली। Filters बदलें या नई भर्ती बनाएं।</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())<div class="p-4 border-t border-slate-100">{{ $jobs->links() }}</div>@endif
    </div>
</div>
<style>.label{display:block;font-size:10px;font-weight:800;color:#64748b;margin-bottom:5px;text-transform:uppercase;letter-spacing:.04em}.input{width:100%;border:1px solid #dbe2ea;border-radius:11px;padding:9px 11px;background:#fff;font-size:12px;outline:none}</style>
@endsection
