# CGJobs — AI Studio Project Architecture & Integration Rules

> **Purpose:** This document is the authoritative handoff guide for Google AI Studio, ChatGPT/Codex, or any other AI coding assistant working on CGJobs. Read this before changing code, deployment, database, API, or Android integration.
>
> **Core rule:** Do not infer architecture from old files, old screenshots, or old deployment guides. The current GitHub `main` branch and the rules in this document are the source of truth.

## 1. Project identity

- Project: **CGJobs**
- GitHub repository: `chandrakar-shubham/CGJobs`
- Primary purpose: Chhattisgarh government-job/news/current-affairs ecosystem.
- Main components in the repository:
  - `laravel-backend/` — Laravel application source in the monorepo.
  - `app/` — Android application source.
  - `wordpress-site/` and `wordpress-plugin/` — WordPress public-site/integration work.
- Laravel is the central source of truth for content, database, administration, public APIs, ingestion/automation, and notifications.
- Android must consume the Laravel REST API. Android must **not scrape external job websites directly**.

## 2. CRITICAL: Git repository vs production filesystem

### GitHub repository

The monorepo keeps Laravel under:

```text
CGJobs/
├── app/                         # Android app
├── laravel-backend/             # Laravel source
├── wordpress-site/              # WordPress deployment source
├── wordpress-plugin/            # WordPress API/plugin source
└── docs/
```

### Hostinger production

Hostinger deploys the **Laravel-only `hostinger-production` branch**. That branch is created from `laravel-backend/` using Git subtree split, so Laravel files appear at the root of the deployed branch.

The actual active production Laravel directory is:

```text
/home/u318809787/domains/darkgoldenrod-camel-860943.hostingersite.com/public_html
```

That directory currently contains the Laravel root files such as:

```text
public_html/
├── app/
├── artisan
├── bootstrap/
├── composer.json
├── composer.lock
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── public/
├── .env                 # production secret; NEVER overwrite from Git
└── .hostinger-production
```

**Important:** In the current Hostinger configuration, `public_html` itself contains the deployed Laravel project root. Do not assume the old `cgjobs-backend/public_html` architecture is still active.

The Laravel `public/` directory remains the framework's public asset/entry-point directory, but Hostinger's configured deployment root is `public_html`.

### `laravel-backend` on the server

There is an older directory:

```text
/home/u318809787/domains/darkgoldenrod-camel-860943.hostingersite.com/laravel-backend
```

This is **not the active Hostinger deployment directory**. It is an old leftover copy and is not a Git working tree. Do not use it as the production deployment target and do not make routine production code changes there.

When working on production Laravel commands, use:

```bash
cd /home/u318809787/domains/darkgoldenrod-camel-860943.hostingersite.com/public_html
```

Use PHP 8.3 explicitly:

```bash
/opt/alt/php83/usr/bin/php
```

The server's default `php` command may point to PHP 8.1 and must not be used for this Laravel 11 application.

## 3. Git → Hostinger deployment architecture

Normal development flow is:

```text
Edit code in GitHub/main
        ↓
Commit to main
        ↓
GitHub Actions
        ↓
subtree split laravel-backend
        ↓
hostinger-production branch
        ↓
Hostinger deploys hostinger-production
        ↓
public_html
        ↓
production Laravel application
```

The workflow is:

```text
.github/workflows/sync-hostinger-production.yml
```

It intentionally builds a Laravel-only production branch from `laravel-backend/` and removes the first-install `install.php` from production. The `.hostinger-production` marker is used by the production Composer/deployment logic.

**Do not bypass Git for normal development.** Do not manually edit production PHP files as a substitute for committing the fix to GitHub.

## 4. Production database safety — NON-NEGOTIABLE

Production data already exists and must be preserved.

Allowed routine schema update:

```bash
/opt/alt/php83/usr/bin/php artisan migrate --force
```

Before migration, inspect:

```bash
/opt/alt/php83/usr/bin/php artisan migrate:status
```

