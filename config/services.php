<?php

return [
    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'service_account_path' => env('FCM_SERVICE_ACCOUNT_PATH'),
    ],

    'newsapi' => [
        'key' => env('NEWSAPI_KEY'),
    ],

    'newsdata' => [
        'key' => env('NEWSDATA_KEY'),
    ],
];
