<?php
return [
    'app' => 'System',
    'app_settings' => 'Settings related to the system',

    'banner' => 'Banner',
    'banner_settings' => 'Settings related to the banner',

    'partner' => 'Partner',
    'partner_settings' => 'Settings related to the partner',

    'saved' => 'Settings have been saved successfully!',

    'sections' => [
        'titles' => [
            'general' => 'General Settings',
            'titles' => 'Page Titles',
            'contact' => 'Contact Information',
            'socials' => 'Social Media',
            'zalo' => 'Zalo API Settings',
        ],

        'descriptions' => [
            'general' => 'Configure general settings for the application',
            'titles' => 'Configure titles for category pages',
            'contact' => 'Information displayed in the footer or contact page',
            'socials' => 'Links to social media pages',
            'zalo' => "Configuration needed to integrate with external services - Don't change if you don't know what you are doing!!",
        ],
    ],

    'banners' => [
        'partner' => 'Partner',
        'mobile_partner' => 'Partner (Mobile)',
        'design' => 'Design',
        'mobile_design' => 'Design (Mobile)',
        'rental' => 'Rental Supplies',
        'mobile_rental' => 'Rental Supplies (Mobile)',
    ],

    'fields' => [
        'title' => 'Application Title',
        'description' => 'Application Description',
        'name' => 'Application Name',
        'logo' => 'Application Logo',
        'favicon' => 'Favicon',
        'zalo_token' => 'Zalo Access Token',
        'zalo_refresh_token' => 'Zalo Refresh Token',
        'zalo_app_secret' => 'Zalo App Secret',
        'zalo_app_id' => 'Zalo App ID',
        'zalo_admin_phone' => 'Admin Phone Number to receive messages during testing',
        'zalo_otp_template_id' => 'Zalo OTP Message Template ID',

        'titles' => [
            'partner' => 'Partner Page Title',
            'design' => 'Design Page Title',
            'rental' => 'Rental Supplies Page Title',
        ],

        'banners' => [
            'partner' => 'Partner Banners',
            'mobile_partner' => 'Partner Banners (Mobile)',
            'design' => 'Design Banners',
            'mobile_design' => 'Design Banners (Mobile)',
            'rental' => 'Rental Supplies Banners',
            'mobile_rental' => 'Rental Supplies Banners (Mobile)',

            'helper_text' => 'Upload banner images to display on the respective pages.',
        ],

        'contact_hotline' => 'Contact Hotline',
        'contact_email' => 'Contact Email',

        'socials' => [
            'facebook' => 'Facebook Link',
            'facebook_group' => 'Facebook Group Link',
            'zalo' => 'Zalo Link',
            'youtube' => 'Youtube Link',
            'tiktok' => 'Tiktok Link',
        ],

        'minimum_balance' => 'Minimum Balance',
        'fee_percentage' => 'Fee Percentage',
        'default_balance' => 'Default Balance',
    ],

    'placeholders' => [
        'default_balance' => 'Default balance for new partners',
    ]
];