Never use these on production:

```text
migrate:fresh
migrate:refresh
migrate:reset
rollback (unless specifically diagnosing/reverting a known migration)
db:wipe
DROP DATABASE
CREATE DATABASE
replace the production .env
regenerate APP_KEY
routine seeding
```

Do not overwrite the production `.env` from Git. Do not expose database credentials, APP_KEY, Firebase secrets, or other production secrets in commits, documentation, logs, or AI prompts.

## 5. Current production migration state

The production database was synchronized successfully on **2026-09-07**.

The previously pending Jobs migrations were successfully executed:

```text
2026_09_07_000004_add_deadline_reminder_tracking_to_jobs       DONE
2026_09_07_000005_add_job_workflow_fields                     DONE
2026_09_07_000006_add_jobs_advanced_management                DONE
```

The important consequence is that the production database now contains the workflow, analytics, deadline-reminder, SEO, job-version, and job-document schema required by the current Laravel code.

## 6. Laravel application responsibilities

Laravel is the central backend and owns:

- Admin dashboard.
- Jobs management.
- Job taxonomy and departments.
- Job import/sync system.
- News/current affairs content.
- Static GK.
- Categories/sections.
- Alerts and push notification token registration.
- Scheduled publishing.
- Deadline reminders.
- Job views/apply-click tracking.
- Job version history.
- Job documents/official links.
- SEO fields.
- REST API.
- Public web pages.
- Background queue processing.

### Job main categories

There are exactly four main job categories:

```text
CGSSB
CGPSC
Central Govt
Contractual
```

The concerned department is separate metadata. The source website is also metadata and must not replace the main category taxonomy.

## 7. API is the contract between Laravel and Android

### Production API base URL

```text
https://darkgoldenrod-camel-860943.hostingersite.com/
```

Existing `/api/*` endpoints are retained for Android backward compatibility.

Stable versioned endpoints are available under:

```text
/api/v1/*
```

**New clients and new integrations should prefer `/api/v1/*`. Existing Android code using `/api/*` must continue to work unless a deliberate API migration is implemented.**

Current API routes:

```text
GET  /api/health
GET  /api/news
GET  /api/jobs
GET  /api/news/{id}
GET  /api/sections
GET  /api/categories
GET  /api/static-gk
GET  /api/settings
GET  /api/alerts
POST /api/alerts/register-token
```

Stable equivalents:

```text
GET  /api/v1/health
GET  /api/v1/news
GET  /api/v1/jobs
GET  /api/v1/news/{id}
GET  /api/v1/sections
GET  /api/v1/categories
GET  /api/v1/static-gk
GET  /api/v1/settings
GET  /api/v1/alerts
POST /api/v1/alerts/register-token
```

The route definition is in:

```text
laravel-backend/routes/api.php
```

Do not silently rename, remove, or change existing response keys just to make Android code easier. Treat the API response format as a compatibility contract.

## 8. Jobs API behavior

`GET /api/v1/jobs` returns:

```json
{
  "success": true,
  "count": 0,
  "jobs": []
}
```

Supported useful query parameters include:

```text
category
job_category
department
query
limit
lang
```

`limit` is bounded by the backend to 1–200.

`GET /api/v1/news` returns the same general structure but uses the `news` array.

`GET /api/v1/news/{id}` returns:

```json
{
  "success": true,
  "item": { ... }
}
```

The job ID can be the public `custom_id` or numeric job ID.

## 9. Canonical Job API object

Laravel's `Job::toApiArray()` is the canonical serializer. Android and WordPress should consume these API fields rather than querying database columns directly.

Important fields include:

```text
id
language
title
titleHindi
titleEnglish
summary
shortSummary
detailedContent
category
categoryHindi
categoryEnglish
jobCategory
department
mainJobCategories
section
postType
source
webUrl
imageUrl
posterUrl
sourceImageUrl
bodyImageUrls
imageSource
publishedAt
relativeTime
isBreaking
isNew
vacancies
salary
eligibility
ageLimit
selectionProcess
officialNotificationUrl
applyUrl
originalApplyUrl
importantDates
```

