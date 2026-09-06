@extends('web.layout')

@section('title', $title.' — CGJobs')

@section('content')
<section class="page-head">
    <div class="container">
        <div class="kicker">
            CGJobs •
            @if($type === 'jobs')
                Recruitment
            @elseif($type === 'gk')
                Exam Ready
            @else
                Stay Informed
            @endif
        </div>
        <h1>{{ $title }}</h1>
        <p>
            @if($type === 'jobs')
                छत्तीसगढ़ और सरकारी भर्ती से जुड़े नवीनतम updates, vacancies, eligibility और important dates एक जगह।
            @elseif($type === 'gk')
                प्रतियोगी परीक्षाओं के लिए महत्वपूर्ण Static GK, questions और concise notes।
            @else
                परीक्षा की तैयारी के लिए महत्वपूर्ण national, state और education current affairs — सरल भाषा में।
            @endif
        </p>
    </div>
</section>

<section style="padding-bottom:55px">
    <div class="container">
        <form method="GET" action="{{ url()->current() }}" class="card" style="margin-bottom:24px;padding:18px">
            <div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(160px,1fr) minmax(150px,.8fr) auto;gap:12px;align-items:end">
                <label style="display:block;font-size:12px;font-weight:800;color:#334155">
                    Search
                    <input type="search" name="q" value="{{ $search }}" placeholder="{{ $type === 'jobs' ? 'Search jobs, departments, posts...' : ($type === 'gk' ? 'Search questions, topics...' : 'Search current affairs...') }}" style="display:block;width:100%;margin-top:7px;border:1px solid #dbe2ea;border-radius:12px;padding:12px 14px;font:inherit;outline:none">
                </label>
                <label style="display:block;font-size:12px;font-weight:800;color:#334155">
                    Category
                    <select name="category" style="display:block;width:100%;margin-top:7px;border:1px solid #dbe2ea;border-radius:12px;padding:12px 14px;background:#fff;font:inherit">
                        <option value="">All categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" @selected($selectedCategory === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </label>
                <label style="display:block;font-size:12px;font-weight:800;color:#334155">
                    Sort
                    <select name="sort" style="display:block;width:100%;margin-top:7px;border:1px solid #dbe2ea;border-radius:12px;padding:12px 14px;background:#fff;font:inherit">
                        <option value="latest" @selected($sort === 'latest')>Latest first</option>
                        @if($type === 'jobs')
                            <option value="closing" @selected($sort === 'closing')>Closing soon</option>
                        @endif
                        <option value="oldest" @selected($sort === 'oldest')>Oldest first</option>
                    </select>
                </label>
                <button type="submit" class="btn btn-primary" style="height:45px">Search</button>
            </div>
            @if($search || $selectedCategory || $sort !== 'latest')
                <div style="margin-top:12px">
                    <a href="{{ url()->current() }}" style="font-size:12px;font-weight:800;color:#64748b">Clear filters ×</a>
                </div>
            @endif
        </form>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin:0 0 15px">
            <div style="font-size:13px;color:#64748b;font-weight:700">
                {{ $items->total() }} {{ $type === 'jobs' ? 'jobs' : ($type === 'gk' ? 'GK questions' : 'updates') }} found
            </div>
        </div>

        <div class="grid grid-3">
            @forelse($items as $item)
                @if($type === 'gk')
                    <a class="card" href="{{ route('gk.show', $item->custom_id ?: $item->id) }}">
                        <div class="meta-row">
                            <span class="pill">{{ $item->category ?: 'GK' }}</span>
                        </div>
                        <h3>{{ $item->hindi_title ?: $item->title }}</h3>
                        @if($item->question)
                            <p>{{ $item->question }}</p>
                        @endif
                        @if($item->answer)
                            <div class="gk-answer">{{ $item->answer }}</div>
                        @endif
                        <span class="read">Read GK →</span>
                    </a>
                @else
                    <a class="card" href="{{ route($type === 'current-affairs' ? 'current-affairs.show' : 'job.show', $item->custom_id ?: $item->id) }}">
                        <div class="meta-row">
                            <span class="pill">{{ $item->category ?: ($type === 'jobs' ? 'Government Job' : 'Current Affairs') }}</span>
                            @if($item->is_new)
                                <span class="pill pill-new">NEW</span>
                            @endif
                            @if($item->is_breaking)
                                <span class="pill pill-breaking">BREAKING</span>
                            @endif
                        </div>
                        <h3>{{ $item->title }}</h3>
                        <p>{{ $item->summary ?: 'CGJobs पर इस update की पूरी जानकारी पढ़ें।' }}</p>
                        @if($type === 'jobs' && $item->last_date)
                            <div style="margin-top:13px;font-size:11px;font-weight:850">Last date: {{ $item->last_date }}</div>
                        @endif
                        <span class="read">Read full update →</span>
                    </a>
                @endif
            @empty
                <div class="card" style="grid-column:1/-1;text-align:center;padding:55px;color:#64748b">
                    <div style="font-size:38px;margin-bottom:10px">⌕</div>
                    <h3 style="margin-bottom:7px">No results found</h3>
                    <p>Try another keyword or remove one of the filters.</p>
                    <a href="{{ url()->current() }}" class="btn btn-primary" style="margin-top:14px">View all</a>
                </div>
            @endforelse
        </div>

        @if($items->hasPages())
            <div class="pager">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</section>

<style>
@media (max-width: 820px) {
    form.card > div:first-child { grid-template-columns: 1fr !important; }
    form.card button { width:100%; }
}
</style>
@endsection
