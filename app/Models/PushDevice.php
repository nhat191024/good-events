<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushDevice extends Model
{
    public const string PLATFORM_IOS = 'ios';

    public const string PLATFORM_ANDROID = 'android';

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'device_id',
        'platform',
        'fcm_token',
        'voip_token',
        'last_seen_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['last_seen_at' => 'immutable_datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
