<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSection;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::orderBy('display_order', 'asc');
        
        if ($request->filled('section')) {
            $query->where('section_id', $request->section);
        }

        $categories = $query->get();
        $sections = AppSection::where('is_active', true)->orderBy('display_order', 'asc')->get();

        return view('admin.categories.index', compact('categories', 'sections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'hindi_name' => 'nullable|string|max:150',
            'section_id' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $validated['slug'] = $slug;
        $validated['is_active'] = true;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        Category::create($validated);

        return redirect()->route('admin.categories.index', ['section' => $validated['section_id']])
            ->with('success', 'श्रेणी सफलतापूर्वक जोड़ी गई (Category created)');
    }

    public function destroy(Category $category)
    {
        $section = $category->section_id;
        $category->delete();
        return redirect()->route('admin.categories.index', ['section' => $section])
            ->with('success', 'श्रेणी हटा दी गई (Category deleted)');
    }
}
