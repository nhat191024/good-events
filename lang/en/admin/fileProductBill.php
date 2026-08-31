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

    'actions' => [
        'change_payment_status' => 'Change payment status',
        'change_payment_status_warning' => 'This manual action may grant or revoke the customer’s download access. Verify the PayOS transaction before confirming.',
        'confirm_change' => 'Confirm change',
        'reason' => 'Adjustment reason',
        'reason_helper' => 'The reason and administrator account will be recorded in the activity log.',
        'status_updated' => 'Payment status updated',
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
