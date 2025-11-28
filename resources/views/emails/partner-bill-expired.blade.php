<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.partner_bill_expired.title') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .email-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 300;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555;
        }

        .alert-banner {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #78350f;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 16px;
        }

        .bill-info {
            background-color: #f3f4f6;
            border-left: 4px solid #6b7280;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .bill-info h3 {
            margin-top: 0;
            color: #333;
            font-size: 18px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            min-width: 120px;
        }

        .info-value {
            color: #333;
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-expired {
            background-color: #fef3c7;
            color: #92400e;
        }

        .highlight {
            color: #6b7280;
            font-weight: 600;
        }

        .message-box {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .message-box h4 {
            margin-top: 0;
            color: #92400e;
            font-size: 16px;
        }

        .message-box p {
            margin-bottom: 0;
            color: #78350f;
        }

        .suggestions-section {
            margin: 25px 0;
        }

        .suggestions-section h4 {
            color: #374151;
            margin-bottom: 15px;
        }

        .suggestions-section ul {
            margin: 0;
            padding-left: 20px;
        }

        .suggestions-section li {
            margin-bottom: 10px;
            color: #4b5563;
        }

        .cta-section {
            text-align: center;
            margin: 30px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s;
            margin: 5px;
        }

        .cta-button.secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        .footer {
            background-color: #f3f4f6;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }

            .info-value {
                text-align: left;
                margin-top: 5px;
            }

            .cta-button {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>⏰ {{ __('emails.partner_bill_expired.title') }}</h1>
        </div>

        <div class="content">
            <div class="greeting">
                {{ __('emails.common.greeting_client', ['name' => $partnerBill->client->name ?? 'Khách hàng']) }}
            </div>

            <div class="alert-banner">
                {{ __('emails.partner_bill_expired.alert_message') }}
            </div>

            <p>{{ __('emails.partner_bill_expired.message') }}</p>

            <div class="bill-info">
                <h3>📋 {{ __('emails.common.bill_details') }}</h3>

                <div class="info-row">
                    <span class="info-label">{{ __('emails.common.order_code') }}:</span>
                    <span class="info-value highlight">#{{ $partnerBill->code }}</span>
                </div>

                @if ($partnerBill->event)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.common.event_name') }}:</span>
                        <span class="info-value">{{ $partnerBill->event->name ?? $partnerBill->custom_event }}</span>
                    </div>
                @endif

                @if ($partnerBill->category)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.common.category') }}:</span>
                        <span class="info-value">{{ $partnerBill->category->name }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">{{ __('emails.common.event_date') }}:</span>
                    <span class="info-value">{{ $partnerBill->date ? $partnerBill->date->format('d/m/Y') : __('Not specified') }}</span>
                </div>

                @if ($partnerBill->start_time)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.common.start_time') }}:</span>
                        <span class="info-value">{{ $partnerBill->start_time->format('H:i') }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">{{ __('emails.common.address') }}:</span>
                    <span class="info-value">{{ $partnerBill->address }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">{{ __('emails.common.status') }}:</span>
                    <span class="info-value">
                        <span class="status-badge status-expired">{{ __('emails.partner_bill_expired.status_expired') }}</span>
                    </span>
                </div>
            </div>

            <div class="message-box">
                <h4>{{ __('emails.partner_bill_expired.reason_title') }}</h4>
                <p>{{ __('emails.partner_bill_expired.reason_message') }}</p>
            </div>

            <div class="suggestions-section">
                <h4>{{ __('emails.partner_bill_expired.suggestions_title') }}</h4>
                <ul>
                    @foreach (__('emails.partner_bill_expired.suggestions_list') as $suggestion)
                        <li>{{ $suggestion }}</li>
                    @endforeach
                </ul>
            </div>

            <p>{{ __('emails.partner_bill_expired.apology_message') }}</p>

            <p>{{ __('emails.common.contact_support') }}</p>

            <div class="cta-section">
                <a class="cta-button" href="#">
                    {{ __('emails.partner_bill_expired.cta_new_order') }}
                </a>
                <a class="cta-button secondary" href="#">
                    {{ __('emails.partner_bill_expired.cta_contact_support') }}
                </a>
            </div>
        </div>

        <div class="footer">
            <p>
                {{ __('emails.common.thanks') }}<br>
                {{ __('emails.common.automated_email') }}
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                {{ __('emails.common.copyright', ['year' => date('Y')]) }}
            </p>
        </div>
    </div>
</body>

</html>
