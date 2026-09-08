<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class CurrentAffairsWebController extends Controller
{
    /**
     * Public Current Affairs Listing Page
     */
    public function index(Request $request)
    {
        $query = Job::query()->where('section', 'news');

        if ($request->filled('category') && $request->category !== 'सभी' && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->filled('q')) {
            $s = '%' . $request->q . '%';
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', $s)
                  ->orWhere('summary', 'like', $s)
                  ->orWhere('detailed_content', 'like', $s);
            });
        }

        $news = $query->orderBy('id', 'desc')->paginate(12);

        $categories = Job::where('section', 'news')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return view('web.current_affairs.index', compact('news', 'categories'));
    }

    /**
     * Public Full Article Reading Page with Source Citations
     */
    public function show(Request $request, string $slugOrId)
    {
        $lang = $request->query('lang', session('language', 'hi')) === 'en' ? 'en' : 'hi';

        $article = Job::where('slug', $slugOrId)
            ->orWhere('custom_id', $slugOrId)
            ->orWhere('id', $slugOrId)
            ->firstOrFail();

        if ($lang === 'en') {
            $article->title = $article->title_en ?: $article->title;
            $article->summary = $article->summary_en ?: $article->summary;
            $article->detailed_content = $article->detailed_content_en ?: $article->detailed_content;
            $article->category = $article->category_en ?: $article->category;
        }

        // Related articles for CGPSC / CGSSB exam preparation
        $related = Job::where('section', 'news')
            ->where('id', '!=', $article->id)
            ->where(function ($q) use ($article) {
                $q->where('category', $article->category)
                  ->orWhere('section', 'news');
            })
            ->orderBy('id', 'desc')
            ->take(4)
            ->get();

        if ($lang === 'en') {
            foreach ($related as $rel) {
                $rel->title = $rel->title_en ?: $rel->title;
                $rel->summary = $rel->summary_en ?: $rel->summary;
                $rel->category = $rel->category_en ?: $rel->category;
            }
        }

        return view('web.current_affairs.show', compact('article', 'related', 'lang'));
    }
}
