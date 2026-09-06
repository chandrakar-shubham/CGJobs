<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'ticker_text' => AppSetting::get('ticker_text', 'CGPSC राज्य सेवा परीक्षा 2026 प्रारंभिक परीक्षा की तिथि जारी | व्यापम शिक्षक पात्रता TET प्रवेश पत्र डाउनलोड करें'),
            'ticker_enabled' => AppSetting::get('ticker_enabled', '1'),
            'whatsapp_url' => AppSetting::get('whatsapp_url', 'https://chat.whatsapp.com/CGJobsOfficial'),
            'telegram_url' => AppSetting::get('telegram_url', 'https://t.me/cgjobs_official'),
            'support_email' => AppSetting::get('support_email', 'support@cgjobs.info'),
            'support_phone' => AppSetting::get('support_phone', '+91 771 2432100'),
            'app_version' => AppSetting::get('app_version', '1.0.0'),
            'maintenance_mode' => AppSetting::get('maintenance_mode', '0'),
            'maintenance_message' => AppSetting::get('maintenance_message', 'सर्वर रखरखाव कार्य प्रगति पर है। कृपया कुछ समय बाद पुनः प्रयास करें।'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'ticker_text' => 'nullable|string|max:500',
            'whatsapp_url' => 'nullable|url|max:255',
            'telegram_url' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:100',
            'support_phone' => 'nullable|string|max:50',
            'app_version' => 'nullable|string|max:20',
            'maintenance_message' => 'nullable|string|max:300',
        ]);

        AppSetting::set('ticker_text', $validated['ticker_text'] ?? '', 'ticker', 'Live Breaking News Marquee');
        AppSetting::set('ticker_enabled', $request->boolean('ticker_enabled') ? '1' : '0', 'ticker', 'Enable or disable ticker banner');
        AppSetting::set('whatsapp_url', $validated['whatsapp_url'] ?? '', 'social', 'Official WhatsApp Community');
        AppSetting::set('telegram_url', $validated['telegram_url'] ?? '', 'social', 'Official Telegram Channel');
        AppSetting::set('support_email', $validated['support_email'] ?? '', 'contact', 'Support Email');
        AppSetting::set('support_phone', $validated['support_phone'] ?? '', 'contact', 'Support Phone');
        AppSetting::set('app_version', $validated['app_version'] ?? '1.0.0', 'app', 'Current Minimum App Version');
        AppSetting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0', 'app', 'Maintenance mode active');
        AppSetting::set('maintenance_message', $validated['maintenance_message'] ?? '', 'app', 'Maintenance message');

        return redirect()->route('admin.settings.index')->with('success', 'App settings and ticker saved successfully.');
    }
}
