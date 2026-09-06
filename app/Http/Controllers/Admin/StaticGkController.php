<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StaticGk;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaticGkController extends Controller
{
    public function index(Request $request)
    {
        $query = StaticGk::query();
        if ($request->filled('category')) $query->where('category', $request->category);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")->orWhere('title_en', 'like', "%{$search}%")
                  ->orWhere('hindi_title', 'like', "%{$search}%")->orWhere('question', 'like', "%{$search}%")
                  ->orWhere('question_en', 'like', "%{$search}%");
            });
        }
        $items = $query->orderBy('display_order')->orderByDesc('id')->paginate(15);
        $categories = Category::where('section_id', 'static_gk')->orWhere('slug', 'like', '%gk%')->get();
        if ($categories->isEmpty()) $categories = collect();
        return view('admin.static_gk.index', compact('items', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('section_id', 'static_gk')->get();
        return view('admin.static_gk.create', compact('categories'));
    }

    public function store(Request $request, TranslationService $translator)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255', 'category' => 'required|string|max:100',
            'question' => 'nullable|string', 'answer' => 'nullable|string', 'key_points_raw' => 'nullable|string',
            'detailed_notes' => 'nullable|string', 'year_exam_reference' => 'nullable|string|max:150',
            'is_verified' => 'nullable|boolean', 'display_order' => 'nullable|integer',
        ]);
        $keyPoints = $this->parsePoints($request->key_points_raw);
        $gk = new StaticGk();
        $gk->custom_id = 'gk-' . Str::slug($request->title) . '-' . rand(100, 999);
        $gk->title_en = $validated['title'];
        $gk->category_en = $validated['category'];
        $gk->question_en = $validated['question'] ?? null;
        $gk->answer_en = $validated['answer'] ?? null;
        $gk->key_points_en = $keyPoints;
        $gk->detailed_notes_en = $validated['detailed_notes'] ?? null;
        $gk->year_exam_reference_en = $validated['year_exam_reference'] ?? null;
        $gk->title = $validated['title']; $gk->hindi_title = $validated['title'];
        $gk->category = $validated['category']; $gk->category_hindi = $validated['category'];
        $gk->question = $validated['question'] ?? null; $gk->answer = $validated['answer'] ?? null;
        $gk->key_points = $keyPoints; $gk->detailed_notes = $validated['detailed_notes'] ?? null;
        $gk->year_exam_reference = $validated['year_exam_reference'] ?? null;
        $gk->is_verified = $request->has('is_verified'); $gk->display_order = $validated['display_order'] ?? 0;
        $gk->translation_status = 'pending'; $gk->save();
        $this->translateGk($gk, $translator);
        return redirect()->route('admin.static-gk.index')->with('success', 'Static GK published with automatic Hindi translation.');
    }

    public function edit(StaticGk $staticGk)
    {
        $categories = Category::where('section_id', 'static_gk')->get();
        return view('admin.static_gk.edit', compact('staticGk', 'categories'));
    }

    public function update(Request $request, StaticGk $staticGk, TranslationService $translator)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255', 'category' => 'required|string|max:100',
            'question' => 'nullable|string', 'answer' => 'nullable|string', 'key_points_raw' => 'nullable|string',
            'detailed_notes' => 'nullable|string', 'year_exam_reference' => 'nullable|string|max:150', 'display_order' => 'nullable|integer',
        ]);
        $points = $this->parsePoints($request->key_points_raw);
        $staticGk->fill([
            'title_en'=>$validated['title'], 'category_en'=>$validated['category'], 'question_en'=>$validated['question'] ?? null,
            'answer_en'=>$validated['answer'] ?? null, 'key_points_en'=>$points, 'detailed_notes_en'=>$validated['detailed_notes'] ?? null,
            'year_exam_reference_en'=>$validated['year_exam_reference'] ?? null, 'title'=>$validated['title'], 'hindi_title'=>$validated['title'],
            'category'=>$validated['category'], 'category_hindi'=>$validated['category'], 'question'=>$validated['question'] ?? null,
            'answer'=>$validated['answer'] ?? null, 'key_points'=>$points, 'detailed_notes'=>$validated['detailed_notes'] ?? null,
            'year_exam_reference'=>$validated['year_exam_reference'] ?? null, 'is_verified'=>$request->has('is_verified'),
            'display_order'=>$validated['display_order'] ?? 0, 'translation_status'=>'pending', 'translated_at'=>null, 'translation_error'=>null,
        ])->save();
        $this->translateGk($staticGk->fresh(), $translator);
        return redirect()->route('admin.static-gk.index')->with('success', 'Static GK updated and Hindi translation refreshed.');
    }

    public function destroy(StaticGk $staticGk)
    {
        $staticGk->delete();
        return redirect()->route('admin.static-gk.index')->with('success', 'Static GK कार्ड हटा दिया गया!');
    }

    private function parsePoints(?string $raw): array
    {
        return collect(preg_split('/\r?\n/', $raw ?? ''))->map(fn($line) => ltrim(trim($line), "•-* \t"))->filter()->values()->all();
    }

    private function translateGk(StaticGk $gk, TranslationService $translator): void
    {
        try {
            $t = $translator->translateMany([
                'title'=>$gk->title_en, 'category'=>$gk->category_en, 'question'=>$gk->question_en,
                'answer'=>$gk->answer_en, 'notes'=>$gk->detailed_notes_en, 'reference'=>$gk->year_exam_reference_en,
            ]);
            $points = [];
            foreach (($gk->key_points_en ?: []) as $point) $points[] = $translator->translate($point) ?: $point;
            $ready = !empty($t['title']);
            $gk->title = $t['title'] ?: $gk->title_en; $gk->hindi_title = $gk->title;
            $gk->category = $t['category'] ?: $gk->category_en; $gk->category_hindi = $gk->category;
            $gk->question = $t['question'] ?: $gk->question_en; $gk->answer = $t['answer'] ?: $gk->answer_en;
            $gk->detailed_notes = $t['notes'] ?: $gk->detailed_notes_en; $gk->year_exam_reference = $t['reference'] ?: $gk->year_exam_reference_en;
            $gk->key_points = $points ?: ($gk->key_points_en ?: []);
            $gk->translation_status = $ready ? 'ready' : 'pending'; $gk->translation_error = $ready ? null : 'GOOGLE_TRANSLATE_API_KEY is not configured or translation service is unavailable.';
            $gk->translated_at = $ready ? now() : null; $gk->save();
        } catch (\Throwable $e) {
            $gk->translation_status = 'failed'; $gk->translation_error = $e->getMessage(); $gk->save();
        }
    }
}
