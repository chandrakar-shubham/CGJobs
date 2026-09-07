# CGJobs Canonical Job Form

The manual **Create New Job**, normal **Edit Job**, and **Edit Crawled Job** screens must use the same canonical attribute vocabulary.

## Canonical attributes

| Attribute | Backend/API meaning |
|---|---|
| Job Title | `title` |
| Main Category | `job_category` — CGSSB, CGPSC, Central Govt, Contractual |
| Department | `department` |
| Post Type | `post_type` |
| Total Posts | `vacancies` |
| Organization / Source | `source` |
| Application Start Date | `application_start` |
| Application End Date | `last_date` |
| Exam Date | `exam_date` |
| Admit Card Date | `admit_card_date` |
| Result Date | `result_date` |
| Age Limit | `age_limit` |
| Salary / Pay | `salary` |
| Eligibility | `eligibility` |
| Selection Process | `selection_process` |
| Main Image URL | `image_url` |
| Source URL | `source_url` / crawler original `external_url` |
| Official Notification URL | `official_notification_url` |
| Apply URL | `apply_url` |
| Short Summary | `summary` |
| Full Article / Description | `detailed_content` |
| HOT / Breaking | `is_breaking` |
| New | `is_new` |
| Workflow | `workflow_status` |
| Scheduled At | `scheduled_at` |

## Rules

1. Do not invent a second name for an existing attribute in frontend, Android, WordPress, crawler or API code.
2. Dates use native calendar controls in Admin. The backend continues accepting legacy string date formats so existing production jobs remain safe.
3. Main image means the primary job image exposed through the canonical API serializer. It is not a separate frontend-only image.
4. Crawler edits use the same form as manual job creation. Crawler-specific original URL is retained as `external_url`.
5. Live SEO checking is an editor aid. It must not create a second SEO/content record or replace the canonical API.
6. Laravel `Job::toApiArray()` remains the canonical public serializer. Android and WordPress consume `/api/v1/*`.
7. The form is intentionally a single-page editor. Do not split the canonical fields into unrelated Media/Source, Content, or Settings tabs.
8. The UI may group fields visually into Job Details, Important Dates, Recruitment Information, Main Image & Official Links, Content, and Publishing, but these are presentation groups—not separate data models.

## Shared implementation

The shared Blade form is:

```text
laravel-backend/resources/views/admin/jobs/_canonical-form.blade.php
```

The live SEO checker is:

```text
laravel-backend/public/admin/job-form.js
```

The attribute registry is:

```text
laravel-backend/app/Support/JobFormSchema.php
```

Crawler edit/publish uses:

```text
CanonicalJobImportController
CanonicalJobImportApprovalController
```

## WordPress/API relationship

WordPress is a consumer of the Laravel API, not a second job database. The same canonical names should be mapped from API fields into WordPress presentation. Do not change API keys to match a WordPress UI label; map presentation labels to the existing canonical API fields.
