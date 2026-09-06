<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StaticGk;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaticGkController extends Controller
{
    public function index(Request $request)
    {
        $query = StaticGk::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('hindi_title', 'like', "%{$search}%")
                  ->orWhere('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('display_order', 'asc')->orderBy('id', 'desc')->paginate(15);
        $categories = Category::where('section_id', 'static_gk')->orWhere('slug', 'like', '%gk%')->get();

        if ($categories->isEmpty()) {
            $categories = collect([
                (object)['name' => 'छत्तीसगढ़ का इतिहास', 'hindi_name' => 'इतिहास'],
                (object)['name' => 'छत्तीसगढ़ का भूगोल', 'hindi_name' => 'भूगोल'],
                (object)['name' => 'जनजातियां व लोक संस्कृति', 'hindi_name' => 'जनजाति'],
                (object)['name' => 'अर्थव्यवस्था व खनिज संसाधन', 'hindi_name' => 'अर्थव्यवस्था'],
                (object)['name' => 'प्रशासनिक ढांचा व पंचायती राज', 'hindi_name' => 'प्रशासन'],
            ]);
        }

        return view('admin.static_gk.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('section_id', 'static_gk')->get();
        return view('admin.static_gk.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'hindi_title' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'category_hindi' => 'nullable|string|max:100',
            'question' => 'nullable|string',
            'answer' => 'nullable|string',
            'key_points_raw' => 'nullable|string',
            'detailed_notes' => 'nullable|string',
            'year_exam_reference' => 'nullable|string|max:150',
            'is_verified' => 'nullable|boolean',
            'display_order' => 'nullable|integer',
        ]);

        $keyPoints = [];
        if (!empty($request->key_points_raw)) {
            $lines = explode("\n", $request->key_points_raw);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                $trimmed = ltrim($trimmed, "•-* \t");
                if (!empty($trimmed)) {
                    $keyPoints[] = $trimmed;
                }
            }
        }

        $gk = new StaticGk();
        $gk->custom_id = 'gk-' . Str::slug($request->title) . '-' . rand(100, 999);
        $gk->title = $validated['title'];
        $gk->hindi_title = $validated['hindi_title'] ?: $validated['title'];
        $gk->category = $validated['category'];
        $gk->category_hindi = $validated['category_hindi'] ?: $validated['category'];
        $gk->question = $validated['question'];
        $gk->answer = $validated['answer'];
        $gk->key_points = $keyPoints;
        $gk->detailed_notes = $validated['detailed_notes'];
        $gk->year_exam_reference = $validated['year_exam_reference'];
        $gk->is_verified = $request->has('is_verified');
        $gk->display_order = $validated['display_order'] ?? 0;
        $gk->save();

        return redirect()->route('admin.static-gk.index')->with('success', 'Static GK कार्ड सफलतापूर्वक जोड़ा गया!');
    }

    public function edit(StaticGk $staticGk)
    {
        $categories = Category::where('section_id', 'static_gk')->get();
        return view('admin.static_gk.edit', compact('staticGk', 'categories'));
    }

    public function update(Request $request, StaticGk $staticGk)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'hindi_title' => 'nullable|string|max:255',
            'category' => 'required|string|max:100',
            'category_hindi' => 'nullable|string|max:100',
            'question' => 'nullable|string',
            'answer' => 'nullable|string',
            'key_points_raw' => 'nullable|string',
            'detailed_notes' => 'nullable|string',
            'year_exam_reference' => 'nullable|string|max:150',
            'display_order' => 'nullable|integer',
        ]);

        $keyPoints = [];
        if (!empty($request->key_points_raw)) {
            $lines = explode("\n", $request->key_points_raw);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                $trimmed = ltrim($trimmed, "•-* \t");
                if (!empty($trimmed)) {
                    $keyPoints[] = $trimmed;
                }
            }
        }

        $staticGk->title = $validated['title'];
        $staticGk->hindi_title = $validated['hindi_title'] ?: $validated['title'];
        $staticGk->category = $validated['category'];
        $staticGk->category_hindi = $validated['category_hindi'] ?: $validated['category'];
        $staticGk->question = $validated['question'];
        $staticGk->answer = $validated['answer'];
        $staticGk->key_points = $keyPoints;
        $staticGk->detailed_notes = $validated['detailed_notes'];
        $staticGk->year_exam_reference = $validated['year_exam_reference'];
        $staticGk->is_verified = $request->has('is_verified');
        $staticGk->display_order = $validated['display_order'] ?? 0;
        $staticGk->save();

        return redirect()->route('admin.static-gk.index')->with('success', 'Static GK कार्ड सफलतापूर्वक अपडेट किया गया!');
    }

    public function destroy(StaticGk $staticGk)
    {
        $staticGk->delete();
        return redirect()->route('admin.static-gk.index')->with('success', 'Static GK कार्ड हटा दिया गया!');
    }
}
