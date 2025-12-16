<?php
namespace App\Notifications;

use App\Models\PartnerBill;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ThreadCreated
{
    public static function send(PartnerBill $bill, $user)
    {
        Notification::make()
            ->title(__('notification.thread_created_title'))
            ->body(__('notification.thread_created_body', ['code' => $bill->code]))
            ->info()
            ->actions([
                Action::make('open')
                    ->label('Mở chat')
                    ->url(route('chat.index', ['chat' => $bill->thread_id])),
            ])
            ->sendToDatabase($user);
    }
}
