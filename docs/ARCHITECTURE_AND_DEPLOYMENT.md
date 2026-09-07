# CGJobs Architecture & Production Deployment

## Target architecture

```text
                    CGJobs Laravel Core
             jobs / sources / automation / admin
                         |
                    REST API v1
              /api/v1/* (stable contract)
                    /           \
                   /             \
          WordPress website    Android app
             public/SEO          mobile

Laravel owns the database. Clients never connect directly to MySQL.
```

## Rules

1. Laravel is the single source of truth for jobs and structured recruitment data.
2. WordPress is a presentation/SEO layer and consumes Laravel over HTTPS.
3. Android consumes the same versioned API contract.
4. WordPress must never read the Laravel MySQL database directly.
5. New public integrations use `/api/v1/*`; existing `/api/*` routes remain for backward compatibility.
6. Production migrations are incremental only. Never run `migrate:fresh`, `db:wipe`, or production seed commands during deployment.
7. `.env` and `APP_KEY` are server-owned secrets and are never committed to Git.
8. `composer.lock` is authoritative in production; never run `composer update` as part of deployment.
9. Every production deployment must finish with Laravel caches rebuilt and a health check.
10. GitHub `main` remains the source of truth for application code.

## WordPress integration

The repository contains `wordpress-plugin/cgjobs-api/`, a small API bridge. Install it as a normal WordPress plugin and configure its **Laravel API base URL** to the versioned API, for example:

`https://api.example.com/api/v1`

The plugin provides `[cgjobs_jobs]` and `[cgjobs_job]` shortcodes and caches API responses briefly in WordPress. A full CGJobs WordPress theme can be built on top of this contract without changing Laravel data ownership.

## Production deployment

The current Hostinger deployment branch is generated automatically from `laravel-backend/`. The generated branch is Laravel-only and contains a `.hostinger-production` marker.

`deploy-hostinger.php` is intentionally safe for repeat deployments. It:

- uses the committed lock file;
- installs production dependencies without Composer scripts;
- runs Laravel package discovery explicitly;
- runs incremental migrations with `--force`;
- clears and rebuilds config, route and view caches;
- never creates or replaces `.env`;
- never changes `APP_KEY`;
- never wipes or seeds the production database.

If Hostinger's Git build reports a Composer lock/content-hash failure, fix `composer.json`/`composer.lock` in Git first. Do not run `composer update` on production.

## Long-term hosting direction

If Hostinger shared Git deployment cannot reliably execute the deployment runner, the Laravel core should move to an environment with a first-class deployment hook/CI runner (Hostinger VPS or another managed application platform). WordPress can remain on ordinary WordPress hosting. The API contract means this hosting move does not require rebuilding the website or Android app.
