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
        $expireAt = now()->addMinutes(60 * 24);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => TemporaryImage::getTemporaryImageUrl($this, $expireAt, 'thumb'),
            'parent' => $this->when(
                $this->relationLoaded('parent') && $this->parent,
                fn () => [
                    'id' => $this->parent->id,
                    'name' => $this->parent->name,
                ]
            ),
            
            // 'category' => $this->whenLoaded('category', function () use ($expireAt) {
            //     $cat = $this->category;

            //     return [
            //         'id' => $cat->id,
            //         'name' => $cat->name,
            //         'image' => TemporaryImage::getTemporaryImageUrl($cat, $expireAt, 'thumbnails'),
            //         'parent' => $this->when(
            //             $cat->relationLoaded('parent') && $cat->parent,
            //             fn () => [
            //                 'id' => $cat->parent->id,
            //                 'name' => $cat->parent->name,
            //                 'image' => $this->getTemporaryImageUrl($cat->parent, $expireAt),
            //             ]
            //         ),
            //     ];
            // }),
            // }),
        ];
    }
}
