<?php

use App\Filament\Partner\Pages\CalendarPage;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\PartnerBill;
use App\Mail\PartnerBillReceived;
use App\Mail\PartnerBillConfirmed;
use App\Mail\PartnerBillReminder;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Calendar API routes
Route::middleware(['auth'])->group(function () {
    Route::get('/api/calendar/events', [CalendarPage::class, 'getEvents'])->name('calendar.events');
    Route::get('/api/calendar/locale', [CalendarPage::class, 'getLocaleData'])->name('calendar.locale');
});

// Language switcher for testing
Route::get('/locale/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'vi'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
})->name('locale.switch');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

// Mail Preview Routes (chỉ dành cho development)
if (app()->environment(['local', 'staging', 'testing'])) {
    Route::prefix('mail-preview')->group(function () {

        Route::get('partner-bill-received/{id}/{type}/{locale?}', function ($id, $type, $locale = 'vi') {
            $partnerBill = PartnerBill::with(['client', 'partner', 'category', 'event', 'details'])->findOrFail($id);

            if (!in_array($type, ['client', 'partner'])) {
                abort(400, 'Type must be either "client" or "partner"');
            }

            if (!in_array($locale, ['vi', 'en'])) {
                abort(400, 'Locale must be either "vi" or "en"');
            }

            return new PartnerBillReceived($partnerBill, $type, $locale);
        })->name('mail.preview.partner-bill-received');

        Route::get('partner-bill-confirmed/{id}/{type}/{locale?}', function ($id, $type, $locale = 'vi') {
            $partnerBill = PartnerBill::with(['client', 'partner', 'category', 'event', 'details'])->findOrFail($id);

            if (!in_array($type, ['client', 'partner'])) {
                abort(400, 'Type must be either "client" or "partner"');
            }

            if (!in_array($locale, ['vi', 'en'])) {
                abort(400, 'Locale must be either "vi" or "en"');
            }

            return new PartnerBillConfirmed($partnerBill, $type, $locale);
        })->name('mail.preview.partner-bill-confirmed');

        Route::get('partner-bill-reminder/{id}/{type}/{locale?}', function ($id, $type, $locale = 'vi') {
            $partnerBill = PartnerBill::with(['client', 'partner', 'category', 'event', 'details'])->findOrFail($id);

            if (!in_array($type, ['client', 'partner'])) {
                abort(400, 'Type must be either "client" or "partner"');
            }

            if (!in_array($locale, ['vi', 'en'])) {
                abort(400, 'Locale must be either "vi" or "en"');
            }

            return new PartnerBillReminder($partnerBill, $type, $locale);
        })->name('mail.preview.partner-bill-reminder');

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
                        'confirmed_client_vi' => route('mail.preview.partner-bill-confirmed', [$bill->id, 'client', 'vi']),
                        'confirmed_client_en' => route('mail.preview.partner-bill-confirmed', [$bill->id, 'client', 'en']),
                        'confirmed_partner_vi' => route('mail.preview.partner-bill-confirmed', [$bill->id, 'partner', 'vi']),
                        'confirmed_partner_en' => route('mail.preview.partner-bill-confirmed', [$bill->id, 'partner', 'en']),
                        'reminder_client_vi' => route('mail.preview.partner-bill-reminder', [$bill->id, 'client', 'vi']),
                        'reminder_client_en' => route('mail.preview.partner-bill-reminder', [$bill->id, 'client', 'en']),
                        'reminder_partner_vi' => route('mail.preview.partner-bill-reminder', [$bill->id, 'partner', 'vi']),
                        'reminder_partner_en' => route('mail.preview.partner-bill-reminder', [$bill->id, 'partner', 'en']),
                    ]
                ];
            }

            return view('mail.preview.index', compact('previews'));
        })->name('mail.preview.index');
    });
}
