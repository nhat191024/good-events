<?php

namespace App\Enum;

enum FileProductBillStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('admin/fileProductBill.status.pending'),
            self::PAID => __('admin/fileProductBill.status.paid'),
            self::CANCELLED => __('admin/fileProductBill.status.cancelled'),
        };
    }

    public static function asSelectArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::PAID->value => self::PAID->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }
}
