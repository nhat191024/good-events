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
}
