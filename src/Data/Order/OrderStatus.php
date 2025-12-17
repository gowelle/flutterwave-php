<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Order;

/**
 * Order status values for filtering orders.
 *
 * @see https://developer.flutterwave.com/reference/orders_list
 */
enum OrderStatus: string
{
    case Completed = 'completed';
    case Pending = 'pending';
    case Authorized = 'authorized';
    case PartiallyCompleted = 'partially-completed';
    case Voided = 'voided';
    case Failed = 'failed';
}
