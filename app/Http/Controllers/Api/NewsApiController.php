<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $channel = $request->query('channel');
        $query = $channel === 'mobile' || $channel === 'web'
            ? News::query()->publishedOn($channel)
            : News::query()->published();

        if ($request->filled('category') && !in_array($request->category, ['सभी', 'All'], true)) {
            $category = $request->category;
            $query->where(fn ($q) => $q->where('category', $category)->orWhere('category_en', $category));
        }

        if ($request->filled('query')) {
            $search = '%' . $request->query('query') . '%';
            $query->where(fn ($q) => $q
                ->where('title', 'like', $search)
                ->orWhere('title_en', 'like', $search)
                ->orWhere('summary', 'like', $search)
                ->orWhere('summary_en', 'like', $search)
                ->orWhere('content', 'like', $search)
                ->orWhere('content_en', 'like', $search)
                ->orWhere('source', 'like', $search));
        }

        $limit = min(max((int) $request->get('limit', 100), 1), 200);
        $items = $query->orderByDesc('published_at')->orderByDesc('id')->take($limit)->get();

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'channel' => $channel ?: 'published',
            'news' => $items->map(fn ($item) => $item->toApiArray($this->language($request)))->values(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $channel = $request->query('channel');
        $query = $channel === 'mobile' || $channel === 'web'
            ? News::query()->publishedOn($channel)
            : News::query()->published();
        $item = $query->find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'News article not found'], 404);
        }

        return response()->json([
            'success' => true,
            'item' => $item->toApiArray($this->language($request)),
        ]);
    }

    private function language(Request $request): string
    {
        return in_array($request->query('lang', 'hi'), ['hi', 'en'], true)
            ? $request->query('lang', 'hi')
            : 'hi';
    }
}
