<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\PartnerCategory */
class PartnerCategoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'order' => $this->order,
            'parent_id' => $this->parent_id,
            'min_price' => $this->min_price,
            'max_price' => $this->max_price,
            'description' => $this->description,
            'image' => $this->mediaUrl('images'),
            'parent' => $this->when(
                $this->relationLoaded('parent') && $this->parent,
                fn () => [
                    'id' => $this->parent->id,
                    'name' => $this->parent->name,
                    'slug' => $this->parent->slug,
                ]
            ),
        ];
    }
}
