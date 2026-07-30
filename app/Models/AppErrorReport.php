<?php

namespace App\Models;

use App\Enum\AppErrorSeverity;
use App\Enum\AppErrorType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
            'occurred_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
