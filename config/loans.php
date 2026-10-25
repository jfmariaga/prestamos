<?php

return [
    'notification_days_before' => (int) env('LOAN_NOTIFICATION_DAYS_BEFORE', 2),

    'whatsapp' => [
        'base_url' => env('WHATSAPP_API_BASE_URL', 'https://graph.facebook.com/v20.0'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'token' => env('WHATSAPP_API_TOKEN'),
    ],
];
