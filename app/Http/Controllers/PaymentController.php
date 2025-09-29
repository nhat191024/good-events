<?php

namespace App\Http\Controllers;

use Bavix\Wallet\Models\Transaction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Filament\Notifications\Notification;

class PaymentController extends Controller
{
    public function result(Request $request)
    {
        // Handle the payment result here
        $orderCode = $request->query('orderCode', 'unknown');
        $status = $request->query('status', 'unknown');
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($status === 'success') {
            $transactionId = intval(explode('1010', $orderCode)[0] ?? 0);
            $transaction = Transaction::find($transactionId);
            if ($transaction) {
                $transaction->confirm();
            }

            Notification::make()
                ->title(__('partner/transaction.notification.add_funds_success'))
                ->body(__('partner/transaction.notification.add_funds_success_message', ['transactionId' => $transactionId]))
                ->success()
                ->sendToDatabase($user);

            return redirect()->route('filament.partner.resources.wallets.index');
        } else {
            Notification::make()
                ->title(__('partner/transaction.notification.add_funds_failed'))
                ->body(__('partner/transaction.notification.add_funds_failed_message'))
                ->danger()
                ->sendToDatabase($user);

            return redirect()->route('filament.partner.resources.wallets.index');
        }
    }
}
