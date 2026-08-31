<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Cmgmyr\Messenger\Models\Message as BaseMessage;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $thread_id
 * @property int $user_id
 * @property string|null $client_message_id
 * @property int|null $call_id
 * @property int|null $call_duration_seconds
 * @property string $type
 * @property string|null $body
 * @property numeric|null $location_latitude
 * @property numeric|null $location_longitude
 * @property string|null $location_label
 * @property string|null $location_address
 * @property CarbonImmutable|null $deleted_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read array<int, array<string, mixed>> $attachments
 * @property-read array<string, mixed>|null $location
 * @property-read array<string, mixed>|null $call_summary
 * @property-read string $preview_text
 * @property-read MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, Participant> $participants
 * @property-read int|null $participants_count
 * @property-read Collection<int, Participant> $recipients
 * @property-read int|null $recipients_count
 * @property-read Thread|null $thread
 * @property-read User|null $user
 *
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
 *
 * @mixin \Eloquent
 */
class Message extends BaseMessage implements HasMedia
{
    use InteractsWithMedia;

    public const string TYPE_TEXT = 'text';

    public const string TYPE_IMAGE = 'image';

    public const string TYPE_LOCATION = 'location';

    public const string TYPE_CALL = 'call';

    public const string TYPE_PRICE_INCREASE_REQUEST = 'price_increase_request';

    public const string MEDIA_COLLECTION_CHAT_IMAGES = 'chat_images';

    protected $fillable = [
        'thread_id',
        'user_id',
        'client_message_id',
        'call_id',
        'call_duration_seconds',
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
        'call_summary',
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

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function priceIncreaseRequest(): HasOne
    {
        return $this->hasOne(PartnerBillPriceIncreaseRequest::class);
    }

    /** @return array<string, mixed>|null */
    public function getCallSummaryAttribute(): ?array
    {
        if ($this->type !== self::TYPE_CALL || $this->call_id === null) {
            return null;
        }

        return [
            'id' => $this->call?->uuid,
            'duration_seconds' => (int) ($this->call_duration_seconds ?? 0),
            'started_at' => $this->call?->started_at?->toIso8601String(),
            'ended_at' => $this->call?->ended_at?->toIso8601String(),
        ];
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
            self::TYPE_CALL => '[Cuộc gọi]',
            self::TYPE_PRICE_INCREASE_REQUEST => $this->priceIncreaseRequest
                ? 'Yêu cầu tăng giá lên '.number_format($this->priceIncreaseRequest->requested_total, 0, ',', '.').' đ'
                : '[Yêu cầu tăng giá]',
            default => (string) $this->body,
        };
    }
}
