<?php
return [
    'user' => 'Users',

    'fields' => [
        'label' => [
            'name' => 'Name',
            'avatar' => 'Avatar',
            'email' => 'Email Address',
            'country_code' => 'Country Code',
            'phone' => 'Phone Number',
            'password' => 'Password',
            'email_verified_at' => 'Email Verified At',
        ],

        'placeholder' => [
            'name' => 'Enter name',
            'email' => 'Enter email address',
            'country_code' => 'Enter country code',
            'phone' => 'Enter phone number',
            'password' => 'Enter password',
        ],
    ],

    'ban_title' => 'Ban User',
    'ban_description' => 'Are you sure you want to ban this user?',

    'ban_success_message' => 'User has been banned successfully.',

    'cannot_ban_self' => 'You cannot ban yourself.',
];
