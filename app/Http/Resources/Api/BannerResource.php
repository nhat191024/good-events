<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

/** @mixin \App\Models\Banner */
class BannerResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'image' => $this->mediaUrl('banners'),
            'images' => $this->mediaUrls('banners'),
        ];
    }
}
