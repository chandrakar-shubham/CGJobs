<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Services\FirebaseNotificationService;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::query();
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', $s)->orWhere('title_en', 'like', $s)
                  ->orWhere('summary', 'like', $s)->orWhere('summary_en', 'like', $s)->orWhere('vacancies', 'like', $s);
            });
        }
        if ($request->filled('category') && $request->category !== 'All') $query->where('category', $request->category);
        if ($request->filled('section')) $query->where('section', $request->section);
        if ($request->filled('post_type')) $query->where('post_type', $request->post_type);
        $jobs = $query->orderByDesc('id')->paginate(15);
        $categories = Category::orderBy('name')->get();
        return view('admin.jobs.index', compact('jobs', 'categories'));
    }

    public function create(Request $request)
    {
        $defaultSection = $request->query('section', 'jobs');
        $categories = Category::orderBy('name')->get();
        return view('admin.jobs.create', compact('categories', 'defaultSection'));
    }

    public function store(Request $request, FirebaseNotificationService $fcmService, TranslationService $translator)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255', 'summary' => 'required|string', 'detailed_content' => 'nullable|string',
            'category' => 'required|string', 'section' => 'nullable|string|in:jobs,news', 'post_type' => 'nullable|string',
            'vacancies' => 'nullable|string', 'salary' => 'nullable|string', 'eligibility' => 'nullable|string', 'age_limit' => 'nullable|string',
            'selection_process' => 'nullable|string', 'source' => 'nullable|string', 'source_url' => 'nullable|url',
            'official_notification_url' => 'nullable|url', 'apply_url' => 'nullable|url', 'image_url' => 'nullable|url',
            'application_start' => 'nullable|string', 'last_date' => 'nullable|string', 'exam_date' => 'nullable|string',
            'is_breaking' => 'nullable|boolean', 'is_new' => 'nullable|boolean', 'broadcast_push' => 'nullable|boolean',
        ]);

        $validated['section'] = $request->input('section', 'jobs');
        $validated['post_type'] = $request->input('post_type', 'job');
        $validated['is_breaking'] = $request->has('is_breaking');
        $validated['is_new'] = $request->has('is_new');
        $validated['published_at'] = date('Y-m-d');
        $validated['relative_time'] = 'हाल ही में';

        // English is the master/source. Existing Hindi columns remain the Hindi presentation layer.
        $validated['title_en'] = $validated['title'];
        $validated['summary_en'] = $validated['summary'];
        $validated['detailed_content_en'] = $validated['detailed_content'] ?? null;
        $validated['category_en'] = $validated['category'];
        $validated['eligibility_en'] = $validated['eligibility'] ?? null;
        $validated['selection_process_en'] = $validated['selection_process'] ?? null;
        $validated['relative_time_en'] = 'Recently';
        $validated['translation_status'] = 'pending';

        $job = Job::create($validated);
        $this->translateJob($job, $translator);

        if ($request->has('broadcast_push')) {
            $fcmService->broadcast(
                title: "नई भर्ती: " . ($job->title ?: $job->title_en),
                message: ($job->vacancies ? "[{$job->vacancies}] " : "") . ($job->summary ?: $job->summary_en),
                category: $job->category,
                actionUrl: $job->apply_url ?: $job->official_notification_url,
                articleId: (string)$job->id
            );
        }
        return redirect()->route('admin.jobs.index')->with('success', 'Post published with automatic Hindi translation.');
    }

    public function edit(Job $job)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.jobs.edit', compact('job', 'categories'));
    }

    public function update(Request $request, Job $job, TranslationService $translator)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255', 'summary' => 'required|string', 'detailed_content' => 'nullable|string',
            'category' => 'required|string', 'section' => 'nullable|string|in:jobs,news', 'post_type' => 'nullable|string',
            'vacancies' => 'nullable|string', 'salary' => 'nullable|string', 'eligibility' => 'nullable|string', 'age_limit' => 'nullable|string',
            'selection_process' => 'nullable|string', 'source' => 'nullable|string', 'source_url' => 'nullable|url',
            'official_notification_url' => 'nullable|url', 'apply_url' => 'nullable|url', 'image_url' => 'nullable|url',
            'application_start' => 'nullable|string', 'last_date' => 'nullable|string', 'exam_date' => 'nullable|string',
        ]);
        $validated['section'] = $request->input('section', $job->section ?: 'jobs');
        $validated['post_type'] = $request->input('post_type', $job->post_type ?: 'job');
        $validated['is_breaking'] = $request->has('is_breaking');
        $validated['is_new'] = $request->has('is_new');
        $validated['title_en'] = $validated['title'];
        $validated['summary_en'] = $validated['summary'];
        $validated['detailed_content_en'] = $validated['detailed_content'] ?? null;
        $validated['category_en'] = $validated['category'];
        $validated['eligibility_en'] = $validated['eligibility'] ?? null;
        $validated['selection_process_en'] = $validated['selection_process'] ?? null;
        $validated['relative_time_en'] = 'Recently';
        $validated['translation_status'] = 'pending';
        $job->update($validated);
        $this->translateJob($job->fresh(), $translator);
        return redirect()->route('admin.jobs.index')->with('success', 'Post updated and Hindi translation refreshed.');
    }

    public function destroy(Job $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.index')->with('success', 'अधिसूचना हटा दी गई (Job deleted)');
    }

    private function translateJob(Job $job, TranslationService $translator): void
    {
        try {
            $translations = $translator->translateMany([
                'title' => $job->title_en,
                'summary' => $job->summary_en,
                'detailed' => $job->detailed_content_en,
                'category' => $job->category_en,
                'eligibility' => $job->eligibility_en,
                'selection' => $job->selection_process_en,
            ]);
            $job->title = $translations['title'] ?: $job->title_en;
            $job->summary = $translations['summary'] ?: $job->summary_en;
            $job->detailed_content = $translations['detailed'] ?: $job->detailed_content_en;
            $job->category = $translations['category'] ?: $job->category_en;
            $job->eligibility = $translations['eligibility'] ?: $job->eligibility_en;
            $job->selection_process = $translations['selection'] ?: $job->selection_process_en;
            $job->translation_status = $translations['title'] ? 'ready' : 'pending';
            $job->translation_error = $translations['title'] ? null : 'GOOGLE_TRANSLATE_API_KEY is not configured or translation service is unavailable.';
            $job->translated_at = $translations['title'] ? now() : null;
            $job->save();
        } catch (\Throwable $e) {
            $job->translation_status = 'failed';
            $job->translation_error = $e->getMessage();
            $job->save();
        }
    }
}
