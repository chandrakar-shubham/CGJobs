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
                            <span class="pill">
                                {{ $item->category ?: ($type === 'jobs' ? 'Government Job' : 'Current Affairs') }}
                            </span>
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
                            <div style="margin-top:13px;font-size:11px;font-weight:850">
                                Last date: {{ $item->last_date }}
                            </div>
                        @endif
                        <span class="read">Read full update →</span>
                    </a>
                @endif
            @empty
                <div class="card" style="grid-column:1/-1;text-align:center;padding:55px;color:#64748b">
                    @if($type === 'jobs')
                        अभी कोई सरकारी नौकरी उपलब्ध नहीं है।
                    @elseif($type === 'current-affairs')
                        अभी कोई Current Affairs उपलब्ध नहीं है।
                    @else
                        अभी कोई Static GK उपलब्ध नहीं है।
                    @endif
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
@endsection
