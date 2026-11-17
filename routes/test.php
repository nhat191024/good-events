<?php

// all test route gonna be here

use App\Models\PartnerBill;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Broadcasting test screen (dev only)
Route::get('/test/broadcasting', function () {
    return Inertia::render('test/Broadcasting');
})->name('test.broadcasting');

use App\Mail\AssetPurchaseInvoice;
use App\Mail\PartnerBillConfirmed;
use App\Mail\PartnerBillReceived;
use App\Mail\PartnerBillReminder;
use App\Mail\WalletTopUpInvoice;
use App\Models\FileProductBill;
use Bavix\Wallet\Models\Transaction;

// Mail Preview Routes (chỉ dành cho development)
if (app()->environment(['local', 'staging', 'testing'])) {
    Route::prefix('mail-preview')->group(function () {

        Route::get('partner-bill-received/{id}/{type}/{locale?}', function ($id, $type, $locale = 'vi') {
            $partnerBill = PartnerBill::with(['client', 'partner', 'category', 'event', 'details'])->findOrFail($id);

            if (! in_array($type, ['client', 'partner'])) {
                abort(400, 'Type must be either "client" or "partner"');
            }

            if (! in_array($locale, ['vi', 'en'])) {
                abort(400, 'Locale must be either "vi" or "en"');
            }

            return new PartnerBillReceived($partnerBill, $type, $locale);
        })->name('mail.preview.partner-bill-received');

        Route::get('partner-bill-confirmed/{id}/{locale?}', function ($id, $locale = 'vi') {
            $partnerBill = PartnerBill::with(['client', 'partner', 'category', 'event', 'details'])->findOrFail($id);

            if (! in_array($locale, ['vi', 'en'])) {
                abort(400, 'Locale must be either "vi" or "en"');
            }

            return new PartnerBillConfirmed($partnerBill, $locale);
        })->name('mail.preview.partner-bill-confirmed');

        Route::get('partner-bill-reminder/{id}/{type}/{locale?}', function ($id, $type, $locale = 'vi') {
            $partnerBill = PartnerBill::with(['client', 'partner', 'category', 'event', 'details'])->findOrFail($id);

            if (! in_array($type, ['client', 'partner'])) {
                abort(400, 'Type must be either "client" or "partner"');
            }

            if (! in_array($locale, ['vi', 'en'])) {
                abort(400, 'Locale must be either "vi" or "en"');
            }

            return new PartnerBillReminder($partnerBill, $type, $locale);
        })->name('mail.preview.partner-bill-reminder');

        // Hóa đơn bán hàng khi nạp tiền vào ví (id = id của giao dịch ví đã xác nhận)
        Route::get('wallet-top-up-invoice/{id}/{locale?}', function ($id, $locale = 'vi') {
            $transaction = Transaction::query()
                ->where('type', Transaction::TYPE_DEPOSIT)
                ->where('confirmed', true)
                ->findOrFail($id);

            if (! in_array($locale, ['vi', 'en'])) {
                abort(400, 'Locale must be either "vi" or "en"');
            }

            return new WalletTopUpInvoice($transaction, $locale);
        })->name('mail.preview.wallet-top-up-invoice');

        // Hóa đơn bán hàng khi mua asset design (id = id của FileProductBill)
        Route::get('asset-purchase-invoice/{id}/{locale?}', function ($id, $locale = 'vi') {
            $bill = FileProductBill::query()->with(['fileProduct', 'client'])->findOrFail($id);

            if (! in_array($locale, ['vi', 'en'])) {
                abort(400, 'Locale must be either "vi" or "en"');
            }

            return new AssetPurchaseInvoice($bill, $locale);
        })->name('mail.preview.asset-purchase-invoice');

        // Route tổng hợp để list tất cả mail preview có sẵn
        Route::get('/', function () {
            $partnerBills = PartnerBill::with(['client', 'partner', 'category', 'event'])
                ->limit(5)
                ->get();

            $previews = [];

            foreach ($partnerBills as $bill) {
                $previews[] = [
                    'bill_id' => $bill->id,
                    'bill_code' => $bill->code,
                    'client_name' => $bill->client?->name ?? 'N/A',
                    'partner_name' => $bill->partner?->name ?? 'N/A',
                    'status' => $bill->status->value,
                    'links' => [
                        'received_client_vi' => route('mail.preview.partner-bill-received', [$bill->id, 'client', 'vi']),
                        'received_client_en' => route('mail.preview.partner-bill-received', [$bill->id, 'client', 'en']),
                        'received_partner_vi' => route('mail.preview.partner-bill-received', [$bill->id, 'partner', 'vi']),
                        'received_partner_en' => route('mail.preview.partner-bill-received', [$bill->id, 'partner', 'en']),
                        'confirmed_partner_vi' => route('mail.preview.partner-bill-confirmed', [$bill->id, 'vi']),
                        'confirmed_partner_en' => route('mail.preview.partner-bill-confirmed', [$bill->id, 'en']),
                        'reminder_client_vi' => route('mail.preview.partner-bill-reminder', [$bill->id, 'client', 'vi']),
                        'reminder_client_en' => route('mail.preview.partner-bill-reminder', [$bill->id, 'client', 'en']),
                        'reminder_partner_vi' => route('mail.preview.partner-bill-reminder', [$bill->id, 'partner', 'vi']),
                        'reminder_partner_en' => route('mail.preview.partner-bill-reminder', [$bill->id, 'partner', 'en']),
                    ],
                ];
            }

            $invoices = Transaction::query()
                ->where('type', Transaction::TYPE_DEPOSIT)
                ->where('confirmed', true)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Transaction $transaction): array => [
                    'transaction_id' => $transaction->getKey(),
                    'invoice_number' => WalletTopUpInvoice::invoiceNumber($transaction),
                    'amount' => (int) data_get($transaction->meta, 'payos.request.amount', $transaction->amount),
                    'links' => [
                        'vi' => route('mail.preview.wallet-top-up-invoice', [$transaction->getKey(), 'vi']),
                        'en' => route('mail.preview.wallet-top-up-invoice', [$transaction->getKey(), 'en']),
                    ],
                ])
                ->all();

            $assetInvoices = FileProductBill::query()
                ->with('fileProduct')
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (FileProductBill $bill): array => [
                    'bill_id' => $bill->getKey(),
                    'invoice_number' => AssetPurchaseInvoice::invoiceNumber($bill),
                    'product' => $bill->fileProduct?->name,
                    'status' => $bill->status->value,
                    'amount' => (int) round((float) ($bill->final_total ?? $bill->total)),
                    'links' => [
                        'vi' => route('mail.preview.asset-purchase-invoice', [$bill->getKey(), 'vi']),
                        'en' => route('mail.preview.asset-purchase-invoice', [$bill->getKey(), 'en']),
                    ],
                ])
                ->all();

            return view('mail.preview.index', compact('previews', 'invoices', 'assetInvoices'));
        })->name('mail.preview.index');
    });
}
