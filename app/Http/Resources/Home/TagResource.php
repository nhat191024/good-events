<?php

namespace App\Http\Resources\Home;

use App\Helper\TemporaryImage\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Tag */
class TagResource extends JsonResource
{
    public function toArray(Request $request)
    {
        $expireAt = now()->addMinutes(60 * 24);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            // 'description' => $this->description,
            // 'price' => $this->price,
            // 'image' => $this->getTemporaryImageUrl($this, $expireAt),
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,

        ];
    }
}
