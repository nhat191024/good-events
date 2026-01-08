<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\FileProduct */
class FileProductResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image' => $this->mediaUrl('thumbnails'),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'category' => $this->whenLoaded('category', function () {
                $cat = $this->category;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn () => [
                            'id' => $cat->parent->id,
                            'name' => $cat->parent->name,
                            'slug' => $cat->parent->slug,
                        ]
                    ),
                ];
            }),
            'tags' => $this->whenLoaded('tags', fn () => TagResource::collection($this->tags)),
        ];
    }
}
