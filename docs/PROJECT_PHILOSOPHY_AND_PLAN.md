# CGJobs — Project Philosophy & Development Plan

**Status:** Living project document  
**Purpose:** Preserve the project's ideas, architecture, priorities, constraints, and long-term direction so future development does not lose the original intent.

---

## 1. Vision

CGJobs is not intended to be merely another job-listing website. It is an automated, structured employment-information ecosystem focused initially on Chhattisgarh recruitment and current affairs.

The core objective is to collect fragmented recruitment information from the web, turn it into reliable structured data, give administrators control over what is published, and distribute the resulting information through multiple channels.

The system should become increasingly automated without becoming dependent on expensive recurring AI usage.

---

## 2. Core Philosophy

### 2.1 Automate discovery, not responsibility

The system should do the repetitive work:

- discover recruitment posts
- crawl source websites
- follow pagination where appropriate
- extract article details
- identify job-related pages
- remove duplicates
- normalize titles and content
- classify jobs
- extract dates, vacancies and links
- preserve useful source media

But publication should remain controllable by the administrator.

**Principle:**

> Machine collects → machine prepares → human controls → machine distributes.

---

### 2.2 Primary sources are authoritative

Sources must eventually be classified into **Primary** and **Secondary** sources.

#### Primary sources

Primary sources are official or authoritative government sources. They are the highest-trust reference for recruitment information.

Current planned Chhattisgarh primary sources include:

#### CGPSC

- Advertisements: https://psc.cg.gov.in/Advertisement.php
- Notifications: https://psc.cg.gov.in/Notifications.php

#### CGSSB (formerly CG Vyapam)

CGSSB is the current organizational identity. The existing CG Vyapam/Vyapam portal is the official web portal and must **not** be treated as a separate organization.

- Online Applications: https://vyapamcg.cgstate.gov.in/Posts?tag=ONLINEAPPLICATION
- Results: https://vyapamcg.cgstate.gov.in/Post?PostID=RESULT

Historical names such as `CG Vyapam`, `CGVYAPAM`, and `Chhattisgarh Vyapam` should be treated as aliases/search terms for CGSSB, not separate organizations.

#### e-Rojgar

- District Recruitments: https://erojgar.cg.gov.in/LandingSite/en/Dist_Recruitments.aspx

The e-Rojgar source is particularly relevant for district-level recruitments.

---

### 2.3 Secondary sources are discovery and enrichment sources

Secondary sources include job aggregators, blogs and other recruitment-information websites.

The first major secondary-source implementation is **JobsKind.com**.

Secondary sources are valuable because they can:

- discover recruitment information quickly
- make difficult-to-read official information easier to discover
- provide additional context
- provide source images/media
- act as an early discovery signal

However, secondary sources are not the final authority when an official primary source is available.

**Principle:**

> Primary source = authority.  
> Secondary source = discovery/enrichment.  
> CGJobs = structured aggregation, verification and distribution.

---

## 3. CGSSB / CG Vyapam Naming Rule

Do not create separate organization categories for CGSSB and CG Vyapam.

Internally the canonical organization/category should be:

`CGSSB`

Suggested aliases:

- CG Vyapam
- CGVYAPAM
- Chhattisgarh Vyapam
- Vyapam

This is important for search, classification and especially deduplication. A secondary article referring to “CG Vyapam recruitment” may describe the same recruitment represented by an official CGSSB source.

---

## 4. Source Architecture

The long-term source model should be:

```text
                         CGJobs Source Network
                                  |
                    +-------------+-------------+
                    |                           |
              PRIMARY SOURCES             SECONDARY SOURCES
                    |                           |
              CGPSC / CGSSB              JobsKind / Blogs /
              e-Rojgar / etc.            Aggregators / etc.
                    |                           |
                    +-------------+-------------+
                                  |
                           Source Crawlers
                                  |
                         Discovery + Extraction
                                  |
                           Deduplication
                                  |
                       Classification / Facts
                                  |
                              JobImport
                                  |
                         Admin Review / Edit
                                  |
                         Approve / Reject
                                  |
                              Job Record
                                  |
                  +---------------+---------------+
                  |               |               |
               Website           API           Android
                                  |
                           Notifications
```

The rest of the system should not need to know the detailed scraping logic of an individual website.

---

## 5. JobsKind.com — Reference Crawler

JobsKind is the current benchmark/reference implementation for secondary-source crawling.

The existing JobsKind crawler should be treated as a mature baseline rather than casually replaced.

It already demonstrates the desired crawler capabilities:

- HTML listing discovery
- same-host validation
- navigation/noise filtering
- job-related link detection
- deep crawling
- pagination discovery
- detail-page fetching
- title/content/date extraction
- duplicate prevention
- category detection
- department detection
- fact extraction
- apply/notification URL extraction
- source image preservation
- import staging
- automatic or approval-based publishing

Future source development should reuse the common architecture and add source-specific handling only when genuinely necessary.

---

## 6. Import Philosophy

Crawling should not directly create final public jobs in the normal approval workflow.

The preferred pipeline is:

```text
Source
  ↓
Fetch
  ↓
Parse
  ↓
Extract
  ↓
Deduplicate
  ↓
Normalize
  ↓
JobImport
  ↓
Admin filtering/review
  ↓
Edit if needed
  ↓
Approve
  ↓
Publish Job
```

`JobImport` is an important safety and quality-control layer and should be preserved.

---

## 7. Admin Panel Philosophy

The admin panel is not just a configuration screen. It is the operational control center for the crawler network.

Administrators should be able to:

