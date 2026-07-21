<?php

namespace App\Models;

use Cmgmyr\Messenger\Models\Message as BaseMessage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $thread_id
 * @property int $user_id
 * @property string|null $client_message_id
 * @property string $type
 * @property string|null $body
 * @property numeric|null $location_latitude
 * @property numeric|null $location_longitude
 * @property string|null $location_label
 * @property string|null $location_address
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read array<int, array<string, mixed>> $attachments
 * @property-read array<string, mixed>|null $location
 * @property-read string $preview_text
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Cmgmyr\Messenger\Models\Participant> $participants
 * @property-read int|null $participants_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Cmgmyr\Messenger\Models\Participant> $recipients
 * @property-read int|null $recipients_count
 * @property-read \App\Models\Thread|null $thread
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message query()
 * @method static Builder<static>|Message unreadForUser($userId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereClientMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereLocationAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereLocationLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereLocationLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereLocationLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Message withoutTrashed()
 * @mixin \Eloquent
 */
class Message extends BaseMessage implements HasMedia
{
    use InteractsWithMedia;

    public const string TYPE_TEXT = 'text';

    public const string TYPE_IMAGE = 'image';

    public const string TYPE_LOCATION = 'location';

    public const string MEDIA_COLLECTION_CHAT_IMAGES = 'chat_images';

    protected $fillable = [
        'thread_id',
        'user_id',
        'client_message_id',
        'type',
        'body',
        'location_latitude',
        'location_longitude',
        'location_label',
        'location_address',
    ];

    protected $appends = [
        'attachments',
        'location',
        'preview_text',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'location_latitude' => 'decimal:7',
            'location_longitude' => 'decimal:7',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(self::MEDIA_COLLECTION_CHAT_IMAGES)
            ->useDisk('public');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAttachmentsAttribute(): array
    {
        if ($this->type !== self::TYPE_IMAGE) {
            return [];
        }

        return $this
            ->getMedia(self::MEDIA_COLLECTION_CHAT_IMAGES)
            ->map(fn (Media $media): array => [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'size' => $media->size,
                'url' => $media->getFullUrl(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLocationAttribute(): ?array
    {
        if ($this->type !== self::TYPE_LOCATION || $this->location_latitude === null || $this->location_longitude === null) {
            return null;
        }

        return [
            'latitude' => (float) $this->location_latitude,
            'longitude' => (float) $this->location_longitude,
            'label' => $this->location_label,
            'address' => $this->location_address,
        ];
    }

    public function getPreviewTextAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_IMAGE => $this->body ?: '[Ảnh]',
            self::TYPE_LOCATION => $this->location_label ?: $this->location_address ?: '[Vị trí]',
            default => (string) $this->body,
        };
    }
}
