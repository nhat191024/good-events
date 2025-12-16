<?php
// app/Notifications/PartnerAcceptedOrder.php
namespace App\Notifications;

use App\Models\PartnerBill;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PartnerAcceptedOrder
{
    public static function send(PartnerBill $bill, $user)
    {
        Notification::make()
            ->title(__('notification.partner_accepted_title'))
            ->body(__('notification.partner_accepted_body', [
                'code' => $bill->code,
                'partner_name' => $bill->partner->name
            ]))
            ->success()
            ->actions([
                Action::make('open')
                    ->label('Xem đơn')
                    ->url(route('client-orders.dashboard', ['order' => $bill->id])),
            ])
            ->sendToDatabase($user);
    }
}
