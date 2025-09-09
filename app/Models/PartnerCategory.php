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
 * @property string $name
 * @property string $slug
 * @property int $category_id
 * @property float $min_price
 * @property float $max_price
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Category $category
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PartnerService> $partnerServices
 * @property-read int|null $partner_services_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereMaxPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereMinPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PartnerCategory withoutTrashed()
 * @mixin \Eloquent
 */
class PartnerCategory extends Model implements HasMedia
{
    use SoftDeletes, HasSlug, InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'min_price',
        'max_price',
        'description',
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
        return $this->belongsTo(Category::class);
    }

    public function partnerServices()
    {
        return $this->hasMany(PartnerService::class, 'category_id');
    }
}
