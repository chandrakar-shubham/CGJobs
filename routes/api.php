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
| CGJobs Android App Mobile REST API Routes
|--------------------------------------------------------------------------
| All routes in this file are automatically prefixed with /api
| Example: /api/health, /api/jobs, /api/news, /api/sections, /api/static-gk
*/

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
