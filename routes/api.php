<?php

use App\Http\Controllers\Api\AiContentController;
use App\Http\Controllers\Api\AlertApiController;
use App\Http\Controllers\Api\HealthApiController;
use App\Http\Controllers\Api\JobApiController;
use App\Http\Controllers\Api\NewsApiController;
use App\Http\Controllers\Api\SectionApiController;
use App\Http\Controllers\Api\SettingApiController;
use App\Http\Controllers\Api\StaticGkApiController;
use Illuminate\Support\Facades\Route;

$registerApiRoutes = static function (): void {
    Route::get('/health', [HealthApiController::class, 'check']);
    Route::get('/news', [JobApiController::class, 'index']);
    Route::get('/jobs', [JobApiController::class, 'jobs']);
    Route::get('/news/{id}', [JobApiController::class, 'show']);
    Route::get('/sections', [SectionApiController::class, 'index']);
    Route::get('/categories', [JobApiController::class, 'categories']);
    Route::get('/static-gk', [StaticGkApiController::class, 'index']);
    Route::get('/settings', [SettingApiController::class, 'index']);
    Route::get('/alerts', [AlertApiController::class, 'index']);
    Route::post('/alerts/register-token', [AlertApiController::class, 'registerToken']);
};

Route::group([], $registerApiRoutes);
Route::prefix('v1')->group($registerApiRoutes);

// Isolated current-affairs API. Existing Jobs-backed /news routes are unchanged.
Route::get('/current-affairs', [NewsApiController::class, 'index']);
Route::get('/current-affairs/{id}', [NewsApiController::class, 'show']);
Route::prefix('v1')->group(static function (): void {
    Route::get('/current-affairs', [NewsApiController::class, 'index']);
    Route::get('/current-affairs/{id}', [NewsApiController::class, 'show']);
});

// AI Content Engine v1. Backend-only; provider keys must never be sent to clients.
Route::prefix('v1/ai')->group(static function (): void {
    Route::post('/process', [AiContentController::class, 'process']);
    Route::get('/content/{aiContent}', [AiContentController::class, 'show']);
});
