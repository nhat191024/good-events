<?php
return [
    'singular' => 'Pending Service',

    'modals' => [
        'approve' => [
            'heading' => 'Approve Service',
            'description' => 'Are you sure you want to approve this service? This action cannot be undone.',
            'actions' => [
                'approve' => 'Approve',
                'cancel' => 'Cancel',
            ],
        ],

        'reject' => [
            'heading' => 'Reject Service',
            'description' => 'Are you sure you want to reject this service? This action cannot be undone.',
            'actions' => [
                'reject' => 'Reject',
                'cancel' => 'Cancel',
            ],
        ],
    ],

    'fields' => [
        'category' => 'Category',
        'user' => 'User',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'deleted_at' => 'Deleted At',
    ],

    'notifications' => [
        'approved' => 'Service has been approved successfully.',
        'rejected' => 'Service has been rejected.',
    ],

    'actions' => [
        'view_videos' => 'View Videos',
        'approve' => 'Approve',
        'reject' => 'Reject',
    ],
];
