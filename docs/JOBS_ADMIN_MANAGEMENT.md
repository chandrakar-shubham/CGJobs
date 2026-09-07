# Jobs Management Center

This feature extends the existing Jobs/JobSource architecture without replacing the JobsKind crawler.

## Product goal

CGJobs is being developed as a reliable, low-cost recruitment information ecosystem. The Jobs section is the core recruitment product: acquire information from trusted sources, normalize it, review it, publish it, notify users, track engagement, and continuously improve quality without adding recurring AI costs to every job.

## Admin Jobs section

The sidebar should keep the original Jobs roadmap intact. Core management comes first; advanced intelligence tools are an additional layer, not a replacement roadmap.

### Core Jobs roadmap

1. Jobs Dashboard
2. All Jobs
3. Create New Job
4. Drafts
5. Scheduled Jobs
6. Expiring Soon
7. Expired Jobs
8. Republished
9. Bulk Manager
10. Categories & Departments
11. Job Templates
12. Notification Center
13. Import / Sync
14. Job Analytics

### Advanced Jobs layer

These features extend individual jobs and the analytics layer:

- Job Version History
- Duplicate Detection
- Job Quality Checker
- Job Timeline
- Notification History
- Official Documents
- SEO Manager
- Advanced Analytics

The advanced layer must remain accessible without making the main Jobs navigation unnecessarily complex. Individual-job tools belong on the job edit/detail workflow; analytics tools belong under the analytics area.

## Workflow

Every job can move through:

`Draft → Scheduled → Published → Archived`

Existing records are treated as published by default. Draft, scheduled and archived jobs are hidden from the public web/API.

Scheduled jobs are published automatically every minute by `cgjobs:publish-scheduled-jobs`.

Republishing should refresh the publication metadata, create the appropriate alert/notification, and preserve the job's deadline/reminder history unless the closing date itself changes.

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

This taxonomy is intentionally stable so the website, Android app, filters, analytics, notifications and future recommendation systems all use the same classification model.

## Create New Job

The form supports organization, vacancy, application dates, exam/admit-card/result dates, age, salary, eligibility, selection process, notification URL, apply URL, source URL, source image, HOT/NEW flags and workflow choice.

Common templates are provided for CGSSB, CGPSC, Central Government, Contractual, Teacher and Police recruitment.

## Automation and acquisition strategy

The acquisition pipeline remains:

`External source → JobSource → fetch/parse/extract → JobImport → review/normalize → Job → public web/API/Android → notification`

The existing crawler and JobImport architecture must not be replaced by the admin management layer.

### Automation principles

- Prefer RSS, JSON and structured feeds where available.
- Use HTML extraction only where necessary.
- Keep source-specific parsing isolated in the source service.
- Run source synchronization in background jobs/queues so web requests do not perform slow scraping.
- Respect source frequency and avoid unnecessary repeated requests.
- Preserve useful source images and official links.
- Never publish unreviewed or obviously incomplete data simply because a crawler found it.
- Keep import and publishing separate so acquisition failures cannot corrupt public records.

## Content and AI strategy

The automated pipeline should remain cost-controlled.

- Do not call a paid LLM for every imported job.
- Prefer deterministic normalization, templates and rules for routine formatting.
- Keep the existing free/structured translation approach.
- Keep deterministic poster generation where possible.
- AI may be offered as an optional manual editor action for difficult rewriting, summarization, classification or quality improvement.
- If paid AI is introduced later, it should be usage-controlled, cache results, avoid duplicate processing, and require an explicit budget/feature switch.

The objective is to make the system useful even when paid AI services are disabled.

## Notifications strategy

Notifications should be useful rather than excessive.

- New published jobs can generate push notifications according to notification policy.
- Republished jobs can notify users when the republish is meaningful.
- Scheduled publishing should notify after publication, not before.
- Deadline reminders target published jobs closing today or within the next two days.
- Reminder state prevents duplicate deadline notifications.
- Changing a closing date resets the reminder state so the new deadline can be handled correctly.
- Notification history should remain auditable from the admin panel.

## Analytics strategy

The analytics system should answer three questions:

1. What jobs are being seen?
2. What jobs are generating application intent?
3. Which categories/sources/content strategies are performing best?

Current foundation:

- Page views
- Application clicks
- Notification counts
- Category/month aggregation

Future analytics should add, where data is available:

