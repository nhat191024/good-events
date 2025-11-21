<?php

namespace App\Http\Resources\Home;

use App\Helper\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

/** @mixin \App\Models\FileProduct */
class FileProductResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'image' => $this->getFirstMediaUrl('thumbnails'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'category' => $this->whenLoaded('category', function () {
                $cat = $this->category;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => $cat->description,
                    // 'image' => TemporaryImage::getTemporaryImageUrl($cat, $expireAt, 'thumbnails'),
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn () => [
                            'id' => $cat->parent->id,
                            'name' => $cat->parent->name,
                            // 'image' => $this->getTemporaryImageUrl($cat->parent, $expireAt),
                        ]
                    ),
                ];
            }),
        ];
    }
}
