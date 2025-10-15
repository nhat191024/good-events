<?php

namespace App\Http\Resources\OrderHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PartnerBillHistoryResource extends JsonResource
{
    public function toArray(Request $request)
    {
        $expireAt = now()->addMinutes(60 * 24);
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
            "final_total" => $this->final_total,
            "note" => $this->note,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,

            "category" => $this->whenLoaded('category', function () use ($expireAt) {
                $cat = $this->category;
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'image' => $cat->getFirstTemporaryUrl($expireAt, 'images'),
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn() => [
                            'name' => $cat->parent->name,
                            'image' => $cat->parent->getFirstTemporaryUrl($expireAt, 'images'),
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
                        fn() => $cat->statistics
                            ->whereIn('metrics_name', [
                                'average_stars',
                                'total_ratings'
                            ])
                            ->mapWithKeys(fn($stat) => [
                                $stat->metrics_name => $stat->metrics_value,
                            ])
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

            "review" => $review,
        ];
    }
}
