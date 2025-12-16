<?php
// app/Notifications/OrderCancelled.php
namespace App\Notifications;

use App\Models\PartnerBill;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OrderCancelled
{
    public static function send(PartnerBill $bill, $user)
    {
        Notification::make()
            ->title(__('notification.order_cancelled_title'))
            ->body(__('notification.order_cancelled_body', ['code' => $bill->code]))
            ->warning()
            ->actions([
                Action::make('open')
                    ->label('Xem đơn')
                    ->url(route('client-orders.dashboard', ['order' => $bill->id])),
            ])
            ->sendToDatabase($user);
    }
}
