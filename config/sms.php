<?php
return [
    'driver' => env('SMS_DRIVER', 'africastalking'),
    'africastalking' => [
        'username' => env('AT_USERNAME', 'sandbox'),
        'api_key' => env('AT_API_KEY', ''),
        'sender_id' => env('AT_SENDER_ID', ''),
        'base_url' => env('AT_ENV', 'sandbox') === 'production'
            ? 'https://api.africastalking.com/version1'
            : 'https://api.sandbox.africastalking.com/version1',
    ],
    'blessed_africa' => [
        'api_key'   => env('BLESSED_AFRICA_API_KEY', ''),
        'sender_id' => env('BLESSED_AFRICA_SENDER_ID', ''),
    ],
    'advanta' => [
        'api_key'    => env('ADVANTA_API_KEY', ''),
        'partner_id' => env('ADVANTA_PARTNER_ID', ''),
        'sender_id'  => env('ADVANTA_SENDER_ID', ''),
    ],
];
