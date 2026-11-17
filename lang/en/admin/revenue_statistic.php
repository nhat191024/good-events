<?php

return [
    'model_label' => 'Revenue statistics',
    'plural_model_label' => 'Revenue statistics',

    'source' => [
        'deposit' => 'Wallet top-up',
        'asset' => 'Design asset payment',
    ],

    'fields' => [
        'invoice_number' => 'Invoice no.',
        'source' => 'Revenue source',
        'paid_at' => 'Paid at',
        'reference_code' => 'Transaction / order code',
        'buyer' => 'Buyer',
        'buyer_phone' => 'Phone',
        'description' => 'Description',
        'subtotal' => 'Amount before VAT',
        'vat_rate' => 'VAT rate',
        'vat_amount' => 'VAT amount',
        'total' => 'Total paid',
        'payment_method' => 'Payment method',
        'order_code' => 'PayOS order code',
        'bank_reference' => 'Bank reference',
        'gateway_time' => 'Transaction time (PayOS)',
        'payment_reference' => 'PayOS payment link ID',
        'created_at' => 'Created at',
    ],

    'export' => [
        'label' => 'Export invoice ledger (Excel)',
    ],

    'summary' => [
        'total' => 'Total',
    ],
];
