<?php
return [
    'partner_show_reminder_title' => 'Reminder: Partner show :code is starting soon',
    'partner_show_reminder_body' => 'The show with code :code is starting on :start_date. Please ensure timely attendance.',

    'order_completed_title' => 'Order Completed',
    'order_completed_body' => 'Your order :code has been completed. Please leave a review!',
    'partner_order_completed_body' => 'Order :code has been completed. Please complete the order',
    'action_review' => 'Leave Review',

    'partner_accepted_title' => 'New Partner Submitted a Quote',
    'partner_accepted_body' => ':partner_name has submitted a quote for your event order :code',

    'client_accepted_title' => 'Client Accepted Your Order',
    'client_accepted_body' => ':client_name has accepted your order :code',

    'partner_bill_expired_title' => 'Event Order :code has Expired',
    'partner_bill_expired_body' => 'The event order with code :code has expired due to no partner confirmation.',

    'client_order_expired_title' => 'Your order :code has expired',
    'client_order_expired_body' => 'Your order :code has expired due to no confirmation from partners and has been cancelled.',

    'balance_low_title' => 'Account Balance Warning!',
    'balance_low_body' => 'Hello Partner! Your account balance is currently only :balance VND. Please top up at least :amount VND to continue using the service without interruption.',
    'open_wallet' => 'Go to your wallet',

    'bill_received' => [
        'title' => 'New Event Order Notification',
        'subject' => 'New event order matching your service - :code',
    ],

    'bill_confirmed' => [
        'title' => 'Event Order Update',
        'subject' => 'Event order code :code has been confirmed by the client',
    ],

    'bill_reminder' => [
        'title' => 'Upcoming Event Reminder',
        'client_subject' => 'Reminder! Event ',
        'partner_subject' => 'Reminder! Event for order :code is coming up, please take a confirmation photo if you have arrived at the venue!',
    ],

    'bill_completed_reminder_partner' => [
        'title' => 'Event Order Completion Reminder',
        'subject' => 'Event order code :code has been completed, please click to complete the order!',
    ],

    'bill_completed_reminder_client' => [
        'title' => 'Event Order Completion Reminder',
        'subject' => 'Your event order code :code has been completed, please click to review the order!',
    ],

    'bill_in_job_reminder' => [
        'title' => 'Partner has arrived at the event location',
        'subject' => 'Partner for order :code has arrived at the event location!',
    ],

    'new_review_received' => [
        'title' => 'You just received a new review',
        'body' => 'Event order code :code just received a review from the client.',
    ],

    'new_message' => [
        'body' => 'You have received a new message',
        'body_count' => 'You have :count new messages',
    ],
];
