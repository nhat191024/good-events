<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    public const string STATUS_RINGING = 'ringing';

    public const string STATUS_ACTIVE = 'active';

    public const string STATUS_ENDED = 'ended';

    public const string TYPE_AUDIO = 'audio';

    public const string TYPE_VIDEO = 'video';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'thread_id',
        'initiated_by',
        'channel',
        'type',
        'status',
        'started_at',
        'ended_at',
        'expires_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function invites(): HasMany
    {
        return $this->hasMany(CallInvite::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CallParticipant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_RINGING, self::STATUS_ACTIVE])
            ->where('expires_at', '>', now());
    }

    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_RINGING, self::STATUS_ACTIVE], true)
            && $this->expires_at->isFuture();
    }
}
