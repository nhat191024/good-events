<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;

class GenericResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
