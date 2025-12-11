<?php

namespace App\Http\Resources\OrderHistory;

use App\Enum\StatisticType;
use App\Helper\TemporaryImage;
use App\Models\Statistical;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PartnerBillHistoryResource extends JsonResource
{
    public function toArray(Request $request)
    {
        $expireAt = now()->addMinutes(200 * 24);
        $review = null;

        if ($request->user()) {
            // lấy review record
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
            'arrival_photo' => TemporaryImage::getTemporaryImageUrl($this, $expireAt,'arrival_photo'),
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,

            "category" => $this->whenLoaded('category', function () use ($expireAt) {
                $cat = $this->category;
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'image' => $this->getTemporaryImageUrl($cat, $expireAt),
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn() => [
                            'name' => $cat->parent->name,
                            'image' => $this->getTemporaryImageUrl($cat->parent, $expireAt),
                        ]
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

            "partner" => $this->whenLoaded("partner", function () use ($expireAt) {
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

    private function getTemporaryImageUrl($model, $expireAt)
    {
        if (!$model || !method_exists($model, 'getFirstTemporaryUrl')) {
            return null;
        }

        try {
            return $model->getFirstTemporaryUrl($expireAt, 'images');
        } catch (\Throwable $e) {
            return method_exists($model, 'getFirstMediaUrl')
                ? $model->getFirstMediaUrl('images')
                : null;
        }
    }
}
