<?php

use App\Http\Controllers\Api\AlertApiController;
use App\Http\Controllers\Api\HealthApiController;
use App\Http\Controllers\Api\JobApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CGJobs Android App Mobile REST API Routes
|--------------------------------------------------------------------------
| All routes in this file are automatically prefixed with /api
| Example: /api/health, /api/news, /api/alerts
*/

Route::get('/health', [HealthApiController::class, 'check']);

Route::get('/news', [JobApiController::class, 'index']);
Route::get('/news/{id}', [JobApiController::class, 'show']);

Route::get('/categories', [JobApiController::class, 'categories']);

Route::get('/alerts', [AlertApiController::class, 'index']);
Route::post('/alerts/register-token', [AlertApiController::class, 'registerToken']);
