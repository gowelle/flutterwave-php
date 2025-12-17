<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Order;

/**
 * Supported actions for updating orders.
 *
 * @see https://developer.flutterwave.com/reference/orders_put
 */
enum OrderAction: string
{
    case Void = 'void';
    case Capture = 'capture';
}