`applyUrl` is intentionally a CGJobs tracking URL generated by Laravel when an original `apply_url` exists. `originalApplyUrl` contains the original official application URL. Do not bypass the tracking URL in Android UI unless there is a deliberate product decision to do so.

The public API excludes non-published workflow states. Android must therefore not attempt to implement its own draft/scheduled/archived filtering logic as a replacement for the backend.

## 10. Android backend connection

Android source is under:

```text
app/
```

Current application ID:

```text
com.aistudio.cgjobs.k8m2px
```

Android API configuration is centralized in:

```text
app/src/main/java/com/example/data/api/ServerConfig.kt
```

The current live server URL is:

```text
https://darkgoldenrod-camel-860943.hostingersite.com/
```

`ServerConfig` supports a configurable server URL, but the live production default must remain the CGJobs Hostinger backend unless the backend migration is intentionally planned and tested.

Retrofit is used for the API client. Moshi is used for JSON conversion.

### Android integration rules

1. Do not hard-code API URLs throughout screens/view models.
2. Use `ServerConfig` and the central API service.
3. Do not scrape job websites from Android.
4. Do not duplicate backend business rules unnecessarily in Android.
5. Do not rename JSON fields without updating the API contract deliberately.
6. Preserve Hindi/English support using the API `lang` behavior.
7. Treat network/API failures gracefully and never replace live API data with fake/mock production data.
8. Before changing API response models, inspect both Laravel serializer and Android models/usages.
9. Test `/api/v1/health` and `/api/v1/jobs` after backend API changes.

## 11. Backend content pipeline

Canonical content flow:

```text
External Sources
      ↓
JobSource
      ↓
JobSourceService
      ↓
Fetch / Parse / Extract
      ↓
JobImport
      ↓
Admin review/edit or configured auto-publish
      ↓
Job
      ↓
Website + REST API + Android + notifications
```

The scheduler checks source frequency and queues background work. Background processing exists specifically to avoid long synchronous scraping requests and 504 errors.

Do not reintroduce synchronous scraping into normal web requests.

## 12. AI policy

The automatic content pipeline should not depend on a paid AI API for every imported item.

AI can be optional/user-triggered where appropriate. The system should remain functional when an AI provider/API key is unavailable.

Do not add a mandatory paid AI API dependency to the ingestion path without explicit approval.

## 13. Notifications

Laravel owns notification generation and alert records.

Android registers its push token through:

```text
POST /api/v1/alerts/register-token
```

Scheduled publishing and deadline reminders are backend responsibilities.

Do not move these responsibilities into Android merely to avoid changing the backend.

## 14. Public website

Current production site:

```text
https://darkgoldenrod-camel-860943.hostingersite.com/
```

Admin:

```text
https://darkgoldenrod-camel-860943.hostingersite.com/admin
```

The public website is Laravel-backed and must consume the same central database/business rules as the API.

WordPress is a separate public-site integration project. It must connect to Laravel through the stable `/api/v1/*` contract rather than creating a second source of truth.

## 15. WordPress integration

WordPress is being prepared on a separate temporary domain because the current Hostinger setup does not permit the desired direct subdomain arrangement.

The long-term intention is to transfer the WordPress site to the desired main domain later.

WordPress must consume Laravel data through `/api/v1/*` and must not duplicate the job database.

WordPress deployment uses a separate production branch/workflow so that WordPress core is not accidentally replaced by the repository's Laravel deployment.

## 16. What an AI coding assistant MUST do before changing anything

Before editing:

1. Inspect the relevant current file(s) from GitHub `main`.
2. Inspect related models/controllers/routes and Android API models/usages when an API change is involved.
3. Check whether the feature already exists elsewhere before adding a duplicate implementation.
4. Preserve existing API compatibility.
5. Preserve the four-category taxonomy.
6. Preserve production data.
7. Keep Git as the source of truth.
8. Prefer incremental migrations for schema changes.
9. Do not modify Hostinger manually as the normal development workflow.
10. Do not assume `laravel-backend` on the production server is active; production is currently `public_html`.

