<?php
/**
 * CGJobs production deployment runner for Hostinger.
 *
 * PURPOSE:
 * - Run on every code deployment AFTER the initial installation.
 * - NEVER recreates the database.
 * - NEVER replaces .env.
 * - NEVER regenerates APP_KEY.
 * - NEVER runs db:seed automatically.
 * - Runs only incremental Laravel migrations and cache rebuilds.
 *
 * IMPORTANT:
 * Configure Hostinger's Git deployment to execute this file/commands after
 * deploying the Laravel project. Delete/disable this file if your Hostinger
 * plan provides a safer private deployment command hook.
 */

$base = __DIR__;

// Prefer Hostinger PHP 8.3 CLI when available.
$phpCandidates = [
    '/opt/alt/php83/usr/bin/php',
    PHP_BINARY,
    '/usr/bin/php',
];
$php = null;
foreach ($phpCandidates as $candidate) {
    if (is_file($candidate) && is_executable($candidate)) {
        $php = $candidate;
        break;
    }
}

if (!$php) {
    fwrite(STDERR, "ERROR: PHP CLI executable not found.\n");
    exit(1);
}

$composerCandidates = [
    '/usr/local/bin/composer2',
    '/usr/bin/composer2',
    '/usr/local/bin/composer',
    '/usr/bin/composer',
];
$composer = null;
foreach ($composerCandidates as $candidate) {
    if (is_file($candidate) && is_executable($candidate)) {
        $composer = $candidate;
        break;
    }
}

function runCommand(string $command): void
{
    echo "\n>>> {$command}\n";
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        fwrite(STDERR, "Deployment stopped. Exit code: {$exitCode}\n");
        exit($exitCode);
    }
}

if (!file_exists($base . '/artisan') || !file_exists($base . '/composer.json')) {
    fwrite(STDERR, "ERROR: This script must be inside the Laravel project root.\n");
    exit(1);
}

if (!file_exists($base . '/.env')) {
    fwrite(STDERR, "ERROR: .env is missing. Run the initial installer first. Deployment will not create or replace it.\n");
    exit(1);
}

if (!$composer) {
    fwrite(STDERR, "ERROR: Composer 2 was not found.\n");
    exit(1);
}

chdir($base);

// Locked dependencies only. Never run composer update in production.
runCommand(escapeshellarg($php) . ' ' . escapeshellarg($composer) . ' install --no-dev --prefer-dist --optimize-autoloader --no-interaction');

// Incremental schema changes only. Never use migrate:fresh, db:wipe, or seed here.
runCommand(escapeshellarg($php) . ' artisan migrate --force --no-interaction');

// Rebuild application caches without changing application data or APP_KEY.
runCommand(escapeshellarg($php) . ' artisan optimize:clear');
runCommand(escapeshellarg($php) . ' artisan config:cache');
runCommand(escapeshellarg($php) . ' artisan route:cache');
runCommand(escapeshellarg($php) . ' artisan view:cache');

echo "\nCGJobs deployment completed successfully. Existing .env, APP_KEY, database records and admin credentials were preserved.\n";
