<?php

namespace App\Enum;

enum StatisticType: string
{
    // Define different types of statistics for only partners
    case NUMBER_CUSTOMER = 'number_customer';
    case SATISFACTION_RATE = 'satisfaction_rate';

        // Define different types of statistics for only clients
    case TOTAL_SPENT = 'total_spent';

        // Define different types of statistics for both partners and clients
    case ORDERS_PLACED = 'orders_placed';
    case COMPLETED_ORDERS = 'completed_orders';
    case CANCELLED_ORDERS_PERCENTAGE = 'cancelled_orders_percentage';

    public function audience(): string
    {
        return match ($this) {
            self::NUMBER_CUSTOMER, self::SATISFACTION_RATE => ROLE::PARTNER->value,
            self::TOTAL_SPENT => ROLE::CLIENT->value,
            self::ORDERS_PLACED, self::COMPLETED_ORDERS, self::CANCELLED_ORDERS_PERCENTAGE => 'both',
        };
    }

    /**
     * Get all enum cases for a specific audience.
     *
     * @param Role $audience
     * @return list<self>
     */
    public static function forAudience(Role $audience): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn(self $case): bool => $case->audience() === $audience->value || $case->audience() === 'both'
        ));
    }
}