- Application click-through rate
- Top jobs by views and clicks
- Department performance
- Source performance
- Deadline-to-click funnel
- Publication-to-view velocity
- Notification-to-view engagement
- Trending categories
- Historical comparisons

Analytics must use real tracked events and must not invent metrics.

## Quality and trust strategy

The Jobs platform should optimize for correctness before volume.

The Job Quality Checker should identify missing or suspicious fields such as title, category, department, dates, eligibility, application URL and official notification URL.

Duplicate Detection should compare normalized titles and, over time, stronger signals such as organization, category, department, dates and source identifiers. Similar jobs should be flagged for review rather than automatically deleted.

Version History and Job Timeline should provide an audit trail for important changes, publication events and notifications.

Official Documents should keep authoritative notification links together with each job.

SEO Manager should control job-specific title, description, keywords and canonical metadata while preserving structured JobPosting data.

## Public product strategy

The public website and Android app should consume the same API/domain model so users see consistent:

- Categories
- Departments
- Job status
- Dates
- Official links
- Notifications
- Job details

Draft, scheduled and archived jobs must never leak into public listings/API/sitemap.

## Android strategy

The Android app is a first-class client, not a separate content database.

Future app improvements should prioritize:

- Fast job discovery
- Category and department filters
- Search
- Job detail pages
- Saved/favourite jobs where appropriate
- Deadline reminders
- Push notifications
- Deep links from notifications to the correct job
- Reliable offline/cache behavior for recently viewed content

The backend remains the source of truth.

## Monetization strategy

The system should first build traffic, trust and repeat usage, then monetize without degrading the recruitment experience.

Potential revenue layers, in priority order:

1. Display advertising once traffic is meaningful.
2. High-quality contextual sponsorships that do not mislead users.
3. Optional premium user features only if they provide genuine value.
4. Recruitment/education partnerships that are clearly labeled.
5. Future B2B data/reporting services only when legally and ethically appropriate.

Do not build the product around paid AI API consumption. AI is a feature, not the cost center.

## Growth strategy

Growth should come from useful, searchable and timely information.

- SEO-friendly individual job pages.
- Strong internal linking between category, department and job pages.
- Fast publication of verified recruitment information.
- Useful deadline reminders.
- Social sharing of individual jobs.
- App deep links from notifications and shared URLs.
- Consistent Hindi/English presentation.
- Structured data for search engines.
- Historical content should remain useful where legally and operationally appropriate.

## Roadmap execution order

### Phase 1 — Reliability and deployment

- Keep Git as the single source of truth.
- Deploy application code through the established Git → Hostinger workflow.
- Run incremental Laravel migrations during deployment.
- Never use destructive production commands such as `migrate:fresh` or database wipes.
- Keep production `.env` and secrets outside Git.
- Rebuild Laravel caches after deployment.

### Phase 2 — Jobs management

- Complete the core 14-item Jobs management roadmap.
- Verify every sidebar route and workflow action.
- Ensure filters and taxonomy remain consistent.
- Preserve the existing crawler/import system.

### Phase 3 — Advanced intelligence

- Strengthen version history.
- Improve duplicate detection beyond exact title matches.
- Make the quality checker actionable.
- Expand job timeline and notification history.
- Improve official document management.
- Expand SEO controls and structured metadata.
- Build advanced analytics from real events.

### Phase 4 — Public growth

- Improve SEO and internal discovery.
- Improve notification relevance.
- Improve Android discovery/deep linking.
- Measure traffic and engagement.
- Introduce monetization only after user value and traffic are established.

### Phase 5 — Optional AI layer

- Add manual AI assistance only where it materially improves editorial productivity.
- Cache generated results.
- Never make paid AI a mandatory dependency for publishing jobs.
- Keep a zero/low-cost operating mode available.

## Deployment rule

Git is the canonical source of application code. Production changes should be represented in Git and deployed through the established deployment pipeline. Database schema changes must be Laravel migrations and must be safe to run incrementally against existing production data.

## Cost control

No paid AI rewriting is added to the automated pipeline. Existing free/structured translation and poster systems remain in place. AI can be added later as an optional manual action rather than a per-job recurring cost.

## Safety

The JobsKind crawler and JobImport acquisition workflow are not rewritten; the Jobs management center operates on already-created Job records. Production data must be preserved during deployments and migrations. Public output must contain only records intended for publication.
