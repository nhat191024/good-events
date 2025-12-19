<?php
return [
    'singular' => 'Report',

    'infolist' => [
        'report_details' => 'Report Details',
        'relationships' => 'Relationships',
        'timestamps' => 'Timestamps',
    ],

    'fields' => [
        'id' => 'ID',
        'title' => 'Title',
        'content' => 'Content',
        'user' => 'Reporting User',
        'reportedUser' => 'Reported User',
        'reportedBill' => 'Reported Show',
        'status' => 'Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ],


    'actions' => [
        'change_status' => 'Change Status',
        'create_chat' => 'Create Chat',
        'view_chat' => 'View Chat',
    ],

    'helpers' => [
        'create_chat' => 'Create a new conversation with the reporting user to discuss the issue.',
        'view_chat' => 'View the created conversation with the reporting user.',
    ],

    'status' => [
        'pending' => 'Pending',
        'reviewed' => 'Reviewed',
        'resolved' => 'Resolved',
    ],
];
