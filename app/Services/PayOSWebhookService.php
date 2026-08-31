<?php

namespace App\Services;

use App\Enum\FileProductBillStatus;
use App\Models\FileProductBill;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use DomainException;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PayOSWebhookService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(bool $success, string $code, array $data): string
    {
        if (! $success || $code !== '00' || ($data['code'] ?? null) !== '00') {
            return 'Webhook acknowledged without a successful payment.';
        }

        $orderCode = (int) $data['orderCode'];

        return DB::transaction(function () use ($orderCode, $data): string {
            $bill = FileProductBill::query()
                ->where('payos_order_code', $orderCode)
                ->lockForUpdate()
                ->first();

            if ($bill instanceof FileProductBill) {
                return $this->markFileProductBillAsPaid($bill, $data);
            }

            $transaction = $this->findWalletTransaction($orderCode);

            if ($transaction instanceof Transaction) {
                return $this->confirmWalletTransaction($transaction, $data);
            }

            return 'Webhook acknowledged for an unknown order.';
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function markFileProductBillAsPaid(FileProductBill $bill, array $data): string
    {
        $this->assertPaymentMatches(
            (int) round($bill->final_total ?? $bill->total),
            $bill->payos_payment_link_id,
            $data,
        );

        $payOSData = $bill->payos_data ?? [];
        $payOSData['webhook'] = $data;

        $bill->forceFill([
            'status' => FileProductBillStatus::PAID,
            'final_total' => $bill->final_total ?? $bill->total,
            'payos_payment_link_id' => $data['paymentLinkId'],
            'payos_data' => $payOSData,
        ])->save();

        return 'File product bill payment processed.';
    }

    private function findWalletTransaction(int $orderCode): ?Transaction
    {
        $transactionId = (int) explode('1010', (string) $orderCode, 2)[0];

        if ($transactionId <= 0) {
            return null;
        }

        $transaction = Transaction::query()->lockForUpdate()->find($transactionId);

        if (! $transaction instanceof Transaction) {
            return null;
        }

        return (int) data_get($transaction->meta, 'payos.order_code') === $orderCode
            ? $transaction
            : null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function confirmWalletTransaction(Transaction $transaction, array $data): string
    {
        $this->assertPaymentMatches(
            (int) data_get($transaction->meta, 'payos.request.amount'),
            data_get($transaction->meta, 'payos.payment_link_id'),
            $data,
        );

        $wasConfirmed = $transaction->confirmed;
        $user = $transaction->payable;

        if (! $user instanceof User) {
            throw new DomainException('The wallet transaction owner is invalid.');
        }

        if (! $wasConfirmed && ! $user->safeConfirm($transaction)) {
            throw new DomainException('The wallet transaction could not be confirmed.');
        }

        $transaction->refresh();
        $metaData = $transaction->meta;
        $metaData['new_balance'] = $user->balanceInt;
        $metaData['payos']['payment_link_id'] = $data['paymentLinkId'];
        $metaData['payos']['webhook'] = $data;
        $transaction->meta = $metaData;
        $transaction->save();

        if (! $wasConfirmed) {
            Notification::make()
                ->title(__('partner/transaction.notification.add_funds_success'))
                ->body(__('partner/transaction.notification.add_funds_success_message', [
                    'transactionId' => $transaction->getKey(),
                ]))
                ->success()
                ->actions([
                    Action::make('open')
                        ->label(__('notification.open_wallet'))
                        ->url(route('filament.partner.resources.wallets.index')),
                ])
                ->sendToDatabase($user);
        }

        return $wasConfirmed
            ? 'Wallet payment was already processed.'
            : 'Wallet payment processed.';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertPaymentMatches(int $expectedAmount, ?string $expectedPaymentLinkId, array $data): void
    {
        if ((int) $data['amount'] !== $expectedAmount) {
            throw new DomainException('The PayOS payment amount does not match the local payment.');
        }

        if ($expectedPaymentLinkId !== null && $data['paymentLinkId'] !== $expectedPaymentLinkId) {
            throw new DomainException('The PayOS payment link does not match the local payment.');
        }
    }
}
