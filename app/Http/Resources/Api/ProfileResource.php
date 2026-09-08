<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'avatar_url' => $this->getFirstMediaUrl('avatar') ?: $this->avatar_url,
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
            'selfie_image' => $this->identityImageUrl($partnerProfile?->selfie_image),
            'identity_card_number' => $partnerProfile?->identity_card_number,
            'front_identity_card_image' => $this->identityImageUrl($partnerProfile?->front_identity_card_image),
            'back_identity_card_image' => $this->identityImageUrl($partnerProfile?->back_identity_card_image),
        ];
    }

    private function identityImageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL) || Str::startsWith($path, ['/storage/', 'storage/'])) {
            return $path;
        }

        return Storage::disk('local')->temporaryUrl(Str::before($path, '?'), now()->addMinutes(5));
    }
}
