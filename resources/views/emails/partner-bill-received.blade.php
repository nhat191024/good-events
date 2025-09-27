<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.partner_bill_received.title') }}</title>
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

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            fontSize: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fee2e2;
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

        .highlight {
            color: #dc2626;
            font-weight: 600;
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
            <h1>
                @if ($recipientType === 'client')
                    🎉 {{ __('emails.partner_bill_received.subject', ['code' => '']) }}
                @else
                    📋 {{ __('emails.partner_bill_received.subject', ['code' => '']) }}
                @endif
            </h1>
        </div>

        <div class="content">
            <div class="greeting">
                @if ($recipientType === 'client')
                    {!! __('emails.partner_bill_received.greeting_client', ['name' => '<strong>' . $partnerBill->client->name . '</strong>']) !!}
                @else
                    {!! __('emails.partner_bill_received.greeting_partner', ['name' => '<strong>' . $partnerBill->partner->name . '</strong>']) !!}
                @endif
            </div>

            <p>
                @if ($recipientType === 'client')
                    {{ __('emails.partner_bill_received.message_client') }}
                @else
                    {{ __('emails.partner_bill_received.message_partner') }}
                @endif
            </p>

            <div class="bill-info">
                <h3>📋 {{ __('emails.partner_bill_received.bill_details') }}</h3>

                <div class="info-row">
                    <span class="info-label">{{ __('emails.partner_bill_received.order_code') }}:</span>
                    <span class="info-value highlight">#{{ $partnerBill->code }}</span>
                </div>

                @if ($partnerBill->event)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.partner_bill_received.event_name') }}:</span>
                        <span class="info-value">{{ $partnerBill->event->name }}</span>
                    </div>
                @endif

                @if ($partnerBill->category)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.partner_bill_received.category') }}:</span>
                        <span class="info-value">{{ $partnerBill->category->name }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">{{ __('emails.partner_bill_received.event_date') }}:</span>
                    <span class="info-value">{{ $partnerBill->date ? $partnerBill->date->format('d/m/Y') : __('Not specified') }}</span>
                </div>

                @if ($partnerBill->start_time)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.partner_bill_received.event_time') }}:</span>
                        <span class="info-value">{{ $partnerBill->start_time->format('H:i') }}</span>
                    </div>
                @endif

                @if ($partnerBill->end_time)
                    <div class="info-row">
                        <span class="info-label">{{ __('End Time') }}:</span>
                        <span class="info-value">{{ $partnerBill->end_time->format('H:i') }}</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">{{ __('emails.partner_bill_received.location') }}:</span>
                    <span class="info-value">{{ $partnerBill->address }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">{{ __('emails.partner_bill_received.phone') }}:</span>
                    <span class="info-value">{{ $partnerBill->phone }}</span>
                </div>

                @if ($partnerBill->final_total)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.partner_bill_received.total_amount') }}:</span>
                        <span class="info-value highlight">{{ number_format($partnerBill->final_total, 0, ',', '.') }} VNĐ</span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="info-label">{{ __('emails.partner_bill_received.status') }}:</span>
                    <span class="info-value">
                        <span class="status-badge status-pending">{{ __('emails.partner_bill_received.status_pending') }}</span>
                    </span>
                </div>

                @if ($partnerBill->note)
                    <div class="info-row">
                        <span class="info-label">{{ __('emails.partner_bill_received.note') }}:</span>
                        <span class="info-value">{{ $partnerBill->note }}</span>
                    </div>
                @endif
            </div>

            <h3>{{ __('emails.partner_bill_received.' . ($recipientType === 'client' ? 'next_steps_client' : 'next_steps_partner')) }}</h3>
            <ul style="margin: 10px 0; padding-left: 20px;">
                @foreach (__('emails.partner_bill_received.next_steps_list_' . $recipientType) as $step)
                    <li style="margin-bottom: 8px;">{{ $step }}</li>
                @endforeach
            </ul>

            <p>{{ __('emails.partner_bill_received.contact_support') }}</p>

            <div class="cta-section">
                <a class="cta-button" href="#">
                    {{ __('emails.partner_bill_received.cta_view_order') }}
                </a>
            </div>
        </div>

        <div class="footer">
            <p>
                {{ __('emails.partner_bill_received.thanks') }}<br>
                {{ __('emails.partner_bill_received.footer_text') }}
            </p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                © {{ date('Y') }} {{ __('emails.common.company_name') }} - Event Management Platform
            </p>
        </div>
    </div>
</body>

</html>
