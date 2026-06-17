<?php

namespace App\Http\Resources\Api;

use App\Enum\StatisticType;
use App\Models\Statistical;
use Illuminate\Http\Request;

/** @mixin \App\Models\PartnerBill */
class PartnerBillHistoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $review = null;

        if ($this->relationLoaded('review') && $this->review) {
            $ratingValue = $this->review->ratings->firstWhere('key', 'rating')?->value
                ?? $this->review->ratings->firstWhere('key', 'overall')?->value;

            $review = [
                'rating' => $ratingValue ? (int) $ratingValue : 0,
                'comment' => $this->review->review ?? '',
                'recommend' => (bool) ($this->review->recommend ?? false),
            ];
        }

        $status = $this->status;
        $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'address' => $this->address,
            'date' => optional($this->date)->toDateString(),
            'start_time' => optional($this->start_time)->format('H:i:s'),
            'end_time' => optional($this->end_time)->format('H:i:s'),
            'total' => $this->total,
            'final_total' => $this->final_total,
            'note' => $this->note,
            'arrival_photo' => $this->mediaUrl('arrival_photo'),
            'status' => $statusValue,
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'category_name' => $this->whenLoaded('category', function () {
                return $this->category->name;
            }),
            'parent_category_name' => $this->whenLoaded('category', function () {
                return $this->category->parent->name;
            }),
            'category_image' => $this->whenLoaded('category', function () {
                return $this->category->getFirstMediaUrl('images', 'thumb');
            }),
            'event_name' => $this->custom_event ?? $this->whenLoaded('event', fn() => $this->event->name),
            'partner' => $this->whenLoaded('partner', function () {
                $partner = $this->partner;

                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'avatar_url' => $partner->getFirstMediaUrl('avatar', 'avatar_webp') ?: $partner->avatar_url,
                    'statistics' => $this->when(
                        $partner->relationLoaded('statistics') && $partner->statistics,
                        fn() => $this->formatStatistics($partner)
                    ),
                    'partner_profile' => $this->when(
                        $partner->relationLoaded('partnerProfile') && $partner->partnerProfile,
                        fn() => [
                            'id' => $partner->partnerProfile->id,
                            'partner_name' => $partner->partnerProfile->partner_name,
                        ]
                    ),
                ];
            }),
            // 'voucher' => $this->whenLoaded('voucher', fn () => [
            //     'id' => $this->voucher?->id,
            //     'code' => $this->voucher?->code,
            // ]),
            'review' => $review,
        ];
    }

    private function formatStatistics($partner): array
    {
        $stats = $partner->statistics
            ->whereIn('metrics_name', [
                StatisticType::AVERAGE_STARS->value,
                StatisticType::TOTAL_RATINGS->value,
            ])
            ->mapWithKeys(fn($stat) => [
                $stat->metrics_name => $stat->metrics_value,
            ]);

        if ($stats->count() < 2) {
            $stats = collect($stats)->merge(
                Statistical::calculatePartnerRatingMetrics($partner->id)
            );
        }

        return [
            StatisticType::AVERAGE_STARS->value => (float) ($stats[StatisticType::AVERAGE_STARS->value] ?? 0),
            StatisticType::TOTAL_RATINGS->value => (int) ($stats[StatisticType::TOTAL_RATINGS->value] ?? 0),
        ];
    }
}
