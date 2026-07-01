<?php

namespace App\Http\Resources\Api\Partner;

use App\Models\PartnerBill;
use App\Models\User;
use Codebyray\ReviewRateable\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin Review */
class PartnerReviewResource extends JsonResource
{
    /**
     * @param Collection<int, PartnerBill> $bills
     */
    public function __construct(
        mixed $resource,
        private readonly Collection $bills,
    ) {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $bill = $this->bills->get($this->partner_bill_id);
        $reviewer = $bill?->client;
        $ratings = $this->ratings->mapWithKeys(fn($rating) => [
            $rating->key => (int) $rating->value,
        ]);

        return [
            'bill' => [
                'id' => $bill?->id,
                'code' => $bill?->code,
                'category' => $bill?->category?->name,
                'event' => $bill?->event?->name ?? $bill?->custom_event,
                'date' => optional($bill?->date)->toDateString(),
                'final_total' => $bill?->final_total,
            ],
            'user' => [
                'id' => $reviewer?->id,
                'name' => $reviewer?->name,
                'avatar_url' => $reviewer?->getFirstMediaUrl('avatar', 'avatar_webp') ? $reviewer->getFirstMediaUrl('avatar', 'avatar_webp') : $reviewer?->avatar_url,
            ],
            'review' => [
                'id' => $this->id,
                'rating' => $ratings->get('rating') ?? $ratings->get('overall'),
                'ratings' => $ratings,
                'comment' => $this->review,
                'recommend' => (bool) $this->recommend,
                'created_at' => optional($this->created_at)->toIso8601String(),
            ],
        ];
    }
}
