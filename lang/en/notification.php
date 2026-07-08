<?php
return [
    'partner_show_reminder_title' => 'Reminder: Partner show :code is starting soon',
    'partner_show_reminder_body' => 'The show with code :code is starting on :start_date. Please ensure timely attendance.',

    'order_completed_title' => 'Order Completed',
    'order_completed_body' => 'Your order :code has been completed. Please leave a review!',
    'partner_order_completed_body' => 'Order :code has been completed. Please complete the order',
    'action_review' => 'Leave Review',

    'partner_accepted_title' => 'New Partner Submitted a Quote',
    'partner_accepted_body' => ':partner_name submitted a quote of :price',

    'client_accepted_title' => 'Client Accepted Your Order',
    'client_accepted_body' => ':client_name has accepted your order :code',

    'partner_bill_expired_title' => 'Event Order :code has Expired',
    'partner_bill_expired_body' => 'The event order with code :code has expired due to no partner confirmation.',

    'client_order_expired_title' => 'Your order has expired',
    'client_order_expired_body' => 'Your :category order has expired due to no confirmation from partners and has been cancelled.',

    'balance_low_title' => 'Account Balance Warning!',
    'balance_low_body' => 'Hello Partner! Your account balance is currently only :balance VND. Please top up at least :amount VND to continue using the service without interruption.',
    'open_wallet' => 'Go to your wallet',

    'bill_received' => [
        'title' => 'New Event Order Notification',
        'subject' => 'An order matching your :category service',
    ],

    'bill_confirmed' => [
        'title' => 'Event Order Update',
        'subject' => 'The :category order has been selected by the client',
    ],

    'bill_reminder' => [
        'title' => 'Upcoming Event Reminder',
        'client_subject' => 'Reminder! The event for your :category order is coming up',
        'partner_subject' => 'Reminder! The event for order :category is coming up, please take a confirmation photo if you have arrived at the venue!',
    ],

    'bill_completed_reminder_partner' => [
        'title' => 'Event Order Completion Reminder',
        'subject' => 'The :category order has been completed, please click complete and take a confirmation photo!',
    ],

    'bill_completed_reminder_client' => [
        'title' => 'Event Order Completion Reminder',
        'subject' => 'Your :category order has been completed!',
    ],

    'bill_in_job_reminder' => [
        'title' => 'Partner has arrived',
        'subject' => 'The partner for your :category order has arrived at the event location!',
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
