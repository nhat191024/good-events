<?php
return [
    'singular' => 'Voucher',

    'fields' => [
        'label' => [
            'code' => 'Voucher Code',
            'discount_percent' => 'Discount Percent (%)',
            'max_discount_amount' => 'Max Discount Amount',
            'min_order_amount' => 'Min Order Amount',
            'usage_limit' => 'Usage Limit',
            'is_unlimited' => 'Is Unlimited',
            'model_type' => 'Apply To',
            'starts_at' => 'Start Date',
            'expires_at' => 'Expiry Date',
        ],

        'placeholder' => [
            'code' => 'Enter voucher code',
            'discount_percent' => 'Enter discount percent (0-100)',
            'max_discount_amount' => 'Enter max discount amount',
            'min_order_amount' => 'Enter min order amount',
            'usage_limit' => 'Enter max usage limit',
            'model_type' => 'Select apply to type',
            'starts_at' => 'Select start date',
            'expires_at' => 'Select expiry date',
        ],

        'helper' => [
            'discount_percent' => 'Enter a value between 0 and 100',
            'max_discount_amount' => 'Maximum discount amount per order (leave empty for no limit)',
            'min_order_amount' => 'Minimum order amount to apply the voucher',
            'usage_limit' => 'Number of times the voucher can be used (only when not unlimited)',
        ],

        'select' => [
            'model_type' => [
                'partner' => 'Partner',
            ],
        ],
    ],
];
