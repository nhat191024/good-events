<?php

namespace App\Filament\Partner\Resources\PartnerBills\Pages;

use App\Enum\PartnerBillStatus;

use App\Filament\Partner\Resources\PartnerBills\PartnerBillResource;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

use Illuminate\Support\Facades\Auth;

use App\Settings\PartnerSettings;

class ViewPartnerBill extends ViewRecord
{
    protected static string $resource = PartnerBillResource::class;

    public function getTitle(): string
    {
        return __('partner/bill.view_details');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAsInJob')
                ->label(__('partner/bill.mark_as_arrived'))
                ->icon('heroicon-o-map-pin')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading(__('partner/bill.mark_in_job_confirm_title'))
                ->modalDescription(__('partner/bill.mark_in_job_confirm'))
                ->modalSubmitActionLabel(__('partner/bill.confirm_arrived'))
                ->modalIcon('heroicon-o-map-pin')
                ->visible(fn() => $this->record->status === PartnerBillStatus::CONFIRMED)
                ->action(function () {
                    $this->record->status = PartnerBillStatus::IN_JOB;
                    $this->record->save();
                    Notification::make()
                        ->title(__('partner/bill.marked_as_in_job'))
                        ->success()
                        ->send();
                }),
            Action::make('complete')
                ->label(__('partner/bill.complete_order'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('partner/bill.complete_order_confirm_title'))
                ->modalDescription(__('partner/bill.complete_order_confirm_description'))
                ->modalSubmitActionLabel(__('partner/bill.confirm_complete'))
                ->modalIcon('heroicon-o-check-circle')
                ->visible(fn() => $this->record->status === PartnerBillStatus::IN_JOB)
                ->action(function () {
                    $user = Auth::user();
                    $balance = $user->balanceInt;
                    $feePercentage = app(PartnerSettings::class)->fee_percentage;
                    $withdrawAmount = floor($this->record->final_total * ($feePercentage / 100));

                    if ($balance < $withdrawAmount) {
                        $formatWithdrawAmount = number_format($withdrawAmount) . ' VND';
                        $formatBalance = number_format($balance) . ' VND';
                        Notification::make()
                            ->title(__('partner/bill.insufficient_balance', ['amount' => $formatWithdrawAmount, 'balance' => $formatBalance]))
                            ->danger()
                            ->send();
                        return;
                    }

                    // Withdraw money
                    $oldBalance = $user->balanceInt;
                    $transaction = $user->withdraw($withdrawAmount, ['reason' => 'Thu phí nền tảng show mã: ' . $this->record->code, 'old_balance' => $oldBalance]);
                    $newBalance = $user->balanceInt;
                    $transaction->meta = array_merge($transaction->meta ?? [], [
                        'new_balance' => $newBalance,
                    ]);
                    $transaction->save();

                    $this->record->status = PartnerBillStatus::COMPLETED;
                    $this->record->save();
                    Notification::make()
                        ->title(__('partner/bill.order_completed_success'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
