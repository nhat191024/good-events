<?php

return [
    // ===== COMMON - Shared keys for all emails =====
    'common' => [
        // Greetings
        'dear' => 'Dear',
        'hello' => 'Hello',
        'greeting_client' => 'Hello :name,',
        'greeting_partner' => 'Hello Partner :name,',
        'regards' => 'Best regards',

        // Company Info
        'team_name' => 'SukiEntot Team',
        'company_name' => 'SukiEntot',
        'support_email' => 'support@sukientot.com',
        'website' => 'https://sukientot.com',
        'copyright' => '© :year SukiEntot - Event Management Platform',

        // Order/Bill Fields
        'order_code' => 'Order Code',
        'event_name' => 'Event',
        'event_date' => 'Event Date',
        'event_time' => 'Event Time',
        'start_time' => 'Start Time',
        'end_time' => 'End Time',
        'category' => 'Category',
        'location' => 'Location',
        'address' => 'Address',
        'phone' => 'Phone',
        'note' => 'Note',
        'status' => 'Status',
        'total_amount' => 'Total Amount',
        'bill_details' => 'Order Details',

        // Party Info
        'client_name' => 'Client',
        'partner_name' => 'Service Provider',
        'contact_info' => 'Contact Information',

        // Status
        'status_pending' => 'Pending',
        'status_paid' => 'Paid',
        'status_confirmed' => 'Confirmed',
        'status_cancelled' => 'Cancelled',

        // Actions
        'view_details' => 'View Details',
        'contact_support' => 'If you have any questions, please contact our support team.',

        // Footer
        'automated_email' => 'This is an automated email, please do not reply to this email.',
        'thanks' => 'Thank you for using our services!',
        'unsubscribe' => 'Unsubscribe from these emails',
        'privacy_policy' => 'Privacy Policy',
        'terms_of_service' => 'Terms of Service',
    ],

    // ===== PARTNER BILL RECEIVED - New order email =====
    'partner_bill_received' => [
        'title' => 'Service Order Notification',
        'subject' => 'Order Matching - :code',
        'new_order_notification' => 'New Order Matching Your Services',

        // Messages - Client
        'message_client' => 'Thank you for your order! We have received your service booking request and our partner will review it shortly.',
        'next_steps_client' => 'What happens next?',
        'next_steps_list_client' => [
            'Our partner will review your request',
            'You will receive a confirmation email once approved',
            'You can contact the partner directly for any questions',
        ],

        // Messages - Partner
        'message_partner' => 'There is a new order that matches your services. Please review the details and accept the order if you can fulfill it.',
        'next_steps_partner' => 'What to do next?',
        'next_steps_list_partner' => [
            'Review the order details carefully',
            'Contact the client if you need clarification',
            'Confirm or decline the order in your dashboard',
        ],

        // CTA
        'cta_view_order' => 'View Order Details',
        'cta_accept_order' => 'View & Accept Order',
    ],

    // ===== PARTNER BILL CONFIRMED - Payment confirmation email =====
    'partner_bill_confirmed' => [
        'title' => 'Service Booking Confirmed',
        'subject' => 'Order Confirmed - :code',
        'success_banner' => '🎉 Order Confirmed Successfully!',

        // Messages
        'message_client' => 'Your order has been confirmed and paid successfully!',
        'message_partner' => 'You have been confirmed by the client for the service booking!',

        // Payment Info
        'payment_info' => 'Payment Information',
        'payment_status' => 'Payment Status',
        'payment_method' => 'Payment Method',
        'transaction_id' => 'Transaction ID',
        'paid_amount' => 'Paid Amount',

        // Important Notes - Client
        'important_note_client_title' => '📝 Important Notes:',
        'important_note_client_list' => [
            'Please arrive on time at the booked location',
            'Contact the service provider if there are any changes',
            'Prepare everything necessary for the event',
        ],
        'thanks_client' => 'Thank you for trusting and using our services. We hope your event will be successful!',

        // Important Notes - Partner
        'important_note_partner_title' => '📝 Your Tasks:',
        'important_note_partner_list' => [
            'Prepare all services as required',
            'Contact the client to confirm details',
            'Arrive on time at the location',
            'Ensure the best service quality',
        ],
        'thanks_partner' => 'We wish you successful service delivery and positive reviews from customers!',

        // CTA
        'cta_client' => 'View Order Details',
        'cta_partner' => 'Manage Order',
    ],

    // ===== PARTNER BILL REMINDER - Pre-event reminder email =====
    'partner_bill_reminder' => [
        'title' => 'Event Reminder',
        'subject' => 'Event Reminder - :code',

        // Alert Banners
        'alert_client' => '🎪 Your event will take place in the next 24 hours!',
        'alert_partner' => '🚀 You have a service to deliver in the next 24 hours!',

        // Time Remaining
        'time_remaining_title' => '⏳ Time Remaining',
        'time_remaining_text' => 'Check specific time',

        // Checklist - Client
        'checklist_client_title' => '✅ Preparation Checklist for Client:',
        'checklist_client_list' => [
            'Reconfirm time and location with service provider',
            'Prepare everything necessary for the event',
            'Check route and transportation',
            'Save the service provider\'s contact number',
            'Arrive 15-30 minutes early',
        ],
        'message_client' => 'We hope your event will be successful and leave beautiful memories!',

        // Checklist - Partner
        'checklist_partner_title' => '🎯 Preparation Checklist for Service Provider:',
        'checklist_partner_list' => [
            'Review the client\'s service requirements',
            'Prepare all necessary equipment and materials',
            'Reconfirm time and location with the client',
            'Check route and plan transportation',
            'Ensure to arrive on time or 15 minutes early',
            'Prepare mentally to provide the best service',
        ],
        'message_partner' => 'Deliver the service professionally and wholeheartedly to receive positive reviews from customers!',

        // Contact Info
        'contact_info_client_title' => '📞 Service Provider Contact Information:',
        'contact_info_partner_title' => '📞 Client Contact Information:',
        'contact_name' => 'Name',
        'contact_email' => 'Email',
        'contact_phone' => 'Phone',

        // CTA
        'cta_client' => 'View Event Details',
        'cta_partner' => 'Manage Order',

        // Footer
        'footer_note' => '<strong>Note:</strong> If there are any changes or issues, please contact immediately.',
    ],

    // ===== PARTNER BILL EXPIRED - Order expired notification email =====
    'partner_bill_expired' => [
        'title' => 'Order Expired',
        'subject' => 'Order Expired - :code',

        // Alert Banner
        'alert_message' => '⚠️ Your order has expired because no partner accepted it!',

        // Messages
        'message' => 'We regret to inform you that your service booking has expired and no partner has accepted the order.',

        // Status
        'status_expired' => 'Expired',

        // Reason
        'reason_title' => '📝 Reason for order expiration:',
        'reason_message' => 'The order has exceeded the waiting period without any partner in our system confirming acceptance. This may be due to the requested service not having a suitable partner available at this time.',

        // Suggestions
        'suggestions_title' => '💡 You can take the following steps:',
        'suggestions_list' => [
            'Place a new order with more flexible timing',
            'Choose a service category with more partners available',
            'Contact our support team for assistance',
            'Review your requirements and location',
        ],

        // Apology
        'apology_message' => 'We sincerely apologize for this inconvenience and hope you will continue to use our services in the future.',

        // CTA
        'cta_new_order' => 'Place New Order',
        'cta_contact_support' => 'Contact Support',
    ],
];
