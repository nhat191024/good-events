<?php

namespace App\Http\Resources\OrderHistory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBill */
class PartnerBillResource extends JsonResource
{
    public function toArray(Request $request)
    {
        $expireAt = now()->addMinutes(60 * 24);
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
            'status' => $this->status,
            'thread_id' => $this->thread_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => $this->whenLoaded('category', function () use ($expireAt) {
                $cat = $this->category;

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'max_price' => $cat->max_price,
                    'min_price' => $cat->min_price,
                    'image' => $this->getTemporaryImageUrl($cat, $expireAt),
                    'parent' => $this->when(
                        $cat->relationLoaded('parent') && $cat->parent,
                        fn () => [
                            'id' => $cat->parent->id,
                            'name' => $cat->parent->name,
                            'image' => $this->getTemporaryImageUrl($cat->parent, $expireAt),
                        ]
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
                        fn () => $cat->statistics
                            ->whereIn('metrics_name', [
                                'average_stars',
                                'total_ratings',
                            ])
                            ->mapWithKeys(fn ($stat) => [
                                $stat->metrics_name => $stat->metrics_value,
                            ])
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
            'review' => $review,
        ];
    }

    private function getTemporaryImageUrl($model, $expireAt)
    {
        if (! $model || ! method_exists($model, 'getFirstTemporaryUrl')) {
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
