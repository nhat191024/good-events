<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\User */
class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $partnerProfile = $this->relationLoaded('partnerProfile') ? $this->partnerProfile : null;

        return [
            'id' => $this->id,
            'is_legit' => $partnerProfile?->is_legit,
            'avatar_url' => $this->avatar_url,
            'name' => $this->name,
            'partner_name' => $partnerProfile?->partner_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'bio' => $this->bio,
            'video_url' => $partnerProfile?->video_url,
            'created_at' => optional($this->created_at)->toIso8601String(),

            'location' => $partnerProfile?->location_id
                ? ($partnerProfile->location?->name . ' - ' . $partnerProfile->location?->province?->name)
                : null,
            'selfie_image' => $partnerProfile?->selfie_image
                ? Storage::disk('local')->temporaryUrl($partnerProfile->selfie_image, now()->addMinutes(5))
                : null,
            'identity_card_number' => $partnerProfile?->identity_card_number,
            'front_identity_card_image' => $partnerProfile?->front_identity_card_image
                ? Storage::disk('local')->temporaryUrl($partnerProfile->front_identity_card_image, now()->addMinutes(5))
                : null,
            'back_identity_card_image' => $partnerProfile?->back_identity_card_image
                ? Storage::disk('local')->temporaryUrl($partnerProfile->back_identity_card_image, now()->addMinutes(5))
                : null,
        ];
    }
}
