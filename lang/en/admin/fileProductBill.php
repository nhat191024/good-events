<?php
return [
    'singular' => 'File Product Bill',
    'plural' => 'File Product Bills',

    'fields' => [
        'id' => 'ID',
        'file_product_id' => 'File Product',
        'client_id' => 'Client',
        'final_total' => 'Final Total',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'status' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'cancelled' => 'Cancelled',
    ],

    'payment_method' => [
        'qr_transfer' => 'QR Bank Transfer',
        'cash' => 'Cash',
        'online' => 'Online Payment',
    ],

    'description' => [
        'qr_transfer' => 'Payment through Sukientot QR code.',
        'cash' => 'Payment with cash at the office or during a meeting.',
        'online' => 'Payment through supported online payment gateways.',
    ],
];
