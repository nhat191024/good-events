<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatInvitation extends Model
{
    public const string STATUS_PENDING = 'pending';

    public const string STATUS_ACCEPTED = 'accepted';

    public const string STATUS_LEFT = 'left';

    /** @var list<string> */
    protected $fillable = [
        'thread_id',
        'user_id',
        'invited_by_user_id',
        'status',
        'accepted_at',
        'left_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'accepted_at' => 'immutable_datetime',
            'left_at' => 'immutable_datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