- configure sources
- select source type
- control fetch frequency
- select approval or automatic publishing
- enable/disable notifications
- manually sync a source
- sync a date range
- refresh pending imports
- search imports
- filter by source
- filter by job category
- filter by department
- filter by date range
- sort imports
- edit imported jobs
- approve individual jobs
- bulk approve filtered jobs
- reject unwanted jobs

The existing filtered bulk-approval workflow should remain a core feature.

**Important principle:** bulk actions should operate on the currently selected filters, not blindly on the entire database.

---

## 8. Current Main Job Categories

The current core taxonomy is:

1. CGSSB
2. CGPSC
3. Central Govt
4. Contractual

This taxonomy should remain stable unless there is a clear product reason to change it.

Departments currently include:

- Education
- Police
- Revenue
- PHE
- PWD
- Health
- Women & Child Development
- Forest
- Agriculture
- Panchayat
- Transport
- Other Departments

Classification should prefer deterministic rules and structured source information before introducing paid AI.

---

## 9. Deduplication and Cross-Source Identity

A major future capability is recognizing that the same recruitment can appear on multiple websites.

Example:

```text
Official CGPSC Advertisement
            ↓
       Recruitment A
            ↑
         JobsKind
            ↑
      Another Blog
```

The system should eventually represent one canonical recruitment/job with multiple source references rather than creating multiple public jobs.

Primary-source information should have higher authority than secondary-source information.

Potential future concepts:

- canonical recruitment identity
- source references
- source priority
- authority level
- official domain
- organization identity
- source-specific confidence
- verification state

This should be designed carefully rather than bolted on through ad-hoc duplicate checks.

---

## 10. AI Philosophy

AI is optional infrastructure, not a requirement for every crawled article.

Automatic AI rewriting was explored previously, but recurring API costs made it unsuitable as the default ingestion strategy.

Therefore:

- do not require paid AI to crawl jobs
- do not require paid AI to classify every job
- do not require paid AI to rewrite every article
- prefer deterministic parsing, normalization and rule-based extraction
- use free services where practical and reliable
- reserve AI for high-value optional features where the cost is justified

Possible future user-facing AI features may include:

- explain a recruitment notification
- answer questions about a job
- summarize a long official notification on demand
- compare eligibility requirements

These are separate from the automatic crawler pipeline.

---

## 11. Queue and Reliability Philosophy

Crawling and scraping can be slow and must not block web requests.

The architecture should therefore preserve background processing:

```text
Admin / Scheduler
       ↓
Dispatch Queue Job
       ↓
Database Queue
       ↓
Worker
       ↓
Fetch / Parse / Import
```

Do not reintroduce large synchronous scraping operations into normal HTTP requests merely for convenience.

The scheduler can run frequently, but each source should be synced according to its configured frequency.

---

## 12. Low-Cost Infrastructure Principle

The system should maximize automation while minimizing recurring operational cost.

Prefer:

- Laravel
- database queues
- deterministic parsers
- rule-based normalization
- free/open web standards such as RSS where available
- source HTML/JSON/REST interfaces where available
- free translation alternatives when sufficiently reliable
- server-side deterministic poster generation

Paid APIs should be introduced only when their value clearly exceeds their recurring cost.

---

## 13. Distribution Philosophy

The crawler should create structured data once and make that data reusable everywhere.

A published job should be capable of powering:

- public website
- REST API
- Android app
- search
- category pages
- department pages
- notifications
- posters/social outputs
- future applications

The database and API are therefore more important than any single UI.

---

## 14. Development Strategy

Development should proceed incrementally.

### Phase 1 — Mature the secondary-source engine

- Keep JobsKind as the reference crawler.
- Fix edge cases without breaking its proven behavior.
- Generalize reusable extraction logic.
- Improve import quality and deduplication.

### Phase 2 — Build primary-source crawlers

Prioritize:

1. CGPSC Advertisements
2. CGPSC Notifications
3. CGSSB/Vyapam Online Applications
4. CGSSB/Vyapam Results/status pages where useful
5. e-Rojgar District Recruitments

Each should be treated as an authoritative primary source.

### Phase 3 — Cross-source intelligence

- Match secondary discoveries to primary records.
- Prevent duplicate public jobs.
- Prefer primary facts when conflicts occur.
- Preserve useful secondary enrichment.

### Phase 4 — Expand source network

Add other government departments and high-value secondary sources using the same architecture.

### Phase 5 — Advanced user ecosystem

Expand search, alerts, Android features, notification intelligence, analytics and optional on-demand AI features.

---

## 15. Rules for Future Development

Before changing the crawler or source system, ask:

1. Does this preserve the Primary vs Secondary source philosophy?
2. Does this improve the common crawler without damaging JobsKind?
3. Does this preserve the JobImport review layer?
4. Does this preserve admin filtering and bulk approval?
5. Does this avoid unnecessary recurring AI/API cost?
6. Does this work through the queue architecture?
7. Does this improve structured data rather than only the UI?
8. Can the feature be reused by future sources?
9. Does it improve source authority/verification rather than blindly trusting aggregators?
10. Will the change make future automation easier rather than create another special case?

If the answer is no, reconsider the implementation before proceeding.

---

## 16. Current Direction

The immediate development direction is **not a wholesale rewrite**.

The current goal is:

> **Use the nearly perfected JobsKind crawler and its Admin import/filter/review workflow as the foundation for a robust multi-source job intelligence system, then add authoritative primary-source crawlers and cross-source verification.**

The first primary-source targets are CGPSC, CGSSB (formerly CG Vyapam), and e-Rojgar.

---

## 17. Living Document

This document should be updated when a major architectural decision, source strategy, product philosophy, or cost constraint changes.

It is intentionally separate from implementation-specific documentation so that code can evolve without losing the project's original direction.
