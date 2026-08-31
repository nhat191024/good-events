<?php

namespace App\Http\Controllers\Api\Partner;

use App\Enum\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Partner\WalletTransactionCollection;
use App\Services\PaymentService;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * GET /partner/wallet/transactions
     *
     * Get transactions history from partner wallet
     *
     * @return JsonResponse
     */
    public function transactions(Request $request)
    {
        $user = $request->user();

        if (! $user->partnerProfile) {
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
     * @return JsonResponse
     */
    public function regenerateAddFundsLink(Request $request)
    {
        $user = $request->user();
        $amount = $request->input('amount');

        if (! $user->partnerProfile) {
            return response()->json(['message' => 'User not a partner.'], 404);
        }

        $id = date('YmdHis').rand(1000, 9999) + 1;
        $oldBalance = $user->balanceInt;
        $transaction = $user->deposit($amount, ['reason' => 'Nạp tiền vào ví qua QR', 'transaction_codes' => $id, 'old_balance' => $oldBalance, 'new_balance' => $oldBalance + $amount], false);

        $timestamp = time();
        $billId = intval($transaction->id.'1010'.substr((string) $timestamp, -3));

        $data = [
            'billId' => $billId,
            'billCode' => $id,
            'amount' => $amount,
            'buyerName' => $user->name,
            'buyerEmail' => $user->email,
            'buyerPhone' => $user->phone,
            'items' => [
                [
                    'name' => 'Nạp tiền vào ví qua QR',
                    'price' => $amount,
                    'quantity' => 1,
                ],
            ],
            'expiryTime' => intval(now()->addMinutes(10)->timestamp),
        ];

        $metaData = PaymentService::withPayOSMetadata($transaction->meta, $billId, $data);
        $transaction->meta = $metaData;
        $transaction->save();

        $paymentService = app(PaymentService::class);
        $response = $paymentService->processAppointmentPayment($data, PaymentMethod::QR_TRANSFER->gatewayChannel(), true);

        $metaData = PaymentService::withPayOSMetadata($metaData, $billId, $data, $response);
        $transaction->meta = $metaData;
        $transaction->save();

        if (isset($response['checkoutUrl'])) {
            return response()->json(['checkoutUrl' => $response['checkoutUrl']]);
        } else {
            return response()->json(['message' => 'Failed to initiate payment. Please try again.'], 500);
        }
    }

    /**
     * Confirm add funds
     *
     * @return JsonResponse
     */
    public function confirmAddFunds(Request $request)
    {
        $orderCode = $request->input('orderCode', 'unknown');
        $user = $request->user();
        $transactionId = intval(explode('1010', (string) $orderCode)[0] ?? 0);
        $transaction = Transaction::query()
            ->where('payable_type', $user->getMorphClass())
            ->where('payable_id', $user->getAuthIdentifier())
            ->find($transactionId);

        if (! $transaction instanceof Transaction) {
            return response()->json(['message' => 'Transaction not found.'], 404);
        }

        if (! $transaction->confirmed) {
            return response()->json([
                'message' => 'Payment is still being confirmed by PayOS.',
                'confirmed' => false,
            ], 202);
        }

        return response()->json([
            'message' => 'Payment confirmed.',
            'confirmed' => true,
            'new_balance' => $user->balanceInt,
        ]);
    }
}
