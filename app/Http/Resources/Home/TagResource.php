<?php

namespace App\Http\Resources\Home;

use App\Helper\TemporaryImage\TemporaryImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

/** @mixin \App\Models\Tag */
class TagResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name', 'vi'),
            'slug' => $this->getTranslation('slug', 'vi'),
        ];
    }
}
