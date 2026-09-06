<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        ]);
    }
}
