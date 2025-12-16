<?php
return [
    'singular' => 'Partner Bill',

    'fields' => [
        'code' => 'Code',
        'address' => 'Address',
        'phone' => 'Phone',
        'date' => 'Date',
        'start_time' => 'Start Time',
        'end_time' => 'End Time',
        'final_total' => 'Final Total',
        'event' => 'Event',
        'client' => 'Client',
        'partner' => 'Partner',
        'category' => 'Category',
        'status' => 'Status',
        'arrival_photo' => 'Arrival Photo',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],

    'actions' => [
        'cancel_bill' => 'Cancel Bill',
        'cancel_bill_heading' => 'Confirm Cancel Partner Bill',
        'cancel_bill_description' => 'Are you sure you want to cancel this partner bill? This action cannot be undone.',
    ],

    'notifications' => [
        'bill_cancelled' => 'Bill Cancelled',
        'bill_cancelled_body' => 'Bill :code has been cancelled.',
    ],
];
