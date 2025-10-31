<?php

namespace App\Models;

use Spatie\Tags\Tag as baseModel;
use Spatie\Sluggable\SlugOptions;

class Tag extends baseModel
{
    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
