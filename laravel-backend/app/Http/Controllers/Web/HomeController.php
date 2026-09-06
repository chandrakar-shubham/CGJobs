<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\StaticGk;
use Illuminate\Http\Request;
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

    public function jobs(Request $request): View
    {
        return $this->listing('jobs', $request);
    }

    public function currentAffairs(Request $request): View
    {
        return $this->listing('current-affairs', $request);
    }

    public function staticGk(Request $request): View
    {
        return $this->listing('gk', $request);
    }

    public function listing(string $type, Request $request): View
    {
        $map = [
            'jobs' => ['title' => 'Latest Government Jobs'],
            'current-affairs' => ['title' => 'Current Affairs & News'],
            'gk' => ['title' => 'Static GK'],
        ];
        abort_unless(isset($map[$type]), 404);

        $search = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $sort = (string) $request->query('sort', 'latest');

        // Build the section-scoped base query first. Category options must come
        // from this section only, never from the other content sections.
        if ($type === 'gk') {
            $query = StaticGk::query();
            $categoryQuery = StaticGk::query();
            $searchColumns = ['title', 'hindi_title', 'question', 'answer'];
        } else {
            $query = Job::query();
            $categoryQuery = Job::query();
            if ($type === 'jobs') {
                $sectionFilter = function ($q) {
                    $q->where('section', 'jobs')->orWhereNull('section');
                };
                $query->where($sectionFilter);
                $categoryQuery->where($sectionFilter);
            } else {
                $sectionFilter = function ($q) {
                    $q->whereIn('section', ['current-affairs', 'current_affairs', 'news']);
                };
                $query->where($sectionFilter);
                $categoryQuery->where($sectionFilter);
            }
            $searchColumns = ['title', 'summary', 'detailed_content', 'category'];
        }

        if ($search !== '') {
            $query->where(function ($q) use ($searchColumns, $search) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($sort === 'oldest') {
            $query->oldest('id');
        } elseif ($sort === 'closing' && $type === 'jobs') {
            $query->orderByRaw('CASE WHEN last_date IS NULL OR last_date = "" THEN 1 ELSE 0 END ASC')
                ->orderBy('last_date', 'asc')
                ->orderByDesc('id');
        } else {
            $query->latest('id');
        }

        // Deliberately derive categories from the section-scoped query without
        // search/sort/category filters, so the dropdown always lists every
        // category available for the current section.
        $categories = $categoryQuery
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $items = $query->paginate(18)->withQueryString();

        return view('web.listing', [
            'type' => $type,
            'title' => $map[$type]['title'],
            'items' => $items,
            'categories' => $categories,
            'search' => $search,
            'selectedCategory' => $category,
            'sort' => $sort,
        ]);
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
