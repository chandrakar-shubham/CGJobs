<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\StaticGk;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latest = Job::query()->latest('id')->take(12)->get();
        $jobs = Job::query()->where(function ($q) { $q->where('section', 'jobs')->orWhereNull('section'); })->latest('id')->take(6)->get();
        $currentAffairs = Job::query()->whereIn('section', ['current-affairs', 'current_affairs', 'news'])->latest('id')->take(6)->get();
        $gk = StaticGk::query()->latest('id')->take(6)->get();
        return view('web.home', compact('latest', 'jobs', 'currentAffairs', 'gk'));
    }

    public function listing(string $type): View
    {
        $map = [
            'jobs' => ['title' => 'Latest Government Jobs', 'query' => fn () => Job::query()->where(function ($q) { $q->where('section', 'jobs')->orWhereNull('section'); })],
            'current-affairs' => ['title' => 'Current Affairs & News', 'query' => fn () => Job::query()->whereIn('section', ['current-affairs', 'current_affairs', 'news'])],
            'gk' => ['title' => 'Static GK', 'query' => fn () => null],
        ];
        abort_unless(isset($map[$type]), 404);
        $items = $type === 'gk' ? StaticGk::query()->latest('id')->paginate(18) : $map[$type]['query']()->latest('id')->paginate(18);
        return view('web.listing', ['type' => $type, 'title' => $map[$type]['title'], 'items' => $items]);
    }

    public function job(string $id): View
    {
        $item = Job::where('custom_id', $id)->orWhere('id', $id)->firstOrFail();
        $related = Job::where('id', '!=', $item->id)->where('category', $item->category)->latest('id')->take(4)->get();
        return view('web.detail', ['item' => $item, 'type' => $this->jobType($item), 'related' => $related]);
    }

    public function gk(string $id): View
    {
        $item = StaticGk::where('custom_id', $id)->orWhere('id', $id)->firstOrFail();
        $related = StaticGk::where('id', '!=', $item->id)->where('category', $item->category)->latest('id')->take(4)->get();
        return view('web.gk-detail', compact('item', 'related'));
    }

    public function sitemap(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $urls = [
            ['loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $base.'/jobs', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $base.'/current-affairs', 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $base.'/gk', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ];
        foreach (Job::query()->select(['id','custom_id','section','updated_at'])->latest('id')->get() as $item) {
            $type = $this->jobType($item);
            $urls[] = ['loc' => $base.'/'.($type === 'current-affairs' ? 'current-affairs' : 'jobs').'/'.($item->custom_id ?: $item->id), 'lastmod' => optional($item->updated_at)->toAtomString(), 'priority' => '0.7'];
        }
        foreach (StaticGk::query()->select(['id','custom_id','updated_at'])->latest('id')->get() as $item) {
            $urls[] = ['loc' => $base.'/gk/'.($item->custom_id ?: $item->id), 'lastmod' => optional($item->updated_at)->toAtomString(), 'priority' => '0.6'];
        }
        return response()->view('web.sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $base = rtrim(config('app.url'), '/');
        return response("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api\nSitemap: {$base}/sitemap.xml\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function jobType(Job $job): string
    {
        return in_array($job->section, ['current-affairs', 'current_affairs', 'news'], true) ? 'current-affairs' : 'jobs';
    }
}
