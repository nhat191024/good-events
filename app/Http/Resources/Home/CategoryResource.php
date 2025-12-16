<?php

namespace App\Http\Resources\Home;

use App\Helper\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Category */
class CategoryResource extends JsonResource
{
    public function toArray(Request $request)
    {
        $expireAt = now()->addMinutes(200 * 24);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->getFirstMediaUrl('images', 'thumb'),
            'parent' => $this->when(
                $this->relationLoaded('parent') && $this->parent,
                fn() => [
                    'id' => $this->parent->id,
                    'name' => $this->parent->name,
                ]
            ),
        ];
    }
}
