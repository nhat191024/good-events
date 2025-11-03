<?php
// app/Notifications/OrderCreated.php
namespace App\Notifications;

use App\Models\PartnerBill;
use Filament\Notifications\Notification;

class OrderCreated
{
    public static function send(PartnerBill $bill, $user)
    {
        Notification::make()
            ->title(__('notification.order_created_title'))
            ->body(__('notification.order_created_body', ['code' => $bill->code]))
            ->info()
            ->sendToDatabase($user);
    }
}
