<?php

namespace App\Http\Resources\OrderHistory;

use App\Enum\StatisticType;
use App\Helper\TemporaryImage;
use App\Models\Statistical;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBill */
class PartnerBillResource extends JsonResource
{
    public function toArray(Request $request)
    {
        $expireAt = now()->addMinutes(200 * 24);
        $review = null;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'address' => $this->address,
            'date' => $this->date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total' => $this->total,
            'final_total' => $this->final_total,
            'note' => $this->note,
            'arrival_photo' => $this->getFirstMedia('arrival_photo')?->getUrl(),
            'status' => $this->status,
            'thread_id' => $this->thread_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => $this->whenLoaded('category', function () use ($expireAt) {
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
                    'max_price' => $cat->max_price,
                    'min_price' => $cat->min_price,
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
                                'id' => $cat->id,
                                'name' => $cat->name,
                                'image' => $url,
                                'image_tag' => $imageTag,
                            ];
                        }
                    ),
                ];
            }),
            'custom_event' => $this->custom_event,
            'event' => $this->whenLoaded('event', function () {
                $cat = $this->event;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                ];
            }),
            'partners' => $this->whenLoaded('details', function () {
                return [
                    'count' => $this->details->count(),
                ];
            }),
            'partner' => $this->whenLoaded('partner', function () {
                $cat = $this->partner;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'statistics' => $this->when(
                        $cat->relationLoaded('statistics') && $cat->statistics,
                        fn () => $this->formatStatistics($cat)
                    ),
                    'partner_profile' => $this->when(
                        $cat->relationLoaded('partnerProfile') && $cat->partnerProfile,
                        fn () => [
                            'id' => $cat->partnerProfile->id,
                            'partner_name' => $cat->partnerProfile->partner_name,
                        ]
                    ),
                ];
            }),
            'voucher' => $this->whenLoaded('voucher', function () {
                return [
                    'id' => $this->voucher?->id,
                    'code' => $this->voucher?->code,
                ];
            }),
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
            ->mapWithKeys(fn ($stat) => [
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
