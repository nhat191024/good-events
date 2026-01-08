<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\Category */
class CategoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'order' => $this->order,
            'is_show' => $this->is_show,
            'parent_id' => $this->parent_id,
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
