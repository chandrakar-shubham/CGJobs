# CGJobs / CGSSB — Project Documentation

**Purpose:** This document is the source-of-truth handoff for developers and AI coding assistants (including Google AI Studio). Read it before making changes to this repository.

**Repository:** `chandrakar-shubham/CGJobs`
**Primary branch:** `main`
**Last documented:** 2026-09-09

---

## 1. Critical Safety Rules

### NEVER BREAK JOBS
The existing Jobs system is production functionality and is intentionally isolated from the new News/AI system.

**DO NOT refactor, replace, rename, or casually modify:**
- `laravel-backend/app/Models/Job.php`
- `laravel-backend/app/Http/Controllers/Api/JobApiController.php`
- existing Jobs APIs such as `/api/jobs`
- existing Jobs-backed `/api/news` and `/api/v1/news`
- Job-specific logic in `JobManagementController`
- existing Jobs routes and route names

The project rule is: **ADD NEWS. NEVER REFACTOR JOBS.**

In particular, do not make the new News engine reuse or replace the Jobs model/API.

### Existing route names that must remain intact
Some Jobs admin route names were accidentally changed during development and were restored. Never rename these:
- `admin.jobs.quality`
- `admin.jobs.duplicates`
- `admin.jobs.import-sync`

---

## 2. Project Vision

CGJobs / CGSSB is evolving into an AI-assisted content platform with two independent content domains:

1. **Jobs** — existing, stable production system.
2. **News / Current Affairs** — new isolated system with its own model, APIs, ingestion, AI processing, review, and publishing workflow.

The long-term AI Content Engine is designed to:
- fetch content from multiple sources;
- normalize and deduplicate locally without AI;
- process News into mobile/app + detailed website + SEO content;
- process Jobs while preserving factual job fields;
- save every successful item immediately;
- allow admin preview/edit/review before publication;
- optionally auto-publish News;
- use minimum AI/API requests through batching;
- support provider fallback;
- keep API keys configurable in admin and never expose them to clients or source control.

---

## 3. Current Architecture

### Jobs
Existing Jobs architecture remains authoritative for Jobs. Do not route Jobs through the new News code.

### News
The isolated News architecture consists of:

`NewsProviderEngine` → `NewsIngestionService` → `News` model → `AiContent` / News Content Engine → Admin News Studio → Publish channels

News has separate APIs:
- `/api/current-affairs`
- `/api/current-affairs/{id}`
- `/api/v1/current-affairs`
- `/api/v1/current-affairs/{id}`

The old Jobs-backed endpoints remain unchanged:
- `/api/news`
- `/api/v1/news`

Do not confuse the two.

---

## 4. News Model

File: `laravel-backend/app/Models/News.php`

News fields include:
- `title`, `title_en`
- `summary`, `summary_en`
- `content`, `content_en`
- `source`
- `source_url`
- `original_url`
- `image_url`
- `category`, `category_en`
- `tags`
- `language`
- `published_at`
- `exam_relevance`
- `featured`
- `status`
- `published_web`
- `published_mobile`

Casts include tags array, published_at datetime, exam_relevance integer, featured boolean, and web/mobile booleans.

`scopePublishedOn($channel)` filters by web/mobile publication state.
`toApiArray()` exposes channel publication fields.

The News migration also migrated existing published News records so both channels initially reflect the previous published state.

---

## 5. News Provider Engine

File: `laravel-backend/app/Services/NewsProviderEngine.php`

Current provider priority:

1. **NewsData** — when configured; live provider.
2. **Official NewsAPI** — when configured; live provider.
3. **Current RSS fallback** — Google News / Google Trends based feeds.
4. **Saurav NewsAPI mirror** — final documented fallback / compatibility source.

Important: the Saurav mirror is a static public mirror and has been observed to contain old data (for example 2022 data). It must NOT be described as guaranteed current news.

### Supported provider fields
The normalizer accepts both:
- `url`
- NewsData's documented `link`

This compatibility is important. Do not remove it.

### Provider normalization
Provider results are normalized into the News ingestion shape, including title, description/summary, source, URL, image URL, publication time, category and content where available.

