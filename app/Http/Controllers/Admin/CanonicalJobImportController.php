<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobImport;
use Illuminate\Http\Request;

class CanonicalJobImportController extends Controller
{
    public function edit(JobImport $jobImport)
    {
        $jobImport->load(['source','job']);
        $facts = is_array($jobImport->raw_payload['admin_fields'] ?? null) ? $jobImport->raw_payload['admin_fields'] : [];
        $values = array_merge([
            'title' => $jobImport->title,
            'job_category' => $jobImport->job_category,
            'department' => $jobImport->department ?: $jobImport->category,
            'post_type' => $facts['post_type'] ?? 'job',
            'vacancies' => $facts['vacancies'] ?? $jobImport->job?->vacancies,
            'source' => $facts['source'] ?? $jobImport->source?->name,
            'application_start' => $facts['application_start'] ?? $jobImport->job?->application_start,
            'last_date' => $facts['last_date'] ?? $jobImport->job?->last_date,
            'exam_date' => $facts['exam_date'] ?? $jobImport->job?->exam_date,
            'admit_card_date' => $facts['admit_card_date'] ?? $jobImport->job?->admit_card_date,
            'result_date' => $facts['result_date'] ?? $jobImport->job?->result_date,
            'age_limit' => $facts['age_limit'] ?? $jobImport->job?->age_limit,
            'salary' => $facts['salary'] ?? $jobImport->job?->salary,
            'eligibility' => $facts['eligibility'] ?? $jobImport->job?->eligibility,
            'selection_process' => $facts['selection_process'] ?? $jobImport->job?->selection_process,
            'summary' => $jobImport->summary,
            'detailed_content' => $jobImport->content,
            'image_url' => $jobImport->image_url,
            'source_url' => $jobImport->external_url,
            'external_url' => $jobImport->external_url,
            'official_notification_url' => $facts['notification_url'] ?? '',
            'notification_url' => $facts['notification_url'] ?? '',
            'apply_url' => $facts['apply_url'] ?? '',
            'is_breaking' => $facts['is_breaking'] ?? false,
            'is_new' => $facts['is_new'] ?? true,
        ], $facts);

        return view('admin.job-sources.edit-import', [
            'import' => $jobImport,
            'values' => $values,
            'crawler' => true,
            'mainCategories' => \App\Support\JobFormSchema::MAIN_CATEGORIES,
            'departments' => \App\Support\JobFormSchema::DEPARTMENTS,
        ]);
    }

    public function update(Request $request, JobImport $jobImport)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'required|string|max:2000',
            'detailed_content' => 'required|string',
            'job_category' => 'required|in:CGSSB,CGPSC,Central Govt,Contractual',
            'department' => 'required|string|max:120',
            'post_type' => 'required|string|max:40',
            'vacancies' => 'nullable|string|max:120',
            'source' => 'nullable|string|max:255',
            'application_start' => 'nullable|string|max:120',
            'last_date' => 'nullable|string|max:120',
            'exam_date' => 'nullable|string|max:120',
            'admit_card_date' => 'nullable|string|max:120',
            'result_date' => 'nullable|string|max:120',
            'age_limit' => 'nullable|string|max:120',
            'salary' => 'nullable|string|max:255',
            'eligibility' => 'nullable|string',
            'selection_process' => 'nullable|string',
            'image_url' => 'nullable|url|max:2000',
            'source_url' => 'required|url|max:2000',
            'external_url' => 'required|url|max:2000',
            'apply_url' => 'nullable|url|max:2000',
            'official_notification_url' => 'nullable|url|max:2000',
            'is_breaking' => 'nullable',
            'is_new' => 'nullable',
        ]);

        $payload = is_array($jobImport->raw_payload) ? $jobImport->raw_payload : [];
        $facts = is_array($payload['facts'] ?? null) ? $payload['facts'] : [];
        $admin = [];
        foreach (['post_type','vacancies','source','application_start','last_date','exam_date','admit_card_date','result_date','age_limit','salary','eligibility','selection_process'] as $key) $admin[$key] = $data[$key] ?? null;
        $admin['is_breaking'] = $request->boolean('is_breaking');
        $admin['is_new'] = $request->boolean('is_new');
        $admin['apply_url'] = $data['apply_url'] ?? null;
        $admin['notification_url'] = $data['official_notification_url'] ?? null;
        $payload['facts'] = array_merge($facts, ['apply_url'=>$admin['apply_url'], 'notification_url'=>$admin['notification_url']]);
        $payload['admin_fields'] = $admin;
        $payload['edited_in_admin'] = true;
        $payload['edited_at'] = now()->toIso8601String();

        $jobImport->update([
            'title'=>$data['title'], 'summary'=>$this->sanitizeRichText($data['summary']), 'content'=>$this->sanitizeRichText($data['detailed_content']),
            'category'=>$data['department'], 'job_category'=>$data['job_category'], 'department'=>$data['department'],
            'published_by'=>$data['job_category'], 'external_url'=>$data['external_url'],
            'image_url'=>$data['image_url'] ?? null, 'raw_payload'=>$payload,
        ]);

        if ($jobImport->status === 'published' && $jobImport->job) {
            $this->syncJob($jobImport->fresh('job'), $admin);
        }

        return redirect()->route('admin.job-sources.index', $request->only(['q','source_id','job_category','department','from','to','sort']))->with('success','Job data updated using the canonical Jobs form.');
    }

    private function syncJob(JobImport $import, array $admin): void
    {
        $job = $import->job;
        $job->update([
            'title'=>$import->title, 'title_en'=>$import->title, 'summary'=>$import->summary, 'summary_en'=>$import->summary,
            'detailed_content'=>$import->content, 'detailed_content_en'=>$import->content,
            'category'=>$import->department, 'category_en'=>$import->department, 'job_category'=>$import->job_category,
            'department'=>$import->department, 'published_by'=>$import->job_category ?: 'CGSSB',
            'post_type'=>$admin['post_type'] ?: 'job', 'source'=>$admin['source'] ?: $job->source,
            'source_url'=>$import->external_url, 'image_url'=>$import->image_url,
            'vacancies'=>$admin['vacancies'], 'application_start'=>$admin['application_start'], 'last_date'=>$admin['last_date'],
            'exam_date'=>$admin['exam_date'], 'admit_card_date'=>$admin['admit_card_date'], 'result_date'=>$admin['result_date'],
            'age_limit'=>$admin['age_limit'], 'salary'=>$admin['salary'], 'eligibility'=>$admin['eligibility'],
            'selection_process'=>$admin['selection_process'], 'apply_url'=>$admin['apply_url'],
            'official_notification_url'=>$admin['notification_url'], 'is_breaking'=>(bool)$admin['is_breaking'], 'is_new'=>(bool)$admin['is_new'],
        ]);
    }

    private function sanitizeRichText(string $html): string
    {
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><h2><h3><h4><ul><ol><li><a><blockquote>');
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(["\']).*?\1/i','',$html) ?? $html;
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*(?:javascript|data):.*?\2/i','$1="#"',$html) ?? $html;
        return trim($html);
    }
}
