<?php

namespace App\Support;

final class JobFormSchema
{
    public const MAIN_CATEGORIES = ['CGSSB', 'CGPSC', 'Central Govt', 'Contractual'];

    public const DEPARTMENTS = [
        'Education','Police','Revenue','PHE','PWD','Health','Women & Child Development',
        'Forest','Agriculture','Panchayat','Transport','Other Departments',
    ];

    public const POST_TYPES = [
        'job' => 'Job Notification',
        'admit_card' => 'Admit Card',
        'result' => 'Result',
        'syllabus' => 'Syllabus',
        'answer_key' => 'Answer Key',
    ];

    public static function all(): array
    {
        return [
            'title','job_category','department','post_type','vacancies','location','source',
            'application_start','last_date','exam_date','admit_card_date','result_date',
            'age_limit','salary','eligibility','selection_process','summary','detailed_content',
            'image_url','source_url','official_notification_url','apply_url',
            'is_breaking','is_new','broadcast_push','workflow_status','scheduled_at',
        ];
    }

    public static function dateFields(): array
    {
        return [
            'application_start' => 'Application Start Date',
            'last_date' => 'Application End Date',
            'exam_date' => 'Exam Date',
            'admit_card_date' => 'Admit Card Date',
            'result_date' => 'Result Date',
        ];
    }

    public static function labels(): array
    {
        return [
            'title' => 'Job Title', 'job_category' => 'Main Category', 'department' => 'Department',
            'post_type' => 'Post Type', 'vacancies' => 'Total Posts', 'location' => 'Job Location',
            'source' => 'Organization / Source', 'application_start' => 'Application Start Date',
            'last_date' => 'Application End Date', 'exam_date' => 'Exam Date',
            'admit_card_date' => 'Admit Card Date', 'result_date' => 'Result Date',
            'age_limit' => 'Age Limit', 'salary' => 'Salary / Pay', 'eligibility' => 'Eligibility',
            'selection_process' => 'Selection Process', 'summary' => 'Short Summary',
            'detailed_content' => 'Full Article / Description', 'image_url' => 'Main Image URL',
            'source_url' => 'Source URL', 'official_notification_url' => 'Official Notification URL',
            'apply_url' => 'Apply URL',
        ];
    }
}
