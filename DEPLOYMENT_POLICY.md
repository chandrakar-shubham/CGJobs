# CGJobs Production Deployment Policy

## Initial installation

`install.php` is an initial-installation wizard only. It may create the production `.env`, create the initial database structure, and seed initial/demo records during first setup.

It must NOT be used for normal application updates.

## Every subsequent deployment

Code changes are deployed from GitHub to Hostinger. The deployment process must:

1. Preserve the existing `.env`.
2. Preserve the existing `APP_KEY`.
3. Install Composer dependencies from `composer.lock` with `composer install` (never `composer update` in production).
4. Run `php artisan migrate --force` so only new migrations are applied.
5. Clear/rebuild Laravel caches.
6. Never run `migrate:fresh`, `db:wipe`, or the initial seeder automatically.
7. Never recreate the database or delete existing application data.

## Client/server feature updates

The Android client and Laravel server remain in the same Git repository. A feature update can change both sides in one Git commit. Server changes are deployed to Hostinger; Android changes are built/released through the Android release process.

The admin panel is part of the Laravel application. It is not reinstalled during a deployment. If an admin feature's PHP/view/assets change, those changed files are deployed like any other code change; existing database records, credentials, `.env`, and `APP_KEY` remain intact.

## Database evolution

All future schema changes must be represented by new Laravel migration files. Migrations must be additive/backward-compatible whenever possible. Do not modify old migrations that have already been executed in production; add a new migration instead.

## Secrets

Never commit `.env`, database passwords, Firebase private credentials, or API keys. Production secrets remain on Hostinger.
