<?php

use App\Http\Controllers\Admin\AiContentEngineController;
use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CanonicalJobImportApprovalController;
use App\Http\Controllers\Admin\CanonicalJobImportController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobAdvancedController;
use App\Http\Controllers\Admin\JobManagementController;
use App\Http\Controllers\Admin\JobPosterTemplateController;
use App\Http\Controllers\Admin\JobScraperConsoleController;
use App\Http\Controllers\Admin\JobSourceController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NewsStudioController;
use App\Http\Controllers\Admin\NewsSyncController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaticGkController;
use App\Http\Controllers\CurrentAffairsWebController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\JobApplyRedirectController;
use App\Http\Controllers\Web\JobPosterController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;

// Public Web Views
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jobs', [HomeController::class, 'jobs'])->name('listing.jobs');
Route::get('/current-affairs', [HomeController::class, 'currentAffairs'])->name('listing.current-affairs');
Route::get('/gk', [HomeController::class, 'staticGk'])->name('listing.gk');
Route::get('/current-affairs/{slug}', [CurrentAffairsWebController::class, 'show'])->name('current-affairs.show');
Route::get('/news/{slug}', [CurrentAffairsWebController::class, 'show'])->name('news.show');
Route::get('/job/{id}', [HomeController::class, 'job'])->name('job.show');
Route::get('/jobs/{id}', [HomeController::class, 'job']);
Route::get('/job/{id}/apply', JobApplyRedirectController::class)->name('job.apply');
Route::get('/jobs/{id}/apply', JobApplyRedirectController::class);
Route::get('/job/{id}/poster', [JobPosterController::class, 'show'])->name('job.poster');
Route::get('/jobs/{id}/poster', [JobPosterController::class, 'show']);
Route::get('/gk/{id}', [HomeController::class, 'gk'])->name('gk.show');
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [HomeController::class, 'robots'])->name('robots');

