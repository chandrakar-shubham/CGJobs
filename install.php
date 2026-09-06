<?php
/**
 * CGJobs Laravel 1-Click Auto-Installer for Hostinger
 * 
 * Instructions:
 * 1. Upload the entire 'laravel-backend' folder to your Hostinger account.
 * 2. Upload this file (install.php) to your public_html/ directory.
 * 3. Visit: https://yourdomain.com/install.php in your web browser.
 * 4. Fill in your MySQL database credentials and click "Install & Configure".
 * 5. Delete this file after installation for security!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$status = null;
$message = '';
$log = [];

// Detect root directory (either one level up in laravel-backend, or current dir)
$possibleDirs = [
    __DIR__ . '/../laravel-backend',
    __DIR__ . '/../cgjobs-backend',
    __DIR__ . '/laravel-backend',
    __DIR__
];

$backendDir = null;
foreach ($possibleDirs as $dir) {
    if (file_exists($dir . '/artisan') && file_exists($dir . '/composer.json')) {
        $backendDir = realpath($dir);
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? '127.0.0.1';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $appUrl = rtrim($_POST['app_url'] ?? '', '/');
    $adminUser = $_POST['admin_user'] ?? 'admin';
    $adminPass = $_POST['admin_pass'] ?? 'admin123';

    if (!$backendDir) {
        $status = 'error';
        $message = "Laravel files not found. Please upload 'laravel-backend' into your hosting directory.";
    } else {
        // Step 1: Test MySQL Connection
        try {
            $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $log[] = "✅ Connected to MySQL database '{$dbName}' successfully.";
        } catch (Exception $e) {
            $status = 'error';
            $message = "Database connection failed: " . $e->getMessage();
        }

        if (!$status) {
            // Step 2: Generate .env file
            $appKey = 'base64:' . base64_encode(random_bytes(32));
            $envContent = "APP_NAME=\"CGJobs Admin\"\n"
                . "APP_ENV=production\n"
                . "APP_KEY={$appKey}\n"
                . "APP_DEBUG=false\n"
                . "APP_TIMEZONE=Asia/Kolkata\n"
                . "APP_URL={$appUrl}\n\n"
                . "DB_CONNECTION=mysql\n"
                . "DB_HOST={$dbHost}\n"
                . "DB_PORT={$dbPort}\n"
                . "DB_DATABASE={$dbName}\n"
                . "DB_USERNAME={$dbUser}\n"
                . "DB_PASSWORD={$dbPass}\n\n"
                . "ADMIN_USERNAME={$adminUser}\n"
                . "ADMIN_PASSWORD={$adminPass}\n";

            file_put_contents($backendDir . '/.env', $envContent);
            $log[] = "✅ Generated .env configuration file.";

            // Step 3: Create MySQL Tables directly via PDO
            try {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `jobs` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `custom_id` VARCHAR(191) UNIQUE NOT NULL,
                        `title` VARCHAR(255) NOT NULL,
                        `summary` TEXT NOT NULL,
                        `detailed_content` LONGTEXT NULL,
                        `category` VARCHAR(100) NOT NULL,
                        `vacancies` VARCHAR(100) NULL,
                        `eligibility` VARCHAR(255) NULL,
                        `age_limit` VARCHAR(100) NULL,
                        `selection_process` VARCHAR(255) NULL,
                        `important_dates` JSON NULL,
                        `official_notification_url` VARCHAR(500) NULL,
                        `apply_url` VARCHAR(500) NULL,
                        `is_hot` TINYINT(1) DEFAULT 0,
                        `is_breaking` TINYINT(1) DEFAULT 0,
                        `published_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        `created_at` TIMESTAMP NULL,
                        `updated_at` TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS `alerts` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `custom_id` VARCHAR(191) UNIQUE NOT NULL,
                        `title` VARCHAR(255) NOT NULL,
                        `message` TEXT NOT NULL,
                        `type` VARCHAR(50) DEFAULT 'job_alert',
                        `target_url` VARCHAR(500) NULL,
                        `is_active` TINYINT(1) DEFAULT 1,
                        `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        `created_at` TIMESTAMP NULL,
                        `updated_at` TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS `categories` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `section_id` VARCHAR(50) DEFAULT 'jobs',
                        `name` VARCHAR(100) NOT NULL,
                        `slug` VARCHAR(100) UNIQUE NOT NULL,
                        `hindi_name` VARCHAR(100) NULL,
                        `color` VARCHAR(50) DEFAULT '#1565C0',
                        `display_order` INT DEFAULT 0,
                        `is_active` TINYINT(1) DEFAULT 1,
                        `created_at` TIMESTAMP NULL,
                        `updated_at` TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS `device_tokens` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `token` VARCHAR(500) UNIQUE NOT NULL,
                        `device_model` VARCHAR(100) NULL,
                        `os_version` VARCHAR(50) NULL,
                        `app_version` VARCHAR(50) NULL,
                        `last_seen_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        `created_at` TIMESTAMP NULL,
                        `updated_at` TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS `static_gks` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `custom_id` VARCHAR(191) UNIQUE NOT NULL,
                        `title` VARCHAR(255) NOT NULL,
                        `hindi_title` VARCHAR(255) NULL,
                        `category` VARCHAR(100) NOT NULL,
                        `category_hindi` VARCHAR(100) NULL,
                        `question` TEXT NULL,
                        `answer` TEXT NULL,
                        `key_points` JSON NULL,
                        `detailed_notes` LONGTEXT NULL,
                        `year_exam_reference` VARCHAR(255) NULL,
                        `is_verified` TINYINT(1) DEFAULT 1,
                        `display_order` INT DEFAULT 0,
                        `created_at` TIMESTAMP NULL,
                        `updated_at` TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS `app_sections` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `section_key` VARCHAR(50) UNIQUE NOT NULL,
                        `name` VARCHAR(100) NOT NULL,
                        `hindi_name` VARCHAR(100) NULL,
                        `description` VARCHAR(255) NULL,
                        `icon` VARCHAR(50) DEFAULT 'folder',
                        `is_active` TINYINT(1) DEFAULT 1,
                        `display_order` INT DEFAULT 0,
                        `created_at` TIMESTAMP NULL,
                        `updated_at` TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                    CREATE TABLE IF NOT EXISTS `app_settings` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `key` VARCHAR(100) UNIQUE NOT NULL,
                        `value` LONGTEXT NULL,
                        `description` VARCHAR(255) NULL,
                        `created_at` TIMESTAMP NULL,
                        `updated_at` TIMESTAMP NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
                $log[] = "✅ Database tables created successfully (jobs, alerts, categories, device_tokens, static_gks, app_sections, app_settings).";

                // Seed Default Sections
                $sections = [
                    ['jobs', 'Jobs & Vacancies', 'सरकारी नौकरियां', 'छत्तीसगढ़ व केंद्रीय सरकारी नौकरी सूचनाएं', 'briefcase', 1, 1],
                    ['news', 'Current Affairs & News', 'समसामयिकी व समाचार', 'दैनिक करंट अफेयर्स, योजनाएं व समसामयिक घटनाएं', 'newspaper', 1, 2],
                    ['static_gk', 'Static GK & Study Notes', 'सामान्य ज्ञान (GK)', 'छत्तीसगढ़ इतिहास, भूगोल, संस्कृति व अध्ययन सामग्री', 'book-open', 1, 3],
                ];
                $secStmt = $pdo->prepare("INSERT IGNORE INTO `app_sections` (`section_key`, `name`, `hindi_name`, `description`, `icon`, `is_active`, `display_order`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                foreach ($sections as $sec) {
                    $secStmt->execute($sec);
                }
                $log[] = "✅ Seeded app navigation sections.";

                // Seed Default Settings (Live Ticker)
                $settings = [
                    ['ticker_text', 'CGPSC राज्य सेवा परीक्षा 2026 प्रारंभिक परीक्षा की तिथि जारी | व्यापम शिक्षक पात्रता TET प्रवेश पत्र डाउनलोड करें', 'App Marquee Ticker'],
                    ['ticker_enabled', '1', 'Ticker Switch'],
                    ['app_name', 'CG Jobs & Current Affairs', 'App Name'],
                    ['app_version', '1.4.0', 'Current Version'],
                    ['contact_email', 'support@cgjobsportal.in', 'Support Email'],
                ];
                $setStmt = $pdo->prepare("INSERT IGNORE INTO `app_settings` (`key`, `value`, `description`, `created_at`, `updated_at`) VALUES (?, ?, ?, NOW(), NOW())");
                foreach ($settings as $setting) {
                    $setStmt->execute($setting);
                }
                $log[] = "✅ Seeded live ticker & app settings.";

                // Seed Default Categories
                $categories = [
                    ['CGPSC', 'cgpsc', 'छत्तीसगढ़ लोक सेवा आयोग', '#C2185B'],
                    ['CG Vyapam', 'cg-vyapam', 'छत्तीसगढ़ व्यावसायिक परीक्षा मंडल', '#1565C0'],
                    ['CG Police', 'cg-police', 'छत्तीसगढ़ पुलिस भर्ती', '#2E7D32'],
                    ['CG Education', 'cg-education', 'शिक्षक व स्कूल शिक्षा', '#E65100'],
                    ['CG Health', 'cg-health', 'स्वास्थ्य एवं परिवार कल्याण', '#00838F'],
                    ['Central Jobs', 'central-jobs', 'केंद्रीय भर्ती (Railway/SSC)', '#4527A0'],
                ];

                $catStmt = $pdo->prepare("INSERT IGNORE INTO `categories` (`name`, `slug`, `hindi_name`, `color`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, NOW(), NOW())");
                foreach ($categories as $cat) {
                    $catStmt->execute($cat);
                }
                $log[] = "✅ Seeded initial recruitment categories.";

                // Seed Sample Verified Jobs
                $jobStmt = $pdo->prepare("INSERT IGNORE INTO `jobs` 
                    (`custom_id`, `title`, `summary`, `detailed_content`, `category`, `vacancies`, `eligibility`, `age_limit`, `selection_process`, `important_dates`, `official_notification_url`, `apply_url`, `is_hot`, `is_breaking`, `published_at`, `created_at`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())");

                $jobStmt->execute([
                    'cg-police-constable-2026',
                    'CG Police Constable 5967 Posts Recruitment 2026',
                    'छत्तीसगढ़ पुलिस विभाग में 5,967 आरक्षक पदों पर सीधी भर्ती। 10वीं/12वीं पास उम्मीदवार ऑनलाइन आवेदन कर सकते हैं।',
                    'छत्तीसगढ़ पुलिस मुख्यालय रायपुर द्वारा आरक्षक (जीडी/चालक/ट्रेडमैन) के रिक्त पदों हेतु विस्तृत विज्ञापन जारी कर दिया गया है।',
                    'CG Police',
                    '5,967',
                    '10वीं / 12वीं उत्तीर्ण (ST वर्ग हेतु 8वीं उत्तीर्ण)',
                    '18 से 28 वर्ष (आरक्षित वर्ग हेतु छूट लागू)',
                    'दस्तावेज जांच, शारीरिक नापजोख (PMT), शारीरिक दक्षता परीक्षा (PET) 100 अंक, लिखित परीक्षा 100 अंक',
                    json_encode(['आवेदन प्रारंभ' => '01/01/2026', 'अंतिम तिथि' => '30/03/2026', 'शारीरिक परीक्षा' => 'अप्रैल 2026']),
                    'https://cgpolice.gov.in',
                    'https://cgpolice.gov.in',
                    1,
                    1
                ]);

                $jobStmt->execute([
                    'cg-vyapam-tet-2026',
                    'CG Vyapam Teacher Eligibility Test (TET) 2026',
                    'छत्तीसगढ़ व्यापम द्वारा प्राथमिक एवं उच्च प्राथमिक शिक्षक पात्रता परीक्षा 2026 की अधिसूचना जारी।',
                    'स्कूल शिक्षा विभाग छत्तीसगढ़ के अंतर्गत प्राथमिक (कक्षा 1 से 5) एवं उच्च प्राथमिक (कक्षा 6 से 8) शालाओं में अध्यापन हेतु।',
                    'CG Vyapam',
                    'पात्रता परीक्षा',
                    'डी.एल.एड. / बी.एड. / बी.एल.एड. में अध्ययनरत या उत्तीर्ण',
                    'न्यूनतम 18 वर्ष',
                    'वस्तुनिष्ठ बहुविकल्पीय लिखित परीक्षा (150 अंक)',
                    json_encode(['ऑनलाइन आवेदन' => '15/01/2026', 'त्रुटि सुधार' => '05/02/2026', 'परीक्षा तिथि' => '25/03/2026']),
                    'https://vyapam.cgstate.gov.in',
                    'https://vyapam.cgstate.gov.in',
                    1,
                    0
                ]);

                $log[] = "✅ Seeded initial verified jobs (CG Police Constable & CG Vyapam TET).";

                // Step 4: Configure public_html index.php and .htaccess if needed
                if (!file_exists(__DIR__ . '/.htaccess')) {
                    $htContent = "<IfModule mod_rewrite.c>\n"
                        . "    RewriteEngine On\n"
                        . "    RewriteCond %{REQUEST_FILENAME} !-d\n"
                        . "    RewriteCond %{REQUEST_FILENAME} !-f\n"
                        . "    RewriteRule ^ index.php [L]\n"
                        . "</IfModule>\n";
                    file_put_contents(__DIR__ . '/.htaccess', $htContent);
                    $log[] = "✅ Created public_html/.htaccess for URL routing.";
                }

                $status = 'success';
                $message = "Setup completed successfully! Your CGJobs Admin Panel is ready.";

            } catch (Exception $e) {
                $status = 'error';
                $message = "Table creation or seeding error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CGJobs Hostinger 1-Click Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-100 min-h-screen py-10 px-4">
    <div class="max-w-xl mx-auto bg-white rounded-3xl shadow-xl p-8 border border-slate-200">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-blue-600 text-white mx-auto flex items-center justify-center text-3xl shadow-lg mb-3">
                <i class="fa-solid fa-cloud-bolt"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Hostinger 1-Click Setup</h1>
            <p class="text-xs text-slate-500 mt-1">CGJobs Laravel Admin Panel & REST API Automated Installer</p>
        </div>

        <?php if ($status === 'success'): ?>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 mb-6 text-emerald-900">
                <h3 class="font-bold text-sm flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-circle-check text-emerald-600"></i> Setup Complete!
                </h3>
                <p class="text-xs text-emerald-800 leading-relaxed"><?= htmlspecialchars($message) ?></p>

                <div class="mt-4 pt-4 border-t border-emerald-200 space-y-2 text-xs">
                    <p><strong>Admin URL:</strong> <a href="<?= htmlspecialchars($appUrl) ?>/admin" target="_blank" class="underline text-blue-600"><?= htmlspecialchars($appUrl) ?>/admin</a></p>
                    <p><strong>API Health:</strong> <a href="<?= htmlspecialchars($appUrl) ?>/api/health" target="_blank" class="underline text-blue-600"><?= htmlspecialchars($appUrl) ?>/api/health</a></p>
                    <p><strong>Login:</strong> <?= htmlspecialchars($adminUser) ?> / (Your password)</p>
                </div>

                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-[11px]">
                    <strong>⚠️ Security Notice:</strong> Please delete <code>install.php</code> from your Hostinger File Manager now to protect your site.
                </div>
            </div>

            <?php if (!empty($log)): ?>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-mono text-slate-700 space-y-1 mb-6">
                    <?php foreach ($log as $l): ?>
                        <div><?= htmlspecialchars($l) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <a href="<?= htmlspecialchars($appUrl) ?>/admin" class="block w-full text-center py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm rounded-xl shadow transition">
                Open Admin Panel <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>

        <?php else: ?>

            <?php if ($status === 'error'): ?>
                <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-6 text-xs text-rose-800 font-medium">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 mr-1.5"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4 text-xs">
                
                <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-xl text-blue-900 text-[11px] leading-relaxed">
                    <strong>Step:</strong> Create a MySQL database in Hostinger <strong>hPanel → Databases → MySQL Databases</strong>, then paste the credentials below.
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Your Domain / App URL</label>
                    <input type="url" name="app_url" required value="<?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]") ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none text-xs">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Database Name</label>
                        <input type="text" name="db_name" required placeholder="u123456_cgjobs" value="<?= htmlspecialchars($_POST['db_name'] ?? '') ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Database User</label>
                        <input type="text" name="db_user" required placeholder="u123456_admin" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Database Password</label>
                        <input type="password" name="db_pass" required placeholder="••••••••" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none text-xs">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Database Host</label>
                        <input type="text" name="db_host" value="127.0.0.1" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none text-xs">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3">
                    <h4 class="font-bold text-slate-700 mb-2">Admin Panel Login Credentials</h4>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-slate-500 mb-1">Username</label>
                            <input type="text" name="admin_user" value="admin" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="block text-slate-500 mb-1">Password</label>
                            <input type="text" name="admin_pass" value="admin123" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-md transition transform active:scale-95 text-xs flex items-center justify-center gap-2">
                    <i class="fa-solid fa-play"></i> Install & Configure Everything Now
                </button>
            </form>
        <?php endif; ?>

    </div>
</body>
</html>
