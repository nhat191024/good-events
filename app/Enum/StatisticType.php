<?php

namespace App\Enum;

enum StatisticType: string
{
    // Define different types of statistics for only partners
    case NUMBER_CUSTOMER = 'number_customer';
    case YEAR_OF_EXPERIENCE = 'year_of_experience';
    case SATISFACTION_RATE = 'satisfaction_rate';

        // Define different types of statistics for only clients
    case TOTAL_SPENT = 'total_spent';
    case ORDERS_PLACED = 'orders_placed';
    case MEMBER_SINCE = 'member_since';

        // Define different types of statistics for both partners and clients
    case COMPLETED_ORDERS = 'completed_orders';
    case CANCELLED_ORDERS_PERCENTAGE = 'cancelled_orders_percentage';

    public function audience(): string
    {
        return match ($this) {
            self::NUMBER_CUSTOMER, self::YEAR_OF_EXPERIENCE, self::SATISFACTION_RATE => ROLE::PARTNER,
            self::TOTAL_SPENT, self::ORDERS_PLACED, self::MEMBER_SINCE => ROLE::CLIENT,
            self::COMPLETED_ORDERS, self::CANCELLED_ORDERS_PERCENTAGE => 'both',
        };
    }

    /**
     * Get all enum cases for a specific audience.
     *
     * @param string $audience
     * @return list<self>
     */
    public static function forAudience(Role $audience): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn(self $case): bool => $case->audience() === $audience
        ));
    }
}
