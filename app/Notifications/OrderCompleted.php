<?php
// app/Notifications/OrderCompleted.php
namespace App\Notifications;

use App\Models\PartnerBill;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class OrderCompleted
{
    public static function send(PartnerBill $bill, $user)
    {
        Notification::make()
            ->title(__('notification.order_completed_title'))
            ->body(__('notification.order_completed_body', ['code' => $bill->code]))
            ->success()
            ->actions([
                Action::make('review')
                    ->label(__('notification.action_review'))
                    ->url(route('orders.index', ['single_order' => $bill->id]))
            ])
            ->sendToDatabase($user);
    }
}
