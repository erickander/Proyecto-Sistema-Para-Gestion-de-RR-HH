<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ai_analyzer' => [
        'url' => env('AI_ANALYZER_URL', 'http://127.0.0.1:4101'),
        'token' => env('AI_ANALYZER_TOKEN'),
        'timeout' => env('AI_ANALYZER_TIMEOUT', 90),
        'connect_timeout' => env('AI_ANALYZER_CONNECT_TIMEOUT', 10),
        'php_time_limit' => env('AI_ANALYZER_PHP_TIME_LIMIT', 180),
        'max_cv_chars' => env('AI_ANALYZER_MAX_CV_CHARS', 18000),
    ],

];
