<?php

namespace App\Filament\Partner\Resources\Wallets\Pages;

use App\Filament\Partner\Resources\Wallets\WalletResource;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;

class ListWallets extends ListRecords
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_funds')
                ->label(__('partner/transaction.button.add_funds'))
                ->color('success')
                ->icon('heroicon-o-plus')
                ->modalHeading(__('partner/transaction.modal.add_funds_title'))
                ->modalDescription(__('partner/transaction.modal.add_funds_description'))
                ->modalSubmitActionLabel(__('partner/transaction.button.submit'))
                ->fillForm([
                    'amount' => '',
                ])
                ->schema(
                    [
                        TextInput::make('amount')
                            ->label(__('partner/transaction.form.amount'))
                            ->integer()
                            ->required()
                            ->minValue(10000)
                            ->step(10000)
                            ->maxValue(10000000)
                            ->suffix('VND')
                            ->placeholder(__('partner/transaction.form.amount_placeholder'))
                    ]
                )
                ->action(function (array $data): void {
                    $this->handleAddFunds($data['amount']);
                })
        ];
    }

    protected function handleAddFunds(int $amount): void
    {
        $user = Auth::user();
        $id = date('YmdHis') . rand(1000, 9999) + 1;
        $transaction = $user->deposit($amount, ['reason' => 'Nạp tiền vào ví qua QR', 'transaction_codes' => $id], false);

        $data = [
            'billId' => "{$transaction->id}1010",
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

        ds($data);

        $paymentService = app(PaymentService::class);
        $response = $paymentService->processAppointmentPayment($data, 'qr_transfer', false);

        if (isset($response['checkoutUrl'])) {
            $this->js('window.open("' . $response['checkoutUrl'] . '", "_blank");');
        } else {
            Notification::make()
                ->title('Error')
                ->body('Failed to initiate payment. Please try again.')
                ->danger()
                ->send();
        }
    }
}
