<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Refund reason for Flutterwave refunds.
 *
 * Represents the reason why a refund is being initiated.
 *
 * @see https://developer.flutterwave.com/reference/refunds_post
 */
enum RefundReason: string
{
    /**
     * Refund for duplicate charge
     */
    case DUPLICATE = 'duplicate';

    /**
     * Refund for fraudulent transaction
     */
    case FRAUDULENT = 'fraudulent';

    /**
     * Refund requested by customer
     */
    case REQUESTED_BY_CUSTOMER = 'requested_by_customer';

    /**
     * Refund for expired uncaptured charge
     */
    case EXPIRED_UNCAPTURED_CHARGE = 'expired_uncaptured_charge';
}