### Image rules
Google-hosted logo/placeholder images are rejected. The article is retained even if no usable image exists.

Blocked image hosts include Google image/asset domains such as:
- `googleusercontent.com`
- `gstatic.com`
- `google.com`
- `google.co.in`
- `google.co.uk`

Do not discard an otherwise valid article only because its image is unusable.

### Geographic/topic/date filters
News fetching supports filters such as:
- topic
- geography (default Chhattisgarh)
- source
- from date
- to date

Current provider normalization and RSS fallback apply the requested filters. Chhattisgarh filtering uses local relevance keywords where needed.

---

## 6. RSS Fallback

File: `laravel-backend/app/Services/NewsRssFallback.php`

RSS fallback uses Google News RSS and Google Trends RSS with topic-specific queries covering areas such as:
- national
- international
- economy
- environment
- science
- defence
- polity
- education
- sports
- awards
- reports
- important days
- Chhattisgarh
- CGPSC
- CG Vyapam

The RSS pipeline has been hardened because generic Google News cards were previously being stored as articles.

### Current RSS safeguards
- Uses RSS `content:encoded` as a description/content fallback.
- Extracts media content / thumbnails.
- Rejects Google-hosted images.
- Fetches publisher-page metadata when available.
- Supports JSON-LD metadata fallback.
- Falls back to real article `<img>` tags when appropriate.
- Extracts article/main/paragraph text when metadata is missing.
- Rejects generic Google News feed items such as a title that is effectively just `Google News`.
- Does not use generic Google News metadata as the article description.
- Continues across feeds to reach the requested number of unique usable articles.

### Known area for future improvement
Some RSS HTML image extraction paths may still need improvement for relative image URLs. If fixing this, use the actual final publisher URL as the base; do not use a dummy domain such as `https://example.com/`.

If an article has no real description after all extraction methods, prefer skipping it over storing the title as its description.

---

## 7. News Ingestion Service

File: `laravel-backend/app/Services/NewsIngestionService.php`

The service delegates source fetching to `NewsProviderEngine` and retains News-only creation logic.

Important behavior:
- limit is constrained to a safe maximum;
- filters are normalized;
- duplicates are detected locally;
- duplicate checks are case-insensitive for title and also compare original/source URLs;
- fetched News records are created independently from AI processing;
- AI processing can be requested after ingestion, but fetching itself does not have to invoke AI.

The duplicate URL logic must support normalized provider URLs and the NewsData `link` field.

---

## 8. News Studio Workflow

The intended admin workflow is:

1. Fetch a batch of News, e.g. 15 or 20.
2. Select only the desired articles.
3. Delete unwanted fetched articles if needed.
4. Add selected items to the persistent **AI Batch**.
5. Fetch more News later.
6. Keep the existing AI Batch while adding new selections.
7. Repeat until the exact desired AI Batch size is reached (e.g. 51).
8. Process the entire AI Batch.
9. Review generated content.
10. Publish selected items.
11. Published items appear in **All News**.

The AI Batch is stored server-side in session as `news_ai_batch`.

### Important batch behavior
If an AI batch partially fails:
- successful items are removed from the pending batch;
- failed/unprocessed items remain in the batch;
- the error is shown for diagnosis/retry;
- if all items fail, the batch is retained and the latest AI error is surfaced.

Do not clear failed batches automatically.

---

## 9. News Studio Stages / Controls

The News admin UI includes concepts for:

### Fetched / Review Queue
- Select All
- Add Selected to AI Batch
- Delete Selected
- Clear Review Queue

### Persistent AI Batch
- show batch count
- Clear AI Batch
- Process Entire AI Batch

### AI Processed News
Shows generated:
- image
- mobile/app content
- website content
- SEO information
- category
- View/Edit/Delete controls
- multi-select
- Publish Selected

### All News
Supports:
- archive
- edit
- unpublish/archive behavior
- publish to website/mobile/app channels
- channel-specific publication flags on the same News record

No separate duplicate News record should be created for mobile versus website publication.

---

## 10. AI Content Engine — Overall Design

The AI Content Engine was introduced as a separate system with its own database tables, provider settings, usage logging, and content records.

