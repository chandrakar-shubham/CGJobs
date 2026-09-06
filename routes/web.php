<?php

use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\NewsSyncController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaticGkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Authentication
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::match(['get', 'post'], '/admin/logout', [AuthController::class, 'logout'])->name('admin.logout');

// Protected Admin Panel Routes
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Job Postings & News CRUD
    Route::resource('jobs', JobController::class);

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

    // NewsAPI & NewsData.io Scraper
    Route::get('/sync', [NewsSyncController::class, 'index'])->name('sync.index');
    Route::post('/sync', [NewsSyncController::class, 'sync'])->name('sync.run');
});
