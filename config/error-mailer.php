<?php

return [
    'email' => [
        'recipient' => [],
        'bcc' => [],
        'cc' => [],
        'subject' => 'An error has occured - ' . env('APP_NAME'),
    ],

    'disabledOn' => [
        'local',
    ],

    'cacheCooldown' => 10, // in minutes

    'webhooks' => [
        'discord' => env('ERROR_MAILER_DISCORD_WEBHOOK'),

        'message' => [
            'title' => 'Error Alert - ' . env('APP_NAME'),
            'description' => 'An error has occured in the application.',
            'error' => 'Error',
            'file' => 'File',
            'line' => 'Line',
            'details_link' => 'See more details'
        ],
    ],

    'storage_path' => storage_path('app/errors'),
];
