<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FileProductBill> $bills
 * @property-read int|null $bills_count
 * @property-read \App\Models\Category $category
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FileProduct withoutTrashed()
 * @mixin \Eloquent
 */
class FileProduct extends Model implements HasMedia
{
    use SoftDeletes, HasSlug, InteractsWithMedia;

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

    //model relationships
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function bills()
    {
        return $this->hasMany(FileProductBill::class);
    }
}
