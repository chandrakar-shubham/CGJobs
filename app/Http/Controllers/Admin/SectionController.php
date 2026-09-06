<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSection;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    public function index()
    {
        $sections = AppSection::with('categories')->orderBy('display_order', 'asc')->get();

        if ($sections->isEmpty()) {
            // Create default sections if not exist
            AppSection::firstOrCreate(['section_key' => 'jobs'], [
                'name' => 'Jobs & Vacancies',
                'hindi_name' => 'सरकारी नौकरियां',
                'description' => 'छत्तीसगढ़ व केंद्र स्तर की सभी नई भर्तियां',
                'icon' => 'briefcase',
                'is_active' => true,
                'display_order' => 1,
            ]);

            AppSection::firstOrCreate(['section_key' => 'news'], [
                'name' => 'Current Affairs & News',
                'hindi_name' => 'दैनिक समसामयिकी व समाचार',
                'description' => 'राज्य, राष्ट्रीय व अंतर्राष्ट्रीय करंट अफेयर्स',
                'icon' => 'newspaper',
                'is_active' => true,
                'display_order' => 2,
            ]);

            AppSection::firstOrCreate(['section_key' => 'static_gk'], [
                'name' => 'Static GK & Study',
                'hindi_name' => 'सामान्य ज्ञान व अध्ययन',
                'description' => 'इतिहास, भूगोल, जनजाति व संस्कृति के तथ्य',
                'icon' => 'book',
                'is_active' => true,
                'display_order' => 3,
            ]);

            $sections = AppSection::with('categories')->orderBy('display_order', 'asc')->get();
        }

        return view('admin.sections.index', compact('sections'));
    }

    public function storeSection(Request $request)
    {
        $validated = $request->validate([
            'section_key' => 'required|string|alpha_dash|unique:app_sections,section_key',
            'name' => 'required|string|max:100',
            'hindi_name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer',
        ]);

        $section = new AppSection();
        $section->section_key = Str::slug($validated['section_key']);
        $section->name = $validated['name'];
        $section->hindi_name = $validated['hindi_name'];
        $section->description = $validated['description'];
        $section->icon = $validated['icon'] ?: 'folder';
        $section->is_active = true;
        $section->display_order = $validated['display_order'] ?? 0;
        $section->save();

        return redirect()->route('admin.sections.index')->with('success', 'नया सेक्शन सफलतापूर्वक बनाया गया!');
    }

    public function toggleSection(AppSection $section)
    {
        $section->is_active = !$section->is_active;
        $section->save();
        return redirect()->route('admin.sections.index')->with('success', 'सेक्शन की स्थिति अपडेट कर दी गई!');
    }
}