// Authentication
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::match(['get', 'post'], '/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Protected Admin Panel Routes
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Job Management (Dashboard, CRUD, Bulk, Actions, Templates)
    Route::get('/jobs/dashboard', [JobManagementController::class, 'dashboard'])->name('jobs.dashboard');
    Route::get('/jobs', [JobManagementController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/scraper-console', [JobScraperConsoleController::class, 'index'])->name('jobs.scraper-console');
    Route::post('/jobs/scraper-console/scrape', [JobScraperConsoleController::class, 'runScrape'])->name('jobs.scraper-console.scrape');
    Route::post('/jobs/scraper-console/delete-staged', [JobScraperConsoleController::class, 'deleteStaged'])->name('jobs.scraper-console.delete-staged');
    Route::post('/jobs/scraper-console/batch-gemini', [JobScraperConsoleController::class, 'processBatchGemini'])->name('jobs.scraper-console.batch-gemini');
    Route::post('/jobs/scraper-console/publish/{job}', [JobScraperConsoleController::class, 'publishJob'])->name('jobs.scraper-console.publish');
    Route::post('/jobs/scraper-console/schedule/{job}', [JobScraperConsoleController::class, 'scheduleJob'])->name('jobs.scraper-console.schedule');
    Route::delete('/jobs/scraper-console/draft/{job}', [JobScraperConsoleController::class, 'deleteDraft'])->name('jobs.scraper-console.delete-draft');
    Route::get('/jobs/create', [JobManagementController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobManagementController::class, 'store'])->name('jobs.store');
    Route::post('/jobs/bulk-action', [JobManagementController::class, 'bulkAction'])->name('jobs.bulk-action');
    Route::get('/jobs/templates', [JobManagementController::class, 'templates'])->name('jobs.templates');
    Route::get('/jobs/republished', [JobAdvancedController::class, 'republished'])->name('jobs.republished');
    Route::get('/jobs/categories', [JobAdvancedController::class, 'categories'])->name('jobs.categories');
    Route::get('/jobs/notifications', [JobAdvancedController::class, 'notifications'])->name('jobs.notifications');
    Route::get('/jobs/import-sync', [JobAdvancedController::class, 'importSync'])->name('jobs.import-sync');
    Route::get('/jobs/quality', [JobAdvancedController::class, 'quality'])->name('jobs.quality');
    Route::get('/jobs/duplicates', [JobAdvancedController::class, 'duplicates'])->name('jobs.duplicates');
    Route::get('/jobs/analytics', [JobAdvancedController::class, 'analytics'])->name('jobs.analytics');
    Route::get('/jobs/{job}/edit', [JobManagementController::class, 'edit'])->name('jobs.edit');
    Route::put('/jobs/{job}', [JobManagementController::class, 'update'])->name('jobs.update');
    Route::delete('/jobs/{job}', [JobManagementController::class, 'destroy'])->name('jobs.destroy');
    Route::post('/jobs/{job}/publish', [JobManagementController::class, 'publish'])->name('jobs.publish');
    Route::post('/jobs/{job}/republish', [JobManagementController::class, 'republish'])->name('jobs.republish');
    Route::get('/jobs/{job}/notification-history', [JobAdvancedController::class, 'notificationHistory'])->name('jobs.notification-history');
    Route::get('/jobs/{job}/versions', [JobAdvancedController::class, 'versions'])->name('jobs.versions');
    Route::get('/jobs/{job}/timeline', [JobAdvancedController::class, 'timeline'])->name('jobs.timeline');
    Route::get('/jobs/{job}/documents', [JobAdvancedController::class, 'documents'])->name('jobs.documents');
    Route::post('/jobs/{job}/documents', [JobAdvancedController::class, 'storeDocument'])->name('jobs.documents.store');
    Route::delete('/jobs/documents/{document}', [JobAdvancedController::class, 'destroyDocument'])->name('jobs.documents.destroy');
    Route::get('/jobs/{job}/seo', [JobAdvancedController::class, 'seo'])->name('jobs.seo');
    Route::post('/jobs/{job}/seo', [JobAdvancedController::class, 'updateSeo'])->name('jobs.seo.update');

    // News Studio & News Management
    Route::get('/news/dashboard', [NewsStudioController::class, 'dashboard'])->name('news.dashboard');
    Route::get('/news', [NewsStudioController::class, 'index'])->name('news.index');
    Route::post('/news/fetch', [NewsStudioController::class, 'fetch'])->name('news.fetch');
    Route::post('/news/batch-add', [NewsStudioController::class, 'addToBatch'])->name('news.batch-add');
    Route::post('/news/batch-clear', [NewsStudioController::class, 'clearBatch'])->name('news.batch-clear');
    Route::post('/news/batch-process', [NewsStudioController::class, 'batchProcess'])->name('news.batch-process');
    Route::post('/news/bulk-delete', [NewsStudioController::class, 'bulkDelete'])->name('news.bulk-delete');
    Route::post('/news/clear-queue', [NewsStudioController::class, 'clearReviewQueue'])->name('news.clear-queue');
    Route::post('/news/publish-selected', [NewsStudioController::class, 'publishSelected'])->name('news.publish-selected');
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');
    Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('/news/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}', [NewsStudioController::class, 'destroy'])->name('news.destroy');
    Route::post('/news/{news}/publish', [NewsController::class, 'publish'])->name('news.publish');
    Route::post('/news/{news}/channel', [NewsStudioController::class, 'channel'])->name('news.channel');
    Route::post('/news/{news}/archive', [NewsStudioController::class, 'archive'])->name('news.archive');
    Route::post('/news/{news}/unpublish', [NewsStudioController::class, 'unpublish'])->name('news.unpublish');
    Route::get('/news/{news}/view', [NewsStudioController::class, 'view'])->name('news.view');

    // AI Content Engine
    Route::get('/ai-engine', [AiContentEngineController::class, 'index'])->name('ai-engine.index');
    Route::post('/ai-engine/settings', [AiContentEngineController::class, 'settings'])->name('ai-engine.settings');
    Route::post('/ai-engine/ingest-news', [AiContentEngineController::class, 'ingestNews'])->name('ai-engine.ingest-news');
    Route::post('/ai-engine/process', [AiContentEngineController::class, 'process'])->name('ai-engine.process');
    Route::get('/ai-engine/preview/{aiContent}', [AiContentEngineController::class, 'preview'])->name('ai-engine.preview');
    Route::post('/ai-engine/retry/{aiContent}', [AiContentEngineController::class, 'retry'])->name('ai-engine.retry');
    Route::post('/ai-engine/publish/{aiContent}', [AiContentEngineController::class, 'publish'])->name('ai-engine.publish');

    // Job Sources & Imports (Crawler)
    Route::get('/job-sources', [JobSourceController::class, 'index'])->name('job-sources.index');
    Route::post('/job-sources', [JobSourceController::class, 'store'])->name('job-sources.store');
    Route::put('/job-sources/{jobSource}', [JobSourceController::class, 'update'])->name('job-sources.update');
    Route::delete('/job-sources/{jobSource}', [JobSourceController::class, 'destroy'])->name('job-sources.destroy');
    Route::post('/job-sources/{jobSource}/toggle-notify', [JobSourceController::class, 'toggleNotify'])->name('job-sources.toggle-notify');
    Route::post('/job-sources/{jobSource}/sync', [JobSourceController::class, 'sync'])->name('job-sources.sync');
    Route::post('/job-sources/{jobSource}/refresh-pending', [JobSourceController::class, 'refreshPending'])->name('job-sources.refresh-pending');
    Route::post('/job-sources/{jobImport}/approve', [JobSourceController::class, 'approve'])->name('job-sources.approve');
    Route::post('/job-sources/{jobImport}/reject', [JobSourceController::class, 'reject'])->name('job-sources.reject');
    Route::post('/job-imports/approve-all', [JobSourceController::class, 'approveAll'])->name('job-imports.approve-all');
    Route::get('/job-imports/{jobImport}/edit', [CanonicalJobImportController::class, 'edit'])->name('job-imports.edit');
    Route::put('/job-imports/{jobImport}', [CanonicalJobImportController::class, 'update'])->name('job-imports.update');
    Route::post('/job-poster-template/upload', [JobPosterTemplateController::class, 'upload'])->name('job-poster-template.upload');

    // Static GK CRUD
    Route::resource('static-gk', StaticGkController::class);

    // Sections & Category Hierarchy
    Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
    Route::post('/sections', [SectionController::class, 'storeSection'])->name('sections.store');
    Route::post('/sections/{section}/toggle', [SectionController::class, 'toggleSection'])->name('sections.toggle');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // App Settings & Live Marquee Ticker
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Push Alerts & FCM
    Route::get('/alerts', [AlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/create', [AlertController::class, 'create'])->name('alerts.create');
    Route::post('/alerts', [AlertController::class, 'store'])->name('alerts.store');
    Route::delete('/alerts/{alert}', [AlertController::class, 'destroy'])->name('alerts.destroy');

    // News Scraper & Gemini AI Sync
    Route::get('/sync', [NewsSyncController::class, 'index'])->name('sync.index');
    Route::post('/sync', [NewsSyncController::class, 'sync'])->name('sync.run');
    Route::post('/sync/gemini-key', [NewsSyncController::class, 'saveGeminiKey'])->name('sync.gemini-key');
    Route::post('/sync/gemini-preview', [NewsSyncController::class, 'previewGemini'])->name('sync.gemini-preview');
});
