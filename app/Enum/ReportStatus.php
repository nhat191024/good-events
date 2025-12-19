<?php

namespace App\Enum;

enum ReportStatus: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case RESOLVED = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('report.status.pending'),
            self::REVIEWED => __('report.status.reviewed'),
            self::RESOLVED => __('report.status.resolved'),
        };
    }
}