### Database models
- `AiContent`
- `AiProviderSetting`
- `AiUsageLog`

### AiContent
Stores:
- source_type
- source_id
- status
- source_snapshot JSON
- generated_content JSON
- input_tokens
- output_tokens
- attempts
- error_message
- processed_at
- published_at

There is a unique `(source_type, source_id)` constraint.

Statuses are designed around:
`pending → processing → generated → validated → ready → published`
with failure/retry/review states as needed.

### AiProviderSetting
Includes:
- provider
- encrypted API key
- model
- fallback provider
- encrypted fallback API key
- fallback model
- enabled
- daily request limit
- daily token limit
- max items per request
- monthly budget
- auto-publish News
- auto-publish Jobs

API keys are encrypted and hidden from normal model serialization. Admin UI should display only masked keys.

**Never log API keys. Never send them to Android/mobile clients. Never commit them to Git.**

### AiUsageLog
Tracks:
- provider
- model
- source type
- item count
- input tokens
- output tokens
- fallback usage
- success/error
- error message
- timestamps

---

## 11. News AI Content Engine

File: `laravel-backend/app/Services/AI/NewsContentEngine.php`

This is News-only. **Do not use it to replace or refactor the Jobs ContentEngine.**

Current primary provider behavior:
- Gemini
- default model: `gemini-3.8-flash`
- authentication via `x-goog-api-key`
- structured JSON response format
- thinking level `low`
- no legacy `temperature`, `topP`, `topK`, or `candidate_count` settings

The current Gemini request intentionally keeps the primary response format simple to reduce 400 errors; local validation remains authoritative.

If Gemini returns HTTP 400 for response-format configuration, the News engine retries once without `responseFormat`. If that also fails, configured fallback provider is used.

### Gemini response parsing
The parser:
- ignores Gemini thought parts;
- combines non-thought text parts;
- handles fenced JSON;
- validates the decoded result;
- preserves provider error details.

### Per-item persistence
Successful results are saved immediately into `AiContent`. One successful item must not be lost because another item failed.

### Auto-publish
News channels can be auto-published according to provider/admin configuration.

---

## 12. Original Jobs ContentEngine — KEEP SEPARATE

File: `laravel-backend/app/Services/AI/ContentEngine.php`

This is the original/general engine used by the existing system and must not be casually rewritten.

Known behavior includes:
- enabled provider setting lookup;
- configurable batch size capped for safety;
- Gemini smaller batch size;
- daily request/token checks;
- primary provider + configured fallback;
- result validation;
- AiContent persistence;
- usage logging.

The Jobs pipeline has stricter factual requirements. AI must never invent or alter factual fields such as:
- vacancies
- dates
- eligibility
- salary
- official URL
- department
- other authoritative job data

If Jobs AI is changed in the future, make the smallest isolated change possible and explicitly verify the Jobs API and existing job tests.

---

## 13. Gemini Model Migration

The project moved the News engine to the current stable Gemini model:

`gemini-3.8-flash`

The migration was based on current Google documentation. Important migration rules include:
- use model ID `gemini-3.8-flash`;
- use `thinkingLevel` instead of legacy `thinking_budget`;
- remove unsupported legacy generation fields such as temperature/top-p/top-k/candidate-count where required by the current model;
- use current structured-output configuration;
- authenticate with `x-goog-api-key`.

Do not blindly copy older Gemini examples into this project.

---

## 14. Provider History / Important Fixes

### Gemini 400 issue
The admin originally showed all News AI items failing. Google AI Studio showed HTTP 400 failures.

The fix included:
- moving Gemini API key authentication to the `x-goog-api-key` header;
- using the correct JSON response MIME configuration;
- reducing Gemini batch size;
- preserving provider error bodies;
- adding tests.

### News Gemini mapping bug
A News result mapping issue was fixed so result IDs are keyed correctly and generated content is saved against the correct News item.

### News ingestion compatibility bug
NewsData uses `link` for article URLs in its documented response shape. The provider normalizer now accepts both `url` and `link`.

