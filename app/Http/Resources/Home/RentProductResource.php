<?php

namespace App\Http\Resources\Home;

use App\Helper\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\RentProduct */
class RentProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $expireAt = now()->addMinutes(200 * 24);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image' => TemporaryImage::getTemporaryImageUrl($this, $expireAt, 'thumbnails'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => $this->whenLoaded('category', function () use ($expireAt) {
                $category = $this->category;

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'parent' => $this->when(
                        $category->relationLoaded('parent') && $category->parent,
                        fn () => [
                            'id' => $category->parent->id,
                            'name' => $category->parent->name,
                        ]
                    ),
                ];
            }),
        ];
    }
}
