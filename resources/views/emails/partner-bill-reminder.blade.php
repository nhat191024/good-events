<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.partner_bill_reminder.title') }}</title>
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
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
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

        .reminder-alert {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .countdown {
            background-color: #fee2e2;
            border: 2px solid #dc2626;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }

        .countdown h3 {
            margin: 0 0 10px 0;
            color: #dc2626;
            font-size: 20px;
        }

        .countdown-timer {
            font-size: 24px;
            font-weight: bold;
            color: #dc2626;
        }

        .bill-info {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
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
            border-bottom: 1px solid #eee;
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

        .highlight {
            color: #dc2626;
            font-weight: 600;
        }

        .checklist {
            background-color: #fef2f2;
            border: 1px solid #dc2626;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .checklist h4 {
            margin-top: 0;
            color: #dc2626;
        }

        .checklist ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .checklist li {
            margin-bottom: 8px;
            color: #dc2626;
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
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        .footer {
            background-color: #fef2f2;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #fecaca;
        }

        .contact-info {
            background-color: #fef2f2;
            border: 1px solid #dc2626;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        @media (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }

            .info-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('emails.partner_bill_reminder.title') }}</h1>
        </div>

        <div class="content">
            <div class="greeting">
                @if ($recipientType === 'client')
                    {{ __('emails.common.greeting_client', ['name' => $partnerBill->client->name]) }}
                @else
                    {{ __('emails.common.greeting_partner', ['name' => $partnerBill->partner->name]) }}
                @endif
            </div>

            <div class="reminder-alert">
                @if ($recipientType === 'client')
                    {{ __('emails.partner_bill_reminder.alert_client') }}
                @else
                    {{ __('emails.partner_bill_reminder.alert_partner') }}
                @endif
            </div>

            <div class="countdown">
                <h3>{{ __('emails.partner_bill_reminder.time_remaining_title') }}</h3>
                @if ($partnerBill->date && $partnerBill->start_time)
                    @php
                        $eventDateTime = \Carbon\Carbon::parse($partnerBill->date->format('Y-m-d') . ' ' . $partnerBill->start_time->format('H:i:s'));
                        $now = \Carbon\Carbon::now();
                        $diff = $eventDateTime->diffForHumans($now);
                    @endphp
                    <div class="countdown-timer">{{ $diff }}</div>
                @else
                    <div class="countdown-timer">{{ __('emails.partner_bill_reminder.time_remaining_text') }}</div>
                @endif
            </div>

            <div class="bill-info">
                <h3>📋 {{ __('emails.common.bill_details') }}</h3>

                <div class="info-row">
                    <span class="info-label">{{ __('emails.common.order_code') }}:</span>
                    <span class="info-value highlight">#{{ $partnerBill->code }}</span>
                </div>

                @if ($partnerBill->event)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.common.event_name') }}:</span>
                        <span class="info-value">{{ $partnerBill->event->name }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">{{ __('emails.common.event_date') }}:</span>
                    <span class="info-value highlight">{{ $partnerBill->date ? $partnerBill->date->format('d/m/Y') : __('Not specified') }}</span>
                </div>

                @if ($partnerBill->start_time)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.common.start_time') }}:</span>
                        <span class="info-value highlight">{{ $partnerBill->start_time->format('H:i') }}</span>
                    </div>
                @endif

                @if ($partnerBill->end_time)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.common.end_time') }}:</span>
                        <span class="info-value">{{ $partnerBill->end_time->format('H:i') }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">{{ __('emails.common.address') }}:</span>
                    <span class="info-value">{{ $partnerBill->address }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">{{ __('emails.common.phone') }}:</span>
                    <span class="info-value">{{ $partnerBill->phone }}</span>
                </div>

                @if ($recipientType === 'client' && $partnerBill->partner)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.common.partner_name') }}:</span>
                        <span class="info-value">{{ $partnerBill->partner->name }}</span>
                    </div>
                @endif

                @if ($recipientType === 'partner' && $partnerBill->client)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.common.client_name') }}:</span>
                        <span class="info-value">{{ $partnerBill->client->name }}</span>
                    </div>
                @endif
            </div>

            @if ($recipientType === 'client')
                <div class="checklist">
                    <h4>{{ __('emails.partner_bill_reminder.checklist_client_title') }}</h4>
                    <ul>
                        @foreach (__('emails.partner_bill_reminder.checklist_client_list') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="contact-info">
                    <strong>{{ __('emails.partner_bill_reminder.contact_info_client_title') }}</strong><br>
                    {{ __('emails.partner_bill_reminder.contact_name') }}: {{ $partnerBill->partner->name ?? 'N/A' }}<br>
                    {{ __('emails.partner_bill_reminder.contact_email') }}: {{ $partnerBill->partner->email ?? 'N/A' }}<br>
                    {{ __('emails.partner_bill_reminder.contact_phone') }}: {{ $partnerBill->partner->phone ?? 'N/A' }}
                </div>

                <p>
                    {{ __('emails.partner_bill_reminder.message_client') }}
                </p>
            @else
                <div class="checklist">
                    <h4>{{ __('emails.partner_bill_reminder.checklist_partner_title') }}</h4>
                    <ul>
                        @foreach (__('emails.partner_bill_reminder.checklist_partner_list') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="contact-info">
                    <strong>{{ __('emails.partner_bill_reminder.contact_info_partner_title') }}</strong><br>
                    {{ __('emails.partner_bill_reminder.contact_name') }}: {{ $partnerBill->client->name ?? 'N/A' }}<br>
                    {{ __('emails.partner_bill_reminder.contact_email') }}: {{ $partnerBill->client->email ?? 'N/A' }}<br>
                    {{ __('emails.partner_bill_reminder.contact_phone') }}: {{ $partnerBill->phone }}
                </div>

                <p>
                    {{ __('emails.partner_bill_reminder.message_partner') }}
                </p>
            @endif

            <div class="cta-section">
                <a class="cta-button" href="#">
                    @if ($recipientType === 'client')
                        {{ __('emails.partner_bill_reminder.cta_client') }}
                    @else
                        {{ __('emails.partner_bill_reminder.cta_partner') }}
                    @endif
                </a>
            </div>
        </div>

        <div class="footer">
            <p>
                {!! __('emails.partner_bill_reminder.footer_note') !!}<br>
                {{ __('emails.common.automated_email') }}
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                {{ __('emails.common.copyright', ['year' => date('Y')]) }}
            </p>
        </div>
    </div>
</body>

</html>