### Provider fallback priority bug
The provider engine originally allowed the static Saurav mirror to win before RSS in some cases. This caused tests and fallback behavior to receive stale/unrelated mirror data.

Current priority is:
**NewsData → official NewsAPI → current RSS → Saurav mirror**.

### Generic Google News bug
Google News RSS metadata was sometimes being stored as if it were the real article. The RSS pipeline now rejects generic Google News entries and attempts publisher-page enrichment.

### Google logo image bug
Google-hosted logos were being saved as article images. These are now rejected, while the article itself is preserved if valid.

---

## 15. SauravKanchan/NewsAPI Reference

Repository: `SauravKanchan/NewsAPI`

Documented static mirror structure includes:
- `/top-headlines/category/<category>/<country_code>.json`
- `/everything/<source_id>.json`
- `/sources.json`

Base mirror:
`https://saurav.tech/NewsAPI/`

It has useful NewsAPI-shaped fields such as:
- source
- author
- title
- description
- url
- urlToImage
- publishedAt
- content

**Important:** the public mirror has been observed to serve old/static data. It is a compatibility fallback, not a reliable source of current 2026 news.

---

## 16. Admin Dashboard / Navigation

A dedicated News dashboard exists at:
`/admin/news/dashboard`

It provides visibility into concepts including:
- total News
- today's News
- fetched/review count
- processed count
- published count
- archived count
- failed count
- website live count
- mobile live count
- category/source distributions
- recent fetched/published News
- current AI batch count
- AI settings
- pipeline status

The admin sidebar has grouped sections such as:
- JOBS
- CONTENT
- AUTOMATION
- SYSTEM
- News subgroup

Jobs sidebar functionality must remain intact.

---

## 17. Scheduled Ingestion

Scheduled News ingestion is fetch-first and currently uses `process=false` so that scheduled fetching does not unexpectedly consume AI quota or bypass the review workflow.

The project includes a News ingestion command and scheduler integration.

The intended pipeline is:
`Scheduled fetch → save News → review/AI batch → process → preview → publish`

Auto-publish may be enabled according to settings, but it must remain a News-only behavior unless explicitly changed.

---

## 18. Testing / CI

There are automated tests for the AI Content Engine and News provider behavior.

Important News provider tests cover:
- documented Saurav sports endpoint structure;
- article field preservation;
- rejection of Google-hosted images;
- NewsData `link` compatibility;
- provider deduplication;
- RSS fallback behavior.

Important News Gemini tests cover:
- Gemini 3.8 Flash URL/model;
- `x-goog-api-key` header;
- response format MIME type;
- no unsupported legacy generation parameters;
- low thinking level;
- generated content persistence;
- Gemini 400 retry without response format;
- thought-part ignoring;
- Groq fallback.

The repository has GitHub Actions for AI Content Engine tests and Hostinger production synchronization.

Never declare a deployment/change complete merely because code was committed. Verify relevant CI workflow results first.

---

## 19. Hostinger Deployment

The production system is hosted on Hostinger.

There is a GitHub Actions workflow that synchronizes the production branch to Hostinger.

Important distinction:
- **code deployment** = GitHub/Hostinger synchronization;
- **content auto-publishing** = publishing News/Jobs content from the application.

Do not confuse these two concepts.

Previously, installation was performed through an `install.php` flow and then the admin panel was available at `/admin`. Preserve the existing deployment architecture unless explicitly asked to redesign it.

---

## 20. Future AI Processing Requirements

The target AI Content Engine should eventually support:

### News output in one AI request
For each News item, return:
- mobile/app short article
- detailed website article
- SEO title
- SEO description
- focus keyword
- secondary keywords
- slug
- OG title
- OG description
- image alt text
- schema-ready information
- Hindi/English content where required
- exam relevance/category/tags where applicable

### Jobs output
AI may rewrite/explain job information, but factual fields must remain backend-authoritative.

### Batch efficiency
Use batches such as 20/50/100+ where provider limits and token budgets allow. Use smart batch sizing and token estimation.

Do not send AI requests for tasks that can be done deterministically:
- duplicate detection
- HTML cleaning
- normalization
- hashing
- validation of required fields
- basic categorization based on explicit rules when possible

