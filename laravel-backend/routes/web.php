<?php

use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\JobPosterTemplateController;
use App\Http\Controllers\Admin\JobSourceController;
use App\Http\Controllers\Admin\NewsSyncController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaticGkController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\JobPosterController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/jobs', [HomeController::class, 'jobs'])->name('listing.jobs');
Route::get('/current-affairs', [HomeController::class, 'currentAffairs'])->name('listing.current-affairs');
Route::get('/gk', [HomeController::class, 'staticGk'])->name('listing.gk');
Route::get('/jobs/{id}/poster', [JobPosterController::class, 'show'])->name('job.poster');
Route::get('/jobs/{id}', [HomeController::class, 'job'])->name('job.show');
Route::get('/current-affairs/{id}', [HomeController::class, 'job'])->name('current-affairs.show');
Route::get('/gk/{id}', [HomeController::class, 'gk'])->name('gk.show');
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [HomeController::class, 'robots'])->name('robots');

Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::match(['get', 'post'], '/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('jobs', JobController::class);
    Route::resource('static-gk', StaticGkController::class);

    Route::get('/job-sources', [JobSourceController::class, 'index'])->name('job-sources.index');
    Route::post('/job-sources', [JobSourceController::class, 'store'])->name('job-sources.store');
    Route::put('/job-sources/{jobSource}', [JobSourceController::class, 'update'])->name('job-sources.update');
    Route::delete('/job-sources/{jobSource}', [JobSourceController::class, 'destroy'])->name('job-sources.destroy');
    Route::post('/job-sources/{jobSource}/sync', [JobSourceController::class, 'sync'])->name('job-sources.sync');

    Route::get('/job-imports/{jobImport}/edit', [JobSourceController::class, 'editImport'])->name('job-imports.edit');
    Route::put('/job-imports/{jobImport}', [JobSourceController::class, 'updateImport'])->name('job-imports.update');
    Route::post('/job-imports/approve-all', [JobSourceController::class, 'approveAll'])->name('job-imports.approve-all');
    Route::post('/job-imports/{jobImport}/approve', [JobSourceController::class, 'approve'])->name('job-sources.approve');
    Route::post('/job-imports/{jobImport}/reject', [JobSourceController::class, 'reject'])->name('job-sources.reject');

    Route::post('/job-poster-template', [JobPosterTemplateController::class, 'upload'])->name('job-poster-template.upload');
    Route::resource('categories', CategoryController::class);
    Route::resource('sections', SectionController::class);
    Route::resource('alerts', AlertController::class);

    // Settings are a singleton page, not a CRUD resource.
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

    Route::get('/news-sync', [NewsSyncController::class, 'index'])->name('sync.index');
    Route::post('/news-sync', [NewsSyncController::class, 'sync'])->name('sync.run');
    Route::post('/news-sync/run', [NewsSyncController::class, 'sync'])->name('news-sync');
    Route::post('/cache/clear', [CacheController::class, 'clear'])->name('cache.clear');
});
