<?php

namespace App\Http\Resources\Api;

use App\Models\PartnerCategoryAccessory;
use Illuminate\Http\Request;

/** @mixin PartnerCategoryAccessory */
class PartnerCategoryAccessoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
