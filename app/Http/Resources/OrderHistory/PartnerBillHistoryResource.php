<?php

namespace App\Http\Resources\OrderHistory;

use App\Enum\StatisticType;
use App\Models\Statistical;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerBillHistoryResource extends JsonResource
{
    public function toArray(Request $request)
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

        return [
            "id" => $this->id,
            "code" => $this->code,
            "address" => $this->address,
            "date" => $this->date,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "total" => $this->total,
            "final_total" => $this->final_total,
            "note" => $this->note,
            'booking_photos' => $this->getMedia('booking_photos')
                ->map(fn ($media): string => $media->getUrl())
                ->values()
                ->all(),
            'arrival_photo' => $this->getFirstMedia('arrival_photo')?->getUrl(),
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,

            "category" => $this->whenLoaded('category', function () {
                $cat = $this->category;
                $image = $cat?->getFirstMedia('images');
                $url = $image?->getUrl();
                $imageTag = $image?->img('thumb')->attributes([
                    'class' => 'w-full h-full object-cover lazy-image',
                    'loading' => 'lazy',
                    'alt' => $cat->name,
                ])->toHtml();
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'image' => $url,
                    'image_tag' => $imageTag,
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        function () {
                            $cat = $this->category?->parent;
                            $image = $cat?->getFirstMedia('images');
                            $url = $image?->getUrl();
                            $imageTag = $image?->img('thumb')->attributes([
                                'class' => 'w-full h-full object-cover lazy-image',
                                'loading' => 'lazy',
                                'alt' => $cat->name,
                            ])->toHtml();

                            return [
                                'name' => $cat->name,
                                'image' => $url,
                                'image_tag' => $imageTag,
                            ];
                        }
                    ),
                ];
            }),

            "custom_event" => $this->custom_event,
            "event" => $this->whenLoaded("event", function () {
                $cat = $this->event;
                return [
                    "id" => $cat->id,
                    "name" => $cat->name
                ];
            }),

            "partner" => $this->whenLoaded("partner", function () {
                $cat = $this->partner;
                return [
                    "id" => $cat->id,
                    "name" => $cat->name,
                    'statistics' => $this->when(
                        $cat->relationLoaded('statistics') && $cat->statistics,
                        fn() => $this->formatStatistics($cat)
                    ),
                    'partner_profile' => $this->when(
                        $cat->relationLoaded('partnerProfile') && $cat->partnerProfile,
                        fn() => [
                            'id' => $cat->partnerProfile->id,
                            'partner_name' => $cat->partnerProfile->partner_name,
                        ]
                    ),
                ];
            }),

            "voucher" => $this->whenLoaded('voucher', function () {
                return [
                    'id' => $this->voucher?->id,
                    'code' => $this->voucher?->code,
                ];
            }),

            "review" => $review,
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
