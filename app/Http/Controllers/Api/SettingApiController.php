<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;

class SettingApiController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'settings' => [
                'tickerText' => AppSetting::get('ticker_text', 'CGPSC राज्य सेवा परीक्षा 2026 प्रारंभिक परीक्षा की तिथि जारी | व्यापम शिक्षक पात्रता TET प्रवेश पत्र डाउनलोड करें'),
                'tickerEnabled' => AppSetting::get('ticker_enabled', '1') === '1',
                'whatsappUrl' => AppSetting::get('whatsapp_url', 'https://chat.whatsapp.com/CGJobsOfficial'),
                'telegramUrl' => AppSetting::get('telegram_url', 'https://t.me/cgjobs_official'),
                'supportEmail' => AppSetting::get('support_email', 'support@cgjobs.info'),
                'supportPhone' => AppSetting::get('support_phone', '+91 771 2432100'),
                'appVersion' => AppSetting::get('app_version', '1.0.0'),
                'maintenanceMode' => AppSetting::get('maintenance_mode', '0') === '1',
                'maintenanceMessage' => AppSetting::get('maintenance_message', ''),
            ]
        ]);
    }
}
