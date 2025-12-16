<?php

namespace App\Enum;

enum PaymentMethod: string
{
    case QR_TRANSFER = 'qr_transfer';

    public function label(): string
    {
        return match ($this) {
            self::QR_TRANSFER => __('admin/fileProductBill.payment_method.qr_transfer'),
        };
    }

    public function description(): ?string
    {
        return match ($this) {
            self::QR_TRANSFER => __('admin/fileProductBill.description.qr_transfer'),
        };
    }

    public function gatewayChannel(): string
    {
        return match ($this) {
            self::QR_TRANSFER => 'qr_transfer',
        };
    }

    /**
     * Return an array suitable for front-end selection list
     * @return array<int, array{code:string,name:string,description:string|null}>
     */
    public static function toOptions(): array
    {
        return array_map(fn (self $value) => [
            'code' => $value->value,
            'name' => $value->label(),
            'description' => $value->description(),
        ], self::cases());
    }
}
