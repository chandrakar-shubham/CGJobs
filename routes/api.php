<?php

use App\Http\Controllers\Api\AlertApiController;
use App\Http\Controllers\Api\HealthApiController;
use App\Http\Controllers\Api\JobApiController;
use App\Http\Controllers\Api\SectionApiController;
use App\Http\Controllers\Api\SettingApiController;
use App\Http\Controllers\Api\StaticGkApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CGJobs REST API
|--------------------------------------------------------------------------
| /api/* is retained for Android backward compatibility.
| /api/v1/* is the stable public integration contract for WordPress,
| Android and future clients. Laravel remains the source of truth.
*/

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

// Existing mobile clients continue using these endpoints.
Route::group([], $registerApiRoutes);

// Stable versioned contract for WordPress and future clients.
Route::prefix('v1')->group($registerApiRoutes);
