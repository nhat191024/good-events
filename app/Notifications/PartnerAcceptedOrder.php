<?php
// app/Notifications/PartnerAcceptedOrder.php
namespace App\Notifications;

use App\Models\PartnerBill;
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
            ->sendToDatabase($user);
    }
}
