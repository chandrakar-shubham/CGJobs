# Jobs Management Center

This feature extends the existing Jobs/JobSource architecture without replacing the JobsKind crawler.

## Admin Jobs section

The sidebar now has a dedicated Jobs area:

- Jobs Dashboard
- All Jobs
- Create New Job
- Job Templates
- Expiring Soon
- Expired Jobs

## Workflow

Every job can move through:

`Draft → Scheduled → Published → Archived`

Existing records are treated as published by default. Draft, scheduled and archived jobs are hidden from the public web/API.

Scheduled jobs are published automatically every minute by `cgjobs:publish-scheduled-jobs`.

## All Jobs

All Jobs supports:

- Search by title, summary, vacancy and source
- Main category filter
- Department filter
- Source filter
- Post type filter
- Workflow status filter
- Deadline filter: open / closing within 2 days / expired
- Publication date range
- Sort by newest / oldest / title / views
- Edit, publish, republish and delete
- Bulk publish, draft, archive, HOT, NEW, notify and delete

## Job taxonomy

Every job is classified using exactly four main categories:

- CGSSB
- CGPSC
- Central Govt
- Contractual

followed by the concerned department. The source website is metadata, not the category.

## Create New Job

The form supports organization, vacancy, application dates, exam/admit-card/result dates, age, salary, eligibility, selection process, notification URL, apply URL, source URL, source image, HOT/NEW flags and workflow choice.

Common templates are provided for CGSSB, CGPSC, Central Government, Contractual, Teacher and Police recruitment.

## Notifications

Publishing/republishing and scheduled publishing can create a Firebase push notification and an Alert record. Deadline reminders run daily at 08:00 and target only published jobs whose closing date is today or within the next two days. Reminder tracking prevents duplicates and a changed closing date resets the reminder state.

## Analytics foundation

Jobs now track page views, application clicks and notification count. Application links go through a CGJobs redirect so the official URL remains unchanged while clicks are counted.

## Public safety

The public website, API and sitemap only expose published jobs. Draft/scheduled/archived records remain available to administrators for editing and historical management.

## Cost control

No paid AI rewriting is added to the automated pipeline. Existing free/structured translation and poster systems remain in place. AI can be added later as an optional manual action rather than a per-job recurring cost.

## Safety

This work is on `feature/jobs-admin-management`. The JobsKind crawler and JobImport acquisition workflow are not rewritten; the Jobs management center operates on already-created Job records.
