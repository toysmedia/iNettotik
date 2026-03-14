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
];
