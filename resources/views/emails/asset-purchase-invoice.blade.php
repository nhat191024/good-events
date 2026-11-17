<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.asset_purchase_invoice.title') }}</title>
    @include('emails.partials.invoice-styles')
</head>

<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ __('emails.asset_purchase_invoice.title') }}</h1>
            <p>{{ __('emails.asset_purchase_invoice.invoice_number') }}: {{ $invoiceNumber }}</p>
        </div>

        <div class="content">
            <div class="greeting">
                {{ __('emails.common.greeting_client', ['name' => $buyerName ?? '']) }}
            </div>

            <div class="success-banner">
                {{ __('emails.asset_purchase_invoice.success_banner') }}
            </div>

            <p>{{ __('emails.asset_purchase_invoice.message') }}</p>

            <table class="party-table" role="presentation">
                <tr>
                    <td>
                        <div class="party-title">{{ __('emails.asset_purchase_invoice.seller') }}</div>
                        <strong>{{ __('emails.common.company_name') }}</strong><br>
                        {{ __('emails.common.support_email') }}<br>
                        {{ __('emails.common.website') }}
                    </td>
                    <td>
                        <div class="party-title">{{ __('emails.asset_purchase_invoice.buyer') }}</div>
                        <strong>{{ $buyerName }}</strong><br>
                        {{ $buyerEmail }}
                        @if ($buyerPhone)
                            <br>{{ $buyerPhone }}
                        @endif
                    </td>
                </tr>
            </table>

            <table class="meta-table" role="presentation">
                <tr>
                    <td>{{ __('emails.asset_purchase_invoice.invoice_number') }}</td>
                    <td class="highlight">{{ $invoiceNumber }}</td>
                </tr>
                <tr>
                    <td>{{ __('emails.asset_purchase_invoice.issued_at') }}</td>
                    <td>{{ $issuedAt?->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>{{ __('emails.asset_purchase_invoice.order_code') }}</td>
                    <td>FPB-{{ $bill->getKey() }}</td>
                </tr>
                @if ($paymentMethod)
                    <tr>
                        <td>{{ __('emails.asset_purchase_invoice.payment_method') }}</td>
                        <td>{{ $paymentMethod }}</td>
                    </tr>
                @endif
                @if ($paymentReference)
                    <tr>
                        <td>{{ __('emails.asset_purchase_invoice.payment_reference') }}</td>
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
                        <th>{{ __('emails.asset_purchase_invoice.item') }}</th>
                        <th class="num">{{ __('emails.asset_purchase_invoice.quantity') }}</th>
                        <th class="num">{{ __('emails.asset_purchase_invoice.unit_price') }}</th>
                        <th class="num">{{ __('emails.asset_purchase_invoice.line_total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $productName }}</td>
                        <td class="num">1</td>
                        <td class="num">{{ number_format($subtotal, 0, ',', '.') }} VNĐ</td>
                        <td class="num">{{ number_format($subtotal, 0, ',', '.') }} VNĐ</td>
                    </tr>
                    @if ($tax > 0)
                        <tr>
                            <td colspan="3">{{ __('emails.asset_purchase_invoice.vat', ['percent' => $vatPercentage]) }}</td>
                            <td class="num">{{ number_format($tax, 0, ',', '.') }} VNĐ</td>
                        </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="3">{{ __('emails.asset_purchase_invoice.grand_total') }}</td>
                        <td class="num">{{ number_format($grandTotal, 0, ',', '.') }} VNĐ</td>
                    </tr>
                </tbody>
            </table>

            <p>{{ __('emails.common.contact_support') }}</p>

            <div class="cta-section">
                <a class="cta-button" href="{{ route('client-orders.asset.dashboard', ['bill_id' => $bill->getKey()]) }}">
                    {{ __('emails.asset_purchase_invoice.cta') }}
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
