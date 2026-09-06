<?php

declare(strict_types=1);

/**
 * CGJobs production deployment runner for Hostinger.
 *
 * Safe for repeat deployments:
 * - NEVER creates/replaces .env or APP_KEY.
 * - NEVER wipes or seeds production data.
 * - Uses the committed composer.lock only.
 * - Runs only incremental migrations and Laravel cache rebuilds.
 *
 * Hostinger must run this from the Laravel project root after Git checkout.
 */

$base = __DIR__;
$envFile = $base . '/.env';

if (!is_file($base . '/artisan') || !is_file($base . '/composer.json')) {
    fwrite(STDERR, "ERROR: This script must run from the Laravel project root.\n");
    exit(1);
}

// Production .env is a server secret and is intentionally not stored in Git.
if (!is_file($envFile)) {
    fwrite(STDERR, "ERROR: Production .env is missing. Deployment stopped safely.\n");
    exit(1);
}

function findExecutable(array $candidates): ?string
{
    foreach ($candidates as $candidate) {
        if (is_file($candidate) && is_executable($candidate)) {
            return $candidate;
        }
    }
    return null;
}

$php = findExecutable([
    '/opt/alt/php83/usr/bin/php',
    PHP_BINARY,
    '/usr/bin/php',
]);

$composer = findExecutable([
    '/usr/local/bin/composer2',
    '/usr/bin/composer2',
    '/usr/local/bin/composer',
    '/usr/bin/composer',
]);

if ($php === null) {
    fwrite(STDERR, "ERROR: PHP CLI executable not found.\n");
    exit(1);
}

if ($composer === null) {
    fwrite(STDERR, "ERROR: Composer 2 executable not found.\n");
    exit(1);
}

function runCommand(string $command): void
{
    echo "\n>>> {$command}\n";
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        throw new RuntimeException("Command failed with exit code {$exitCode}: {$command}");
    }
}

chdir($base);

try {
    echo "CGJobs production deployment started.\n";

    // Locked dependencies only. Never use composer update on production.
    runCommand(
        escapeshellarg($php) . ' ' . escapeshellarg($composer)
        . ' install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress --no-scripts'
    );

    // package discovery is run explicitly because Composer scripts are disabled.
    runCommand(escapeshellarg($php) . ' artisan package:discover --ansi');

    // Incremental schema updates only. No fresh/wipe/seed.
    runCommand(escapeshellarg($php) . ' artisan migrate --force --no-interaction');

    // Rebuild caches without changing .env, APP_KEY, or application data.
    runCommand(escapeshellarg($php) . ' artisan optimize:clear');
    runCommand(escapeshellarg($php) . ' artisan config:cache');
    runCommand(escapeshellarg($php) . ' artisan route:cache');
    runCommand(escapeshellarg($php) . ' artisan view:cache');

    echo "\nCGJobs deployment completed successfully. Existing .env, APP_KEY, database records and admin credentials were preserved.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "\nDeployment stopped safely: {$e->getMessage()}\n");
    exit(1);
}
