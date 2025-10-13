<?php
// app/Notifications/OrderStatusChanged.php
namespace App\Notifications;

use App\Models\PartnerBill;
use Filament\Notifications\Notification;

class OrderStatusChanged
{
    public static function send(PartnerBill $bill, $user, $newStatus)
    {
        $statusLabel = $newStatus->label();

        Notification::make()
            ->title(__('notification.order_status_changed_title'))
            ->body(__('notification.order_status_changed_body', [
                'code' => $bill->code,
                'status' => $statusLabel
            ]))
            ->info()
            ->sendToDatabase($user);
    }
}
