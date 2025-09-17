<?php

namespace App\Enum;

enum PartnerServiceStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('partner/service.status_pending'),
            self::APPROVED => __('partner/service.status_approved'),
            self::REJECTED => __('partner/service.status_rejected'),
        };
    }
}
