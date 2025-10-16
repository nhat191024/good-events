<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
