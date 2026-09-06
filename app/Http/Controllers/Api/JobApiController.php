<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApiController extends Controller
{
    /**
     * Get news and recruitment updates list
     */
    public function index(Request $request): JsonResponse
    {
        $query = Job::query();

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        if ($request->filled('category') && $request->category !== 'सभी' && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->filled('query')) {
            $searchTerm = '%' . $request->query('query') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('summary', 'like', $searchTerm)
                  ->orWhere('vacancies', 'like', $searchTerm);
            });
        }

        $limit = (int) $request->get('limit', 100);
        $jobs = $query->orderBy('id', 'desc')->take($limit)->get();

        return response()->json([
            'success' => true,
            'count' => $jobs->count(),
            'news' => $jobs->map(fn($job) => $job->toApiArray())->values(),
        ]);
    }

    /**
     * Dedicated /api/jobs endpoint
     */
    public function jobs(Request $request): JsonResponse
    {
        $query = Job::query()->where(function ($q) {
            $q->where('section', 'jobs')->orWhereNull('section');
        });

        if ($request->filled('category') && $request->category !== 'सभी' && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        if ($request->filled('query')) {
            $searchTerm = '%' . $request->query('query') . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('summary', 'like', $searchTerm)
                  ->orWhere('vacancies', 'like', $searchTerm);
            });
        }

        $limit = (int) $request->get('limit', 100);
        $jobs = $query->orderBy('id', 'desc')->take($limit)->get();

        return response()->json([
            'success' => true,
            'count' => $jobs->count(),
            'jobs' => $jobs->map(fn($job) => $job->toApiArray())->values(),
        ]);
    }

    /**
     * Get single news item details
     */
    public function show(string $id): JsonResponse
    {
        $job = Job::where('custom_id', $id)->orWhere('id', $id)->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job notification not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'item' => $job->toApiArray(),
        ]);
    }

    /**
     * Get recruitment categories
     */
    public function categories(Request $request): JsonResponse
    {
        $query = Category::where('is_active', true)->orderBy('display_order', 'asc');

        if ($request->filled('section')) {
            $query->where('section_id', $request->section);
        }

        $categories = $query->get();

        // If categories table is empty, return default CG categories
        if ($categories->isEmpty()) {
            $defaultCategories = [
                ['id' => 'all', 'name' => 'All', 'hindiName' => 'सभी'],
                ['id' => 'cgpsc', 'name' => 'CGPSC', 'hindiName' => 'छत्तीसगढ़ लोक सेवा'],
                ['id' => 'vyapam', 'name' => 'CG Vyapam', 'hindiName' => 'व्यापम'],
                ['id' => 'police', 'name' => 'CG Police', 'hindiName' => 'पुलिस भर्ती'],
                ['id' => 'education', 'name' => 'CG Education', 'hindiName' => 'शिक्षक भर्ती'],
                ['id' => 'health', 'name' => 'CG Health', 'hindiName' => 'स्वास्थ्य विभाग'],
            ];

            return response()->json([
                'success' => true,
                'categories' => $defaultCategories,
            ]);
        }

        return response()->json([
            'success' => true,
            'categories' => $categories->map(fn($cat) => $cat->toApiArray())->values(),
        ]);
    }
}
