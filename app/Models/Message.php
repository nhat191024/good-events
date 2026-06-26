<?php

namespace App\Models;

use Cmgmyr\Messenger\Models\Message as BaseMessage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
            ->map(fn(Media $media): array => [
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
