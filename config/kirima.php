<?php

return [
    'enabled' => (bool) env('KIRIMA_ENABLED', false),
    'client_id' => env('KIRIMA_CLIENT_ID'),
    'client_secret' => env('KIRIMA_CLIENT_SECRET'),
    'device_jid' => env('KIRIMA_DEVICE_JID'),
    'reminder_days_before' => (int) env('KIRIMA_REMINDER_DAYS_BEFORE', 3),
    'send_at' => env('KIRIMA_SEND_AT', '09:00'),
    'api_url' => env('KIRIMA_API_URL', 'https://api.kirima.app/api/v1/wa/messages'),
];
