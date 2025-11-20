<?php

namespace App\Enum;

enum PaymentMethod: string
{
    //only bank transfer available for now
    case BANK_TRANSFER = 'bank_transfer';
    // case PAYPAL = 'paypal';
    // case CREDIT_CARD = 'credit_card';
    // case CASH = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => __('admin/fileProductBill.status.bank_transfer'),
        };
    }
}
