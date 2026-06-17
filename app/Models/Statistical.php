<?php

namespace App\Models;

use App\Enum\StatisticType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $user_id
 * @property string $metrics_name
 * @property string $metrics_value
 * @property string|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical whereMetadata($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical whereMetricsName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical whereMetricsValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Statistical whereUserId($value)
 * @mixin \Eloquent
 */
class Statistical extends Model
{
    protected $table = 'statistics';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'metrics_name',
        'metrics_value',
        'metadata',
    ];

    //model relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calculate and persist rating-related metrics for a partner.
     */
    public static function syncPartnerRatingMetrics(int $partnerId): array
    {
        $metrics = self::calculatePartnerRatingMetrics($partnerId);

        foreach ($metrics as $metric => $value) {
            self::updateOrCreate(
                [
                    'user_id' => $partnerId,
                    'metrics_name' => $metric,
                ],
                [
                    'metrics_value' => $value,
                ]
            );
        }

        return $metrics;
    }

    /**
     * Calculate rating-related metrics for a partner without persisting them.
     */
    public static function calculatePartnerRatingMetrics(int $partnerId): array
    {
        $ratingsPerReview = DB::table('ratings')
            ->select('review_id')
            ->selectRaw("MAX(CASE WHEN `key` = 'rating' THEN `value` END) as rating_value")
            ->selectRaw("MAX(CASE WHEN `key` = 'overall' THEN `value` END) as overall_value")
            ->where('key', 'rating')
            ->groupBy('review_id');

        $summary = DB::query()
            ->fromSub($ratingsPerReview, 'review_ratings')
            ->join('reviews', 'reviews.id', '=', 'review_ratings.review_id')
            ->where('reviews.reviewable_type',  Partner::class)
            ->where('reviews.reviewable_id', $partnerId)
            ->where('reviews.approved', true)
            ->selectRaw('COUNT(*) as total_ratings')
            ->selectRaw('AVG(COALESCE(review_ratings.rating_value, review_ratings.overall_value)) as average_stars')
            ->selectRaw('SUM(CASE WHEN COALESCE(review_ratings.rating_value, review_ratings.overall_value) >= 4 THEN 1 ELSE 0 END) as satisfied_ratings')
            ->first();

        $totalRatings = (int) ($summary->total_ratings ?? 0);
        $averageStars = (float) ($summary->average_stars ?? 0);
        $satisfiedRatings = (int) ($summary->satisfied_ratings ?? 0);
        $satisfactionRate = $totalRatings > 0 ? ($satisfiedRatings / $totalRatings) * 100 : 0;

        return [
            StatisticType::AVERAGE_STARS->value => round($averageStars, 2),
            StatisticType::TOTAL_RATINGS->value => $totalRatings,
            StatisticType::SATISFACTION_RATE->value => round($satisfactionRate, 2),
        ];
    }
}
