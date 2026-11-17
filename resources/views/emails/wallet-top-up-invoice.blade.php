<!DOCTYPE html>
<html lang="{{ $locale }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.wallet_top_up_invoice.title') }}</title>
    @include('emails.partials.invoice-styles')
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
