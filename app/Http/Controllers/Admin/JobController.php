<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::query();

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', $s)
                  ->orWhere('summary', 'like', $s)
                  ->orWhere('vacancies', 'like', $s);
            });
        }

        if ($request->filled('category') && $request->category !== 'All') {
            $query->where('category', $request->category);
        }

        $jobs = $query->orderBy('id', 'desc')->paginate(15);
        $categories = Category::orderBy('name')->get();

        return view('admin.jobs.index', compact('jobs', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.jobs.create', compact('categories'));
    }

    public function store(Request $request, FirebaseNotificationService $fcmService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'detailed_content' => 'nullable|string',
            'category' => 'required|string',
            'vacancies' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'age_limit' => 'nullable|string',
            'selection_process' => 'nullable|string',
            'source' => 'nullable|string',
            'source_url' => 'nullable|url',
            'official_notification_url' => 'nullable|url',
            'apply_url' => 'nullable|url',
            'image_url' => 'nullable|url',
            'application_start' => 'nullable|string',
            'last_date' => 'nullable|string',
            'exam_date' => 'nullable|string',
            'is_breaking' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'broadcast_push' => 'nullable|boolean',
        ]);

        $validated['is_breaking'] = $request->has('is_breaking');
        $validated['is_new'] = $request->has('is_new');
        $validated['published_at'] = date('Y-m-d');
        $validated['relative_time'] = 'हाल ही में';

        $job = Job::create($validated);

        // Optionally broadcast push alert
        if ($request->has('broadcast_push')) {
            $fcmService->broadcast(
                title: "नई भर्ती: " . $job->title,
                message: ($job->vacancies ? "[{$job->vacancies}] " : "") . $job->summary,
                category: $job->category,
                actionUrl: $job->apply_url ?: $job->official_notification_url,
                articleId: (string)$job->id
            );
        }

        return redirect()->route('admin.jobs.index')->with('success', 'नौकरी अधिसूचना सफलतापूर्वक प्रकाशित की गई! (Job posted successfully)');
    }

    public function edit(Job $job)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.jobs.edit', compact('job', 'categories'));
    }

    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'detailed_content' => 'nullable|string',
            'category' => 'required|string',
            'vacancies' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'age_limit' => 'nullable|string',
            'selection_process' => 'nullable|string',
            'source' => 'nullable|string',
            'source_url' => 'nullable|url',
            'official_notification_url' => 'nullable|url',
            'apply_url' => 'nullable|url',
            'image_url' => 'nullable|url',
            'application_start' => 'nullable|string',
            'last_date' => 'nullable|string',
            'exam_date' => 'nullable|string',
        ]);

        $validated['is_breaking'] = $request->has('is_breaking');
        $validated['is_new'] = $request->has('is_new');

        $job->update($validated);

        return redirect()->route('admin.jobs.index')->with('success', 'अधिसूचना सफलतापूर्वक अपडेट की गई! (Job updated successfully)');
    }

    public function destroy(Job $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.index')->with('success', 'अधिसूचना हटा दी गई (Job deleted)');
    }
}
