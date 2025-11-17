<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Banner whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'type',
    ];

    /**
     * Summary of registerMediaCollections
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('banners')
            ->useDisk('public');
    }

    /**
     * Summary of registerMediaConversions
     * @param \Spatie\MediaLibrary\MediaCollections\Models\Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(736)
            ->height(420)
            ->sharpen(10)
            ->withResponsiveImages()
            ->format('webp')
            ->optimize()
            ->queued();
    }
}