### Failure isolation
A provider failure should not cause already successful items to be lost. Persist results item-by-item and retain failed items for retry/review.

---

## 21. Development Rules for AI Coding Assistants

Before modifying code:
1. Identify whether the change is News, Jobs, shared infrastructure, or deployment.
2. If News-only, keep the change inside News-specific files where possible.
3. Search the repository before changing routes/controllers/models.
4. Do not replace working Jobs code just to make News easier.
5. Do not rename existing Jobs routes.
6. Preserve backward compatibility for `/api/news` and `/api/v1/news`.
7. Add tests for new News behavior.
8. Run relevant tests.
9. Check GitHub Actions.
10. Check Hostinger synchronization when the change is production-bound.

### Golden rule
> **If a proposed News change requires modifying Job.php, JobApiController.php, `/api/jobs`, or the Jobs-backed `/api/news`, stop and redesign the change.**

---

## 22. Current Known Issues / Watchlist

These are known areas that may need future work, but should not be “fixed” by touching Jobs:

1. RSS relative-image URL handling should use the real publisher URL as the base.
2. Articles with no real description should preferably be skipped rather than storing a title as description.
3. The Saurav mirror is stale/static and should not be treated as current-news authority.
4. Old bad News records already stored in the database are not automatically repaired by ingestion fixes; cleanup should be an explicit admin/data task.
5. RSS publisher-page enrichment may be slower because it may fetch article pages individually.
6. News Studio should remain separate from Jobs Studio.

---

## 23. Key Git History

Important milestones include:

- `2a6076c` — News model foundation.
- `36526ec` — News migration.
- `f8d0742` — isolated News API controller.
- `7dab2ad` — isolated Current Affairs API routes.
- `ac38c4e547acca0aae1be819b9720a0a2be3d76d` — merged AI Content Engine V1.
- `e725c56b6abb50a7ae1e8258768cbc9f3a1763c0` — Gemini 400/auth/request fixes.
- `6da2a8cd9baa80bfeb2d63bb421c76bb6ed3a3c2` — News Studio bulk-delete/review controls.
- `d122f1704ddb4c770a4d251eb57dad23e300a555` — News Studio selection/delete/queue UI.
- `39df15d2177e75870f1c8e2128098cf3a4239a56` — retain failed News AI batches for retry.
- `af10cdf2440801f8ad4e8154c4d1551d4503abcf` — News Gemini engine/result mapping.
- `8ccf1f2f6498a52108afd800010da00c024d57c1` — robust News Gemini fallback tests.
- `53c5db38931d52072f0fca86ee52b45fa4d718c2` — NewsProviderEngine tests.
- `0ae56bbf78e0d741c86ed3b6867d9ab12fafef25` — RSS descriptions/images fixes.
- `f7b75ef85355d958f2f457cbb6841dc0b7ef4a3d` — provider priority + NewsData `link` compatibility + RSS filter fix.

Use the actual Git history and current files as the final authority if this document and code ever disagree.

---

## 24. Handoff Checklist for Google AI Studio

When continuing development, Google AI Studio should first read this file and then inspect the relevant current code.

For News work:
- [ ] Read `DOCUMENTATION.md`.
- [ ] Inspect `NewsProviderEngine.php`.
- [ ] Inspect `NewsRssFallback.php`.
- [ ] Inspect `NewsIngestionService.php`.
- [ ] Inspect `News.php` and News migration.
- [ ] Inspect `NewsContentEngine.php`.
- [ ] Inspect `NewsStudioController` and News Studio views.
- [ ] Inspect News routes.
- [ ] Inspect relevant tests.

For Jobs work:
- [ ] Treat Jobs as production-stable.
- [ ] Do not refactor Jobs to implement News features.
- [ ] Preserve existing APIs/routes/models.
- [ ] Run Jobs tests if a shared change genuinely affects them.

For any production change:
- [ ] Commit to the intended branch.
- [ ] Run tests.
- [ ] Verify GitHub Actions.
- [ ] Verify Hostinger sync.
- [ ] Report the commit SHA and test/deployment result.

**End of documentation.**
