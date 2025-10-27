<?php

return [
    'profile' => [
        'breadcrumb' => 'Profile settings',
        'head_title' => 'Profile settings',
        'heading' => [
            'title' => 'Profile information',
            'description' => 'Keep your profile and contact details up to date',
        ],
        'form' => [
            'partner_name_label' => 'Partner name',
            'sections' => [
                'avatar' => [
                    'title' => 'Profile photo',
                    'description' => 'Upload a clear profile picture so partners can recognise you.',
                    'change' => 'Upload new photo',
                    'remove' => 'Remove selected photo',
                    'helper' => 'PNG or JPG format, up to 5MB.',
                ],
                'contact' => [
                    'title' => 'Contact details',
                    'description' => 'Please make sure your contact information stays accurate.',
                ],
            ],
            'name' => [
                'label' => 'Name',
                'placeholder' => 'Full name',
            ],
            'email' => [
                'label' => 'Email address',
                'placeholder' => 'Email address',
            ],
            'country_code' => [
                'label' => 'Country code',
                'placeholder' => 'e.g. +1',
            ],
            'phone' => [
                'label' => 'Phone number',
                'placeholder' => 'Your phone number',
            ],
            'unverified_notice' => 'Your email address is unverified.',
            'resend_verification' => 'Click here to resend the verification email.',
            'verification_sent' => 'A new verification link has been sent to your email address.',
            'submit' => 'Save',
            'success' => 'Saved.',
        ],
    ],
    'password' => [
        'breadcrumb' => 'Password settings',
        'head_title' => 'Password settings',
        'heading' => [
            'title' => 'Update password',
            'description' => "Ensure your account is using a long, random password to stay secure",
        ],
        'form' => [
            'current' => [
                'label' => 'Current password',
                'placeholder' => 'Current password',
            ],
            'new' => [
                'label' => 'New password',
                'placeholder' => 'New password',
            ],
            'confirmation' => [
                'label' => 'Confirm password',
                'placeholder' => 'Confirm password',
            ],
            'submit' => 'Save password',
            'success' => 'Saved.',
        ],
    ],
    'appearance' => [
        'breadcrumb' => 'Appearance settings',
        'head_title' => 'Appearance settings',
        'heading' => [
            'title' => 'Appearance settings',
            'description' => "Update your account's appearance settings",
        ],
    ],
];
