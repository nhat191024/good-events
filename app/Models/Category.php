<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use SoftDeletes, HasSlug, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
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

    //model helper method
    public function hasPartnerCategories()
    {
        return $this->partnerCategories()->exists();
    }

    public function hasRentProducts()
    {
        return $this->rentProducts()->exists();
    }

    public function hasFileProducts()
    {
        return $this->fileProducts()->exists();
    }

    //model relationship
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function partnerCategories()
    {
        return $this->hasMany(PartnerCategory::class);
    }

    public function rentProducts()
    {
        return $this->hasMany(RentProduct::class);
    }

    public function fileProducts()
    {
        return $this->hasMany(FileProduct::class);
    }
}
