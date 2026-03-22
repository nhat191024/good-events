<?php

namespace App\Http\Resources\Api\Partner;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerService */
class PartnerServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'status' => $this->status,
            'category' => $this->category->name,
            'category_id' => (string) $this->category_id,
            'service_media' => $this->whenLoaded('serviceMedia', function () {
                return PartnerServiceMediaResource::collection($this->serviceMedia);
            }),
        ];
    }
}
