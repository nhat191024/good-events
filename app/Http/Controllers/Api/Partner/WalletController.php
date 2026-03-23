<?php

namespace App\Http\Controllers\Api\Partner;

use Bavix\Wallet\Models\Transaction;

use App\Enum\PaymentMethod;
use App\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\WalletTransactionCollection;

use Illuminate\Http\Request;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

class WalletController extends Controller
{

    /**
     * GET /partner/wallet/transactions
     *
     * Get transactions history from partner wallet
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions(Request $request)
    {
        $user = $request->user();

        if (!$user->partnerProfile) {
            return response()->json(['message' => 'User not a partner.'], 404);
        }

        $transactions = $user->walletTransactions()->where('confirmed', true)->latest()->get();

        return response()->json([
            'data' => new WalletTransactionCollection($transactions),
        ]);
    }

    /**
     * POST /partner/wallet/regenerate-add-funds-link
     *
     * Regenerate add funds link for partner wallet
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function regenerateAddFundsLink(Request $request)
    {
        $user = $request->user();
        $amount = $request->input('amount');

        if (!$user->partnerProfile) {
            return response()->json(['message' => 'User not a partner.'], 404);
        }

        $id = date('YmdHis') . rand(1000, 9999) + 1;
        $oldBalance = $user->balanceInt;
        $transaction = $user->deposit($amount, ['reason' => 'Nạp tiền vào ví qua QR', 'transaction_codes' => $id, 'old_balance' => $oldBalance, 'new_balance' => $oldBalance + $amount], false);

        $timestamp = time();
        $billId = $transaction->id . '1010' . $timestamp;

        $data = [
            'billId' => $billId,
            'billCode' => $id,
            'amount' => $amount,
            'buyerName' => $user->name,
            'buyerEmail' => $user->email,
            'buyerPhone' => $user->phone,
            'items' => [
                [
                    'name' => "Nạp tiền vào ví qua QR",
                    'price' => $amount,
                    'quantity' => 1
                ]
            ],
            'expiryTime' => intval(now()->addMinutes(10)->timestamp)
        ];

        $paymentService = app(PaymentService::class);
        $response = $paymentService->processAppointmentPayment($data, PaymentMethod::QR_TRANSFER->gatewayChannel(), true);

        if (isset($response['checkoutUrl'])) {
            return response()->json(['checkoutUrl' => $response['checkoutUrl']]);
        } else {
            return response()->json(['message' => 'Failed to initiate payment. Please try again.'], 500);
        }
    }

    /**
     * Confirm add funds
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmAddFunds(Request $request)
    {
        $orderCode = $request->input('orderCode', 'unknown');
        $status = strtoupper((string) $request->input('status', 'unknown'));
        $user = $request->user();

        if ($status === 'PAID') {
            $transactionId = intval(explode('1010', $orderCode)[0] ?? 0);
            $transaction = Transaction::find($transactionId);

            if ($transaction instanceof Transaction) {
                $metaData = $transaction->meta;

                $user->confirm($transaction);

                $metaData['new_balance'] = $user->balanceInt;
                $transaction->meta = $metaData;
                $transaction->save();
            } else {
                return response()->json(['message' => 'Transaction not found.'], 404);
            }

            Notification::make()
                ->title(__('partner/transaction.notification.add_funds_success'))
                ->body(__('partner/transaction.notification.add_funds_success_message', ['transactionId' => $transactionId]))
                ->success()
                ->actions([
                    Action::make('open')
                        ->label(__('notification.open_wallet'))
                        ->url(route('filament.partner.resources.wallets.index')),
                ])
                ->sendToDatabase($user);
        } else {
            return response()->json(['message' => 'Payment not successful.'], 400);
        }

        return response()->json(['message' => 'Payment confirmation processed.', 'new_balance' => $user->balanceInt]);
    }
}
