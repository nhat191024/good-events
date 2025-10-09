<?php

namespace App\Enum;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => __('partner/transaction.types.deposit'),
            self::WITHDRAW => __('partner/transaction.types.withdraw'),
        };
    }
}
