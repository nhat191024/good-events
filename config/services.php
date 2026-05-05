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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'opencage' => [
        'key' => env('OPEN_CAGE_API_KEY'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    'apple' => [
        'service_id'    => env('APPLE_SERVICE_ID'),
        'ios_bundle_id' => env('APPLE_IOS_BUNDLE_ID'),
        'redirect'      => (function () {
            $redirect = env('APPLE_REDIRECT_URI');

            if (! is_string($redirect) || $redirect === '') {
                return $redirect;
            }

            if (str_starts_with($redirect, 'http://') || str_starts_with($redirect, 'https://')) {
                return $redirect;
            }

            return rtrim(env('APP_URL', 'http://localhost'), '/') . '/' . ltrim($redirect, '/');
        })(),
    ],

    'payos' => [
        'client_id' => env('PAYOS_CLIENT_ID', ''),
        'api_key' => env('PAYOS_API_KEY', ''),
        'checksum_key' => env('PAYOS_CHECKSUM_KEY', ''),
        'partner_code' => env('PAYOS_PARTNER_CODE', ''),
        'app_deep_link' => env('APP_PAYMENT_RESULT_DEEPLINK_URL', '')
    ],
];
