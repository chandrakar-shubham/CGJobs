<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\Job;
use App\Models\StaticGk;
use Illuminate\Http\JsonResponse;

class HealthApiController extends Controller
{
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'appName' => 'CGJobs Laravel API',
            'version' => '1.0.0',
            'serverTime' => now()->toISOString(),
            'platform' => 'Laravel ' . app()->version(),
            'stats' => [
                'totalJobs' => Job::where('section', 'jobs')->count(),
                'totalNews' => Job::where('section', 'news')->count(),
                'totalStaticGk' => StaticGk::count(),
                'categoriesCount' => Category::count(),
                'alertsCount' => Alert::count(),
            ],
            'ticker' => [
                'enabled' => AppSetting::get('ticker_enabled', '1') === '1',
                'text' => AppSetting::get('ticker_text', 'CGPSC राज्य सेवा परीक्षा 2026 प्रारंभिक परीक्षा की तिथि जारी | व्यापम शिक्षक पात्रता TET प्रवेश पत्र डाउनलोड करें'),
            ]
        ]);
    }
}
