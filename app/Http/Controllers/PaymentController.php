<?php

namespace App\Http\Controllers;

use App\Enum\FileProductBillStatus;
use App\Models\FileProductBill;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function result(Request $request)
    {
        $orderCode = $request->query('orderCode', 'unknown');
        $billId = (int) $request->query('bill_id', 0);
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($billId) {
            $bill = FileProductBill::find($billId);

            if (! $bill || $bill->client_id !== $user->getAuthIdentifier()) {
                abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
            }

            if ($bill->status === FileProductBillStatus::PAID) {
                return redirect()
                    ->route('client-orders.asset.dashboard', ['bill_id' => $bill->getKey()])
                    ->with('success', 'Thanh toán thành công. Bạn có thể tải tài liệu đã mua.');
            }

            return redirect()
                ->route('client-orders.asset.dashboard', ['bill_id' => $bill->getKey()])
                ->with('warning', 'Thanh toán đang được PayOS xác nhận. Đơn hàng hiện vẫn ở trạng thái chờ.');
        }

        $transactionId = intval(explode('1010', (string) $orderCode)[0] ?? 0);
        $transaction = Transaction::query()
            ->where('payable_type', $user->getMorphClass())
            ->where('payable_id', $user->getAuthIdentifier())
            ->find($transactionId);

        $message = $transaction instanceof Transaction && $transaction->confirmed
            ? 'Nạp tiền thành công.'
            : 'Giao dịch nạp ví đang được PayOS xác nhận.';

        return redirect()
            ->route('filament.partner.resources.wallets.index')
            ->with($transaction?->confirmed ? 'success' : 'warning', $message);
    }
}
