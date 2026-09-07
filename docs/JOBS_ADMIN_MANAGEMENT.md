# Jobs Admin Management

This feature extends the existing Jobs/JobSource architecture without replacing the mature JobsKind crawler.

## Admin Jobs section

The sidebar now has a dedicated **Jobs** section with:

1. **All Jobs** — all published job records with search, main category, department, source, post type, deadline status, publication-date and sort filters.
2. **Create New Job** — complete manual job creation form.

## Job taxonomy

Every job is classified as:

- CGSSB
- CGPSC
- Central Govt
- Contractual

followed by its concerned department.

The source website is metadata, not the job category.

## Job actions

From All Jobs an administrator can:

- Modify a job
- Delete a job
- Republish a job

Republishing refreshes the publication date/new status, resets deadline-reminder tracking, and sends a push notification.

## Deadline reminders

The Laravel scheduler runs a daily deadline-reminder command. Jobs whose closing date is today or within the next two days receive one push notification. A database timestamp prevents repeated reminders. Editing the closing date resets the reminder state so a newly extended deadline can be reminded again.

The implementation supports the date formats currently encountered by the crawler, including `dd/mm/yyyy`, `dd-mm-yyyy`, ISO dates, and common month-name formats.

## Safety

This feature is implemented on a separate feature branch and does not alter the JobsKind crawler or the JobImport crawler workflow. The existing source crawler remains the acquisition layer; this feature manages already-created Job records.
