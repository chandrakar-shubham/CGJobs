<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertApiController extends Controller
{
    /**
     * Get alerts and breaking notifications
     */
    public function index(): JsonResponse
    {
        $alerts = Alert::orderBy('id', 'desc')->take(50)->get();

        return response()->json([
            'success' => true,
            'alerts' => $alerts->map(fn($a) => $a->toApiArray())->values(),
        ]);
    }

    /**
     * Register Android FCM device token
     */
    public function registerToken(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'device_model' => $request->deviceModel ?? $request->device_model,
                'platform' => $request->platform ?? 'android',
                'last_active_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered for notifications successfully',
        ]);
    }
}
