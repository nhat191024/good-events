<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\Tag */
class TagResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $name = $this->getTranslation('name', $locale) ?? $this->name;
        $slug = $this->getTranslation('slug', $locale) ?? $this->slug;

        return [
            'id' => $this->id,
            'name' => $name,
            'slug' => $slug,
            'type' => $this->type,
        ];
    }
}
