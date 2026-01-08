<?php

namespace App\Http\Resources\Api;

use App\Enum\StatisticType;
use App\Models\Statistical;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** @mixin \App\Models\PartnerBill */
class PartnerBillHistoryResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        $review = null;

        if ($request->user()) {
            $reviewRow = DB::table('reviews')
                ->where('reviewable_type', User::class)
                ->where('reviewable_id', $this->partner_id)
                ->where('user_id', $request->user()->id)
                ->where('partner_bill_id', $this->id)
                ->first();

            if ($reviewRow) {
                $ratingValue = DB::table('ratings')
                    ->where('review_id', $reviewRow->id)
                    ->where('key', 'rating')
                    ->value('value');

                $review = [
                    'rating' => $ratingValue ? (int) $ratingValue : 0,
                    'comment' => $reviewRow->review ?? '',
                    'recommend' => (bool) ($reviewRow->recommend ?? false),
                ];
            }
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
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
            'category' => $this->whenLoaded('category', function () {
                $cat = $this->category;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'image' => $cat->getFirstMediaUrl('images', 'thumb'),
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn () => [
                            'name' => $cat->parent->name,
                            'image' => $cat->parent->getFirstMediaUrl('images', 'thumb'),
                        ]
                    ),
                ];
            }),
            'custom_event' => $this->custom_event,
            'event' => $this->whenLoaded('event', fn () => [
                'id' => $this->event?->id,
                'name' => $this->event?->name,
            ]),
            'partner' => $this->whenLoaded('partner', function () {
                $partner = $this->partner;

                return [
                    'id' => $partner->id,
                    'name' => $partner->name,
                    'statistics' => $this->when(
                        $partner->relationLoaded('statistics') && $partner->statistics,
                        fn () => $this->formatStatistics($partner)
                    ),
                    'partner_profile' => $this->when(
                        $partner->relationLoaded('partnerProfile') && $partner->partnerProfile,
                        fn () => [
                            'id' => $partner->partnerProfile->id,
                            'partner_name' => $partner->partnerProfile->partner_name,
                        ]
                    ),
                ];
            }),
            'voucher' => $this->whenLoaded('voucher', fn () => [
                'id' => $this->voucher?->id,
                'code' => $this->voucher?->code,
            ]),
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
