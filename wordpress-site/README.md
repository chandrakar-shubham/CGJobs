# CGJobs WordPress Public Site

This directory is the public WordPress layer for CGJobs.

- Laravel remains the source of truth for jobs, automation, alerts and the Android API.
- WordPress communicates with Laravel through `/api/v1` over HTTPS.
- WordPress never connects directly to the Laravel database.
- The site is designed to be lightweight: one custom theme plus the small CGJobs API Bridge plugin.

## Hostinger setup

Deploy the `wordpress-production` branch to the WordPress subdomain with the web root containing `wp-admin`, `wp-content`, and `wp-includes`.

In WordPress:
1. Activate **CGJobs Worldclass**.
2. Activate **CGJobs API Bridge**.
3. Go to **Settings → CGJobs API**.
4. Set the API base URL to the Laravel production URL followed by `/api/v1`.
5. Create a page with slug `jobs`, assign the **Jobs** template, and publish it.
6. Set the static homepage to the CGJobs home page.

The API bridge caches GET responses for 120 seconds to keep the public site fast without putting load on Laravel.
