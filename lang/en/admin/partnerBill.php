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
        'change_status' => 'Change Status',
        'change_status_description' => 'Select a new status for this bill',
        'update' => 'Update',
    ],

    'notifications' => [
        'status_changed' => 'Status Changed',
        'status_changed_body' => 'Bill :code has been changed from :old_status to :new_status',
    ],
];
