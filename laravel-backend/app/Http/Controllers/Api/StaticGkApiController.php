<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StaticGk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticGkApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StaticGk::query();

        if ($request->filled('category')) {
            $cat = $request->category;
            $query->where(function ($q) use ($cat) {
                $q->where('category', $cat)
                  ->orWhere('category_hindi', $cat);
            });
        }

        if ($request->filled('query')) {
            $s = $request->query('query');
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('hindi_title', 'like', "%{$s}%")
                  ->orWhere('question', 'like', "%{$s}%")
                  ->orWhere('answer', 'like', "%{$s}%");
            });
        }

        $limit = min((int)$request->query('limit', 100), 200);
        $items = $query->orderBy('display_order', 'asc')
                       ->orderBy('id', 'desc')
                       ->limit($limit)
                       ->get()
                       ->map(fn($item) => $item->toApiArray());

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'items' => $items,
        ]);
    }
}
