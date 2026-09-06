<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Broadcast notification to all users via topic or registered device tokens
     */
    public function broadcast(string $title, string $message, ?string $category = 'सूचना', ?string $actionUrl = null, ?string $articleId = null): array
    {
        $serverKey = config('services.fcm.server_key') ?: env('FCM_SERVER_KEY');
        
        $payload = [
            'to' => '/topics/all_users',
            'priority' => 'high',
            'notification' => [
                'title' => $title,
                'body' => $message,
                'sound' => 'default',
                'android_channel_id' => 'cgjobs_alerts',
            ],
            'data' => [
                'title' => $title,
                'body' => $message,
                'category' => $category,
                'action_url' => $actionUrl,
                'article_id' => $articleId,
                'click_action' => 'OPEN_ALERT',
                'timestamp' => (string)now()->timestamp,
            ]
        ];

        if (empty($serverKey)) {
            Log::info("FCM Notification simulated (FCM_SERVER_KEY not set in .env): {$title}");
            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Notification recorded. Set FCM_SERVER_KEY in .env to dispatch live push alerts to devices.'
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                Log::info("FCM push broadcast successful: " . $response->body());
                return [
                    'success' => true,
                    'simulated' => false,
                    'response' => $response->json(),
                ];
            } else {
                Log::error("FCM push error: " . $response->body());
                return [
                    'success' => false,
                    'simulated' => false,
                    'error' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            Log::error("FCM dispatch exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
