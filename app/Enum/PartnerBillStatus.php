<?php

namespace App\Enum;

enum PartnerBillStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case EXPIRED = 'expired';
    case IN_JOB = 'in_job';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('partner/bill.status_pending'),
            self::CONFIRMED => __('partner/bill.status_confirmed'),
            self::IN_JOB => __('partner/bill.status_in_job'),
            self::COMPLETED => __('partner/bill.status_completed'),
            self::EXPIRED => __('partner/bill.status_expired'),
            self::CANCELLED => __('partner/bill.status_cancelled'),
        };
    }

    public static function asSelectArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::CONFIRMED->value => self::CONFIRMED->label(),
            self::IN_JOB->value => self::IN_JOB->label(),
            self::COMPLETED->value => self::COMPLETED->label(),
            self::EXPIRED->value => self::EXPIRED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}
