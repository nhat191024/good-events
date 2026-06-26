<?php

namespace App\Support;

use App\Enum\CacheKey;
use App\Models\Message;
use App\Models\User;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Support\Facades\Cache;

class ChatMessagePayload
{
    /**
     * @return array<string, mixed>
     */
    public static function message(Message $message): array
    {
        return [
            'id' => $message->id,
            'thread_id' => $message->thread_id,
            'user_id' => $message->user_id,
            'type' => $message->type,
            'body' => $message->body,
            'attachments' => $message->attachments,
            'location' => $message->location,
            'preview_text' => $message->preview_text,
            'created_at' => $message->created_at?->toIso8601String(),
            'updated_at' => $message->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forDispatch(Message $message, ?User $user = null): array
    {
        $messagePayload = self::message($message);

        return [
            ...$messagePayload,
            'created_at' => $message->created_at,
            'updated_at' => $message->updated_at,
            'user' => [
                'id' => $message->user_id,
                'name' => $user?->name ?? $message->user?->name ?? 'Ghost',
            ],
            'other_participant_ids' => self::otherParticipantIds($message->thread_id, $message->user_id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function response(Message $message, ?User $user = null): array
    {
        return [
            ...self::message($message),
            'user' => [
                'id' => $message->user_id,
                'name' => $user?->name ?? $message->user?->name ?? 'Ghost',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function broadcast(array $message): array
    {
        $createdAt = is_object($message['created_at'] ?? null) && method_exists($message['created_at'], 'toIso8601String')
            ? $message['created_at']->toIso8601String()
            : $message['created_at'] ?? null;
        $updatedAt = is_object($message['updated_at'] ?? null) && method_exists($message['updated_at'], 'toIso8601String')
            ? $message['updated_at']->toIso8601String()
            : $message['updated_at'] ?? null;

        return [
            'sender_id' => $message['user_id'],
            'message' => [
                'id' => $message['id'],
                'thread_id' => $message['thread_id'],
                'user_id' => $message['user_id'],
                'type' => $message['type'] ?? Message::TYPE_TEXT,
                'body' => $message['body'] ?? null,
                'attachments' => $message['attachments'] ?? [],
                'location' => $message['location'] ?? null,
                'preview_text' => $message['preview_text'] ?? (string) ($message['body'] ?? ''),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ],
            'user' => [
                'id' => $message['user_id'],
                'name' => $message['user']['name'] ?? 'Ghost',
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    private static function otherParticipantIds(int $threadId, int $userId): array
    {
        return array_values(array_diff(
            Cache::remember(
                CacheKey::THREAD_PARTICIPANT->value . "{$threadId}",
                now()->addWeek(),
                fn() => Participant::where('thread_id', $threadId)->pluck('user_id')->all()
            ),
            [$userId]
        ));
    }
}
