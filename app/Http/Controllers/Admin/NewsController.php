<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    private const CATEGORIES = [
        'Chhattisgarh', 'India', 'International', 'Government & Schemes',
        'Economy & Banking', 'Environment & Ecology', 'Science & Technology',
        'Defence', 'Polity & Governance', 'Awards & Appointments',
        'Reports & Indexes', 'Important Days', 'Sports', 'CGPSC',
        'CG Police', 'CG Education', 'CG Health', 'Current Affairs',
    ];

    public function index(Request $request)
    {
        $query = News::query();
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where(fn ($q) => $q->where('title', 'like', $term)
                ->orWhere('title_en', 'like', $term)
                ->orWhere('summary', 'like', $term)
                ->orWhere('summary_en', 'like', $term)
                ->orWhere('source', 'like', $term));
        }
        foreach (['category', 'status', 'language'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->$filter);
        }
        $news = $query->orderByDesc('published_at')->orderByDesc('id')->paginate(20)->withQueryString();
        return view('admin.news.index', compact('news') + ['categories' => self::CATEGORIES]);
    }

    public function create()
    {
        return view('admin.news.create', ['news' => new News(), 'categories' => self::CATEGORIES]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = $data['status'] ?? 'draft';
        $data['language'] = $data['language'] ?? 'en';
        $data['featured'] = $request->boolean('featured');
        $data['tags'] = $this->tags($request->input('tags'));
        News::create($data);
        return redirect()->route('admin.news.index')->with('success', 'News article created.');
    }

    public function edit(News $news)
    {
        return view('admin.news.edit', ['news' => $news, 'categories' => self::CATEGORIES]);
    }

    public function update(Request $request, News $news)
    {
        $data = $this->validated($request);
        $data['featured'] = $request->boolean('featured');
        $data['tags'] = $this->tags($request->input('tags'));
        $news->update($data);
        return redirect()->route('admin.news.index')->with('success', 'News article updated.');
    }

    public function publish(News $news)
    {
        $news->update(['status' => 'published', 'published_at' => $news->published_at ?: now(), 'published_web' => true, 'published_mobile' => true]);
        return back()->with('success', 'News article published to Website + Mobile/App.');
    }

    public function destroy(News $news)
    {
        $news->delete();
        return back()->with('success', 'News article deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255', 'title_en' => 'nullable|string|max:255',
            'summary' => 'nullable|string', 'summary_en' => 'nullable|string',
            'content' => 'nullable|string', 'content_en' => 'nullable|string',
            'source' => 'nullable|string|max:255', 'source_url' => 'nullable|url|max:2000',
            'original_url' => 'nullable|url|max:2000', 'image_url' => 'nullable|url|max:2000',
            'category' => 'required|string|max:120', 'category_en' => 'nullable|string|max:120',
            'language' => 'required|in:hi,en', 'published_at' => 'nullable|date',
            'exam_relevance' => 'nullable|integer|min:0|max:5',
            'status' => 'required|in:draft,review,published,archived',
            'tags' => 'nullable|string|max:1000',
        ]);
    }

    private function tags(?string $value): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,\n]+/', (string) $value))));
    }
}
