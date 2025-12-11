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
 * @property-read \App\Models\User $user
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
        $ratingQuery = DB::table('ratings')
            ->join('reviews', 'reviews.id', '=', 'ratings.review_id')
            ->where('reviews.reviewable_type', User::class)
            ->where('reviews.reviewable_id', $partnerId)
            ->where('reviews.approved', true)
            ->where('ratings.key', 'rating');

        $totalRatings = (int) (clone $ratingQuery)->count();
        $averageStars = $totalRatings > 0
            ? (float) ((clone $ratingQuery)->avg('ratings.value') ?? 0)
            : 0;

        return [
            StatisticType::AVERAGE_STARS->value => round($averageStars, 2),
            StatisticType::TOTAL_RATINGS->value => $totalRatings,
        ];
    }
}
