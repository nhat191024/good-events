<?php

namespace App\Models;

use App\Enum\AppErrorSeverity;
use App\Enum\AppErrorType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AppErrorReport extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'severity',
        'custom_type',
        'error_code',
        'message',
        'source',
        'stack_trace',
        'context',
        'api_method',
        'api_url',
        'api_status_code',
        'api_request',
        'api_response',
        'app_version',
        'platform',
        'os_version',
        'device_model',
        'ip_address',
        'user_agent',
        'occurred_at',
        'checked_at',
        'checked_by',
        'occurrence_count',
        'first_occurred_at',
        'last_occurred_at',
        'merged_at',
        'merged_by',
        'merged_into_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AppErrorType::class,
            'severity' => AppErrorSeverity::class,
            'context' => 'array',
            'api_request' => 'array',
            'api_response' => 'array',
            'occurrence_count' => 'integer',
            'occurred_at' => 'immutable_datetime',
            'checked_at' => 'immutable_datetime',
            'first_occurred_at' => 'immutable_datetime',
            'last_occurred_at' => 'immutable_datetime',
            'merged_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by')->withTrashed();
    }

    public function mergedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by')->withTrashed();
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(self::class, 'merged_into_id');
    }

    public function mergedReports(): HasMany
    {
        return $this->hasMany(self::class, 'merged_into_id');
    }
}