## 17. Safe deployment checklist

After a Laravel code change:

```text
1. Change code in repository
2. Commit to main
3. Confirm GitHub Actions syncs hostinger-production
4. Confirm Hostinger deployment completes
5. If DB schema changed:
      php artisan migrate:status
      php artisan migrate --force
6. Clear/cache Laravel as appropriate:
      php artisan optimize:clear
      php artisan config:cache
      php artisan route:cache
      php artisan view:cache
7. Test health endpoint
8. Test affected API endpoint
9. Test public website
10. Test Android integration if API changed
```

Production command location:

```text
/home/u318809787/domains/darkgoldenrod-camel-860943.hostingersite.com/public_html
```

Production PHP:

```text
/opt/alt/php83/usr/bin/php
```

## 18. Rollback philosophy

Git is the rollback source. Keep production deploys tied to identifiable Git commits.

If a deployment breaks production:

- identify the last known-good commit;
- inspect the deployment diff;
- revert/fix through Git;
- redeploy;
- only revert database migrations when the migration is known to be safely reversible and the rollback has been explicitly planned.

Never solve a code deployment problem by deleting/recreating the production database.

## 19. Known historical trap

A previous production outage occurred because newer Laravel code reached production before its database migrations were executed. The live error was:

```text
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'workflow_status'
```

The final root cause was deployment/database schema mismatch, not a reason to rewrite the application architecture.

The correct resolution was:

```text
Deploy current Laravel files
        ↓
Verify migration files exist in public_html/database/migrations
        ↓
Check migrate:status
        ↓
Run php artisan migrate --force
```

This incident is documented here specifically so future AI assistants do not “fix” the problem by deleting data, changing the database, or creating duplicate schema logic.

## 20. Important files to inspect for API/backend work

```text
laravel-backend/routes/api.php
laravel-backend/app/Http/Controllers/Api/JobApiController.php
laravel-backend/app/Models/Job.php
laravel-backend/app/Http/Controllers/Api/AlertApiController.php
laravel-backend/app/Http/Controllers/Api/HealthApiController.php
laravel-backend/app/Http/Controllers/Api/SectionApiController.php
laravel-backend/app/Http/Controllers/Api/StaticGkApiController.php
laravel-backend/app/Http/Controllers/Api/SettingApiController.php
app/src/main/java/com/example/data/api/ServerConfig.kt
```

For deployment:

```text
.github/workflows/sync-hostinger-production.yml
laravel-backend/deploy-hostinger.php
laravel-backend/HOSTINGER_DEPLOYMENT.md
```

For WordPress integration:

```text
wordpress-plugin/cgjobs-api/
wordpress-site/
.github/workflows/sync-wordpress-production.yml
```

## 21. Final rule for Google AI Studio

**Do not redesign or relocate the backend merely because the repository is a monorepo. Do not create a second API, second job database, second Laravel installation, or second production backend.**

The intended ecosystem is:

```text
                    ┌──────────────────┐
                    │ External Sources │
                    └────────┬─────────┘
                             ↓
                    ┌──────────────────┐
                    │ Laravel Backend  │
                    │   + MySQL        │
                    │   + Admin        │
                    │   + Automation   │
                    └───────┬──────────┘
                            ↓
                     REST API /api/v1
                    ┌───────┴──────────┐
                    ↓                  ↓
             Android App        WordPress/Web
                    ↓                  ↓
               End Users          End Users
```

**Laravel + MySQL is the source of truth. API is the integration contract. Android consumes the API. WordPress consumes the API. Hostinger `public_html` is the current production Laravel deployment directory. The old server-side `laravel-backend` directory is not the deployment target. GitHub is the development source of truth.**
