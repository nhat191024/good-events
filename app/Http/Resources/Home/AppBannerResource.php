<?php

namespace App\Http\Resources\Home;

use App\Helper\TemporaryImage\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppBannerResource extends JsonResource
{
    public function toArray(Request $request)
    {
        // $expireAt = now()->addMinutes(200 * 24);

        return [
            'image_tag' => $this->img('thumb')->attributes(['class' => 'w-full h-full object-cover'])->toHtml()
        ];
    }
}
