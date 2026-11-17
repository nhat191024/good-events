<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.wallet_top_up_invoice.title') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 640px;
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

        .header p {
            margin: 6px 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555;
        }

        .success-banner {
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .party-table,
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .party-table td {
            width: 50%;
            vertical-align: top;
            padding: 12px;
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
            font-size: 14px;
        }

        .party-table td+td {
            border-left: 4px solid #fecaca;
        }

        .party-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #b91c1c;
            margin-bottom: 6px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .meta-table td {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .meta-table td:first-child {
            font-weight: 600;
            color: #666;
            width: 45%;
        }

        .meta-table td:last-child {
            text-align: right;
        }

        .items-table {
            margin: 20px 0 0;
            font-size: 14px;
        }

        .items-table th {
            background-color: #dc2626;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: 600;
        }

        .items-table th.num,
        .items-table td.num {
            text-align: right;
            white-space: nowrap;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .total-row td {
            background-color: #fef2f2;
            font-weight: 700;
            font-size: 16px;
            color: #dc2626;
            border-bottom: none;
        }

        .highlight {
            color: #dc2626;
            font-weight: 600;
        }

        .cta-section {
            text-align: center;
            margin: 30px 0 10px;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }

        .footer {
            background-color: #fef2f2;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
            border-top: 1px solid #fecaca;
        }

        @media (max-width: 600px) {
            .party-table td {
                display: block;
                width: auto;
            }

            .party-table td+td {
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('emails.wallet_top_up_invoice.title') }}</h1>
            <p>{{ __('emails.wallet_top_up_invoice.invoice_number') }}: {{ $invoiceNumber }}</p>
        </div>

        <div class="content">
            <div class="greeting">
                {{ __('emails.common.greeting_client', ['name' => $buyer?->name ?? '']) }}
            </div>

            <div class="success-banner">
                {{ __('emails.wallet_top_up_invoice.success_banner') }}
            </div>

            <p>{{ __('emails.wallet_top_up_invoice.message') }}</p>

            <table class="party-table" role="presentation">
                <tr>
                    <td>
                        <div class="party-title">{{ __('emails.wallet_top_up_invoice.seller') }}</div>
                        <strong>{{ __('emails.common.company_name') }}</strong><br>
                        {{ __('emails.common.support_email') }}<br>
                        {{ __('emails.common.website') }}
                    </td>
                    <td>
                        <div class="party-title">{{ __('emails.wallet_top_up_invoice.buyer') }}</div>
                        <strong>{{ $buyer?->name }}</strong><br>
                        {{ $buyer?->email }}
                        @if ($buyer?->phone)
                            <br>{{ $buyer->phone }}
                        @endif
                    </td>
                </tr>
            </table>

            <table class="meta-table" role="presentation">
                <tr>
                    <td>{{ __('emails.wallet_top_up_invoice.invoice_number') }}</td>
                    <td class="highlight">{{ $invoiceNumber }}</td>
                </tr>
                <tr>
                    <td>{{ __('emails.wallet_top_up_invoice.issued_at') }}</td>
                    <td>{{ $issuedAt?->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>{{ __('emails.wallet_top_up_invoice.transaction_id') }}</td>
                    <td>#{{ $transaction->getKey() }}</td>
                </tr>
                <tr>
                    <td>{{ __('emails.wallet_top_up_invoice.payment_method') }}</td>
                    <td>{{ $paymentMethod }}</td>
                </tr>
                @if ($paymentReference)
                    <tr>
                        <td>{{ __('emails.wallet_top_up_invoice.payment_reference') }}</td>
                        <td>{{ $paymentReference }}</td>
                    </tr>
                @endif
                <tr>
                    <td>{{ __('emails.common.status') }}</td>
                    <td class="highlight">{{ __('emails.common.status_paid') }}</td>
                </tr>
            </table>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>{{ __('emails.wallet_top_up_invoice.item') }}</th>
                        <th class="num">{{ __('emails.wallet_top_up_invoice.quantity') }}</th>
                        <th class="num">{{ __('emails.wallet_top_up_invoice.unit_price') }}</th>
                        <th class="num">{{ __('emails.wallet_top_up_invoice.line_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('emails.wallet_top_up_invoice.item_name') }}</td>
                        <td class="num">1</td>
                        <td class="num">{{ number_format($amount, 0, ',', '.') }} VNĐ</td>
                        <td class="num">{{ number_format($amount, 0, ',', '.') }} VNĐ</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3">{{ __('emails.wallet_top_up_invoice.grand_total') }}</td>
                        <td class="num">{{ number_format($amount, 0, ',', '.') }} VNĐ</td>
                    </tr>
                </tbody>
            </table>

            @if ($oldBalance !== null && $newBalance !== null)
                <table class="meta-table" role="presentation">
                    <tr>
                        <td>{{ __('emails.wallet_top_up_invoice.old_balance') }}</td>
                        <td>{{ number_format((int) $oldBalance, 0, ',', '.') }} VNĐ</td>
                    </tr>
                    <tr>
                        <td>{{ __('emails.wallet_top_up_invoice.new_balance') }}</td>
                        <td class="highlight">{{ number_format((int) $newBalance, 0, ',', '.') }} VNĐ</td>
                    </tr>
                </table>
            @endif

            <p>{{ __('emails.common.contact_support') }}</p>

            <div class="cta-section">
                <a class="cta-button" href="{{ route('filament.partner.resources.wallets.index') }}">
                    {{ __('emails.wallet_top_up_invoice.cta') }}
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
