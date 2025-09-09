<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property float $price
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Category $category
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RentProduct withoutTrashed()
 * @mixin \Eloquent
 */
class RentProduct extends Model implements HasMedia
{
    use SoftDeletes, HasSlug, InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Summary of getActivitylogOptions
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty();
    }

    //model relationships
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
