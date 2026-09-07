<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobImport;
use App\Services\JobImportNormalizer;
use App\Services\JobPublishNotificationService;
use App\Services\JobSourceService;

class CanonicalJobImportApprovalController extends Controller
{
    public function __invoke(JobImport $jobImport, JobSourceService $service, JobImportNormalizer $normalizer, JobPublishNotificationService $notifier)
    {
        if (is_array($jobImport->raw_payload) && !empty($jobImport->raw_payload['edited_in_admin'])) {
            $fresh = $jobImport->fresh('source');
        } else {
            $fresh = $normalizer->normalize($jobImport)->fresh('source');
        }

        $job = $service->publish($fresh);
        $facts = is_array($fresh->raw_payload['facts'] ?? null) ? $fresh->raw_payload['facts'] : [];
        $admin = is_array($fresh->raw_payload['admin_fields'] ?? null) ? $fresh->raw_payload['admin_fields'] : [];
        $job->update([
            'published_by' => $fresh->job_category ?: $job->published_by ?: 'CGSSB',
            'image_url' => $fresh->image_url ?: $job->image_url,
            'image_urls' => $fresh->image_urls ?: $job->image_urls,
            'post_type' => $admin['post_type'] ?? $job->post_type,
            'source' => $admin['source'] ?? $job->source,
            'source_url' => $fresh->external_url,
            'vacancies' => $admin['vacancies'] ?? $job->vacancies,
            'application_start' => $admin['application_start'] ?? $job->application_start,
            'last_date' => $admin['last_date'] ?? $job->last_date,
            'exam_date' => $admin['exam_date'] ?? $job->exam_date,
            'admit_card_date' => $admin['admit_card_date'] ?? $job->admit_card_date,
            'result_date' => $admin['result_date'] ?? $job->result_date,
            'age_limit' => $admin['age_limit'] ?? $job->age_limit,
            'salary' => $admin['salary'] ?? $job->salary,
            'eligibility' => $admin['eligibility'] ?? $job->eligibility,
            'selection_process' => $admin['selection_process'] ?? $job->selection_process,
            'apply_url' => array_key_exists('apply_url',$facts) ? $facts['apply_url'] : $job->apply_url,
            'official_notification_url' => array_key_exists('notification_url',$facts) ? $facts['notification_url'] : $job->official_notification_url,
            'is_breaking' => array_key_exists('is_breaking',$admin) ? (bool)$admin['is_breaking'] : $job->is_breaking,
            'is_new' => array_key_exists('is_new',$admin) ? (bool)$admin['is_new'] : $job->is_new,
        ]);
        $notifier->notify($fresh, $job->fresh());
        return back()->with('success','Job approved and published using the canonical job data.');
    }
}
