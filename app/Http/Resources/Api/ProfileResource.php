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
        $partnerProfile = $this->whenLoaded('partnerProfile');

        // $avatarUrl = $this->avatar_url;
        // if ($avatarUrl && str_contains($avatarUrl, 'ui-avatars.com')) {
        //     // If a format query exists, replace it with svg; otherwise append ?format=svg or &format=svg
        //     if (str_contains($avatarUrl, 'format=')) {
        //         $avatarUrl = preg_replace('/([&?])format=[^&]*/', '$1format=png', $avatarUrl);
        //     } else {
        //         $separator = str_contains($avatarUrl, '?') ? '&' : '?';
        //         $avatarUrl .= $separator . 'format=png';
        //     }
        // }

        return [
            'id' => $this->id,
            'is_legit' => $partnerProfile->is_legit ?? null,
            'avatar_url' => $this->avatar_url,
            'name' => $this->name,
            'partner_name' => $partnerProfile->partner_name ?? null,
            'phone' => $this->phone,
            'email' => $this->email,
            'bio' => $this->bio,
            'created_at' => optional($this->created_at)->toIso8601String(),

            'location' => $partnerProfile->location_id ? $partnerProfile->location->name . ' - ' . $partnerProfile->location->province->name ?? null : null,
            'selfie_image' => $partnerProfile->selfie_image ? Storage::disk('local')->temporaryUrl($partnerProfile->selfie_image, now()->addMinutes(5)) : null,
            'identity_card_number' => $partnerProfile->identity_card_number ?? null,
            'front_identity_card_image' => $partnerProfile->front_identity_card_image ? Storage::disk('local')->temporaryUrl($partnerProfile->front_identity_card_image, now()->addMinutes(5)) : null,
            'back_identity_card_image' => $partnerProfile->back_identity_card_image ? Storage::disk('local')->temporaryUrl($partnerProfile->back_identity_card_image, now()->addMinutes(5)) : null,
        ];
    }
}
