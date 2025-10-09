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
            Action::make('complete')
                ->label(__('partner/bill.complete_order'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(__('partner/bill.complete_order_confirm_title'))
                ->modalDescription(__('partner/bill.complete_order_confirm_description'))
                ->modalSubmitActionLabel(__('partner/bill.confirm_complete'))
                ->modalIcon('heroicon-o-check-circle')
                ->visible(fn() => $this->record->status === PartnerBillStatus::CONFIRMED)
                ->action(function () {
                    $user =  Auth::user();
                    $balance = $user->balanceInt;
                    $fee_percentage = app(PartnerSettings::class)->fee_percentage;
                    $withdraw_amount = floor($this->record->final_total * ($fee_percentage / 100));

                    if ($balance < $withdraw_amount) {
                        $format_withdraw_amount = number_format($withdraw_amount) . ' VND';
                        $format_balance = number_format($balance) . ' VND';
                        Notification::make()
                            ->title(__('partner/bill.insufficient_balance', ['amount' => $format_withdraw_amount, 'balance' => $format_balance]))
                            ->danger()
                            ->send();
                        return;
                    }

                    //withdraw da moneyyyy! here come the moneyy! :)
                    $user->withdraw($withdraw_amount);

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
