<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChatInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly int $invitationId,
        private readonly int $threadId,
        private readonly int $inviterId,
        private readonly string $inviterName,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'chat_invitation',
            'code' => 'CHAT_INVITATION',
            'title' => __('notification.chat_invitation.title'),
            'message' => __('notification.chat_invitation.body', [
                'inviter' => $this->inviterName,
            ]),
            'invitation_id' => $this->invitationId,
            'thread_id' => $this->threadId,
            'inviter_id' => $this->inviterId,
            'inviter_name' => $this->inviterName,
        ];
    }
}
