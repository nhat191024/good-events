<?php

namespace App\Http\Resources\OrderHistory;

use App\Enum\StatisticType;
use App\Models\Statistical;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PartnerBillDetail */
class PartnerBillDetailResource extends JsonResource {
    public function toArray(Request $request) {
        return [
            "id"=> $this->id,
            "total"=> $this->total,
            "status"=> $this->status,
            "created_at"=> $this->created_at,
            "updated_at"=> $this->updated_at,
            "partner" => $this->whenLoaded('partner', function() {
                $part = $this->partner;
                return [
                    'id' => $part->id,
                    'name' => $part->name,
                    'avatar' => $part->avatar,
                    'statistics' => $this->when(
                        $part->relationLoaded('statistics') && $part->statistics,
                        fn() => $this->formatStatistics($part)
                    ),
                    'partner_profile' => $this->when(
                        $part->relationLoaded('partnerProfile') && $part->partnerProfile,
                        fn() => [
                            'id' => $part->partnerProfile->id,
                            'partner_name' => $part->partnerProfile->partner_name,
                        ]
                    ),
                ];
            }),
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
