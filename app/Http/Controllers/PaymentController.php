<?php

namespace App\Http\Controllers;

use Bavix\Wallet\Models\Transaction;

use App\Enum\FileProductBillStatus;
use App\Models\FileProductBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Filament\Notifications\Notification;

class PaymentController extends Controller
{
    public function result(Request $request)
    {
        // Handle the payment result here
        $orderCode = $request->query('orderCode', 'unknown');
        $status = strtoupper((string) $request->query('status', 'unknown'));
        $billId = (int) $request->query('bill_id', 0);
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($billId) {
            $bill = FileProductBill::find($billId);

            if (!$bill || $bill->client_id !== $user->getAuthIdentifier()) {
                abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
            }

            if ($status === 'PAID') {
                $bill->forceFill([
                    'status' => FileProductBillStatus::PAID,
                    'final_total' => $bill->final_total ?? $bill->total,
                ])->save();

                return redirect()
                    ->route('client-orders.asset.dashboard', ['bill_id' => $bill->getKey()])
                    ->with('success', 'Thanh toán thành công. Bạn có thể tải tài liệu đã mua.');
            }

            $bill->forceFill([
                'status' => FileProductBillStatus::PENDING,
            ])->save();

            return redirect()
                ->route('client-orders.asset.dashboard', ['bill_id' => $bill->getKey()])
                ->with('error', 'Thanh toán không thành công. Đơn hàng vẫn ở trạng thái chờ thanh toán.');
        }

        if ($status === 'PAID') {
            $transactionId = intval(explode('1010', $orderCode)[0] ?? 0);
            $transaction = Transaction::find($transactionId);
            if ($transaction) {
                $metaData = $transaction->meta;

                // Confirm the transaction first
                $user->confirm($transaction);

                // Update the new balance in meta data
                $metaData['new_balance'] = $user->balanceInt;
                $transaction->meta = $metaData;
                $transaction->save();
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
