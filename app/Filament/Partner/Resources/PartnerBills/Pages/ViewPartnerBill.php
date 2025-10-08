<?php

namespace App\Filament\Partner\Resources\PartnerBills\Pages;

use App\Enum\PartnerBillStatus;
use App\Filament\Partner\Resources\PartnerBills\PartnerBillResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

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
