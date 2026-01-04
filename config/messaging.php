<?php

return [
    'rate_limits' => [
        'send_per_minute' => 20,
        'reply_per_minute' => 30,
        'channels' => [
            'sms' => 10,
            'email' => 60,
            'push' => 120,
            'in_app' => 120,
        ],
    ],
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'twilio'),
        'segment_length' => 160,
        'segment_cost' => 0.01,
    ],
    'email' => [
        'track_opens' => false,
        'track_clicks' => false,
        'from_name' => env('MAIL_FROM_NAME', 'Taisykla'),
        'from_address' => env('MAIL_FROM_ADDRESS', 'support@example.com'),
    ],
    'compliance' => [
        'can_spam' => [
            'require_unsubscribe' => true,
            'footer' => 'Manage email preferences in your account settings.',
        ],
        'tcpa' => [
            'require_opt_in' => true,
            'stop_notice' => 'Reply STOP to unsubscribe.',
        ],
        'gdpr' => [
            'log_consent' => true,
            'data_retention_days' => 365,
        ],
    ],
];
