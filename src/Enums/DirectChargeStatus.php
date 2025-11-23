<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

use App\Domain\Payment\Enums\PaymentStatus;

/**
 * Direct charge status for Flutterwave orchestrator flow.
 *
 * Represents the current state of a charge in the direct charge flow,
 * which may require multiple authorization steps.
 */
enum DirectChargeStatus: string
{
    /**
     * Charge created, awaiting authorization or processing
     */
    case PENDING = 'pending';

    /**
     * Charge requires additional action from customer (PIN, OTP, redirect, etc.)
     */
    case REQUIRES_ACTION = 'requires_action';

    /**
     * Charge successfully completed
     */
    case SUCCEEDED = 'succeeded';

    /**
     * Charge failed (declined, insufficient funds, etc.)
     */
    case FAILED = 'failed';

    /**
     * Charge was cancelled by customer or system
     */
    case CANCELLED = 'cancelled';

    /**
     * Charge timed out waiting for authorization
     */
    case TIMEOUT = 'timeout';

    /**
     * Create from Flutterwave API response status
     */
    public static function fromApiResponse(string $status): self
    {
        return match (mb_strtolower($status)) {
            'succeeded', 'successful', 'success' => self::SUCCEEDED,
            'pending' => self::PENDING,
            'requires_action' => self::REQUIRES_ACTION,
            'failed', 'declined' => self::FAILED,
            'cancelled', 'canceled' => self::CANCELLED,
            'timeout', 'expired' => self::TIMEOUT,
            default => self::FAILED,
        };
    }

    /**
     * Check if charge is in a terminal state (no further actions possible)
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::SUCCEEDED,
            self::FAILED,
            self::CANCELLED,
            self::TIMEOUT => true,
            self::PENDING,
            self::REQUIRES_ACTION => false,
        };
    }

    /**
     * Check if charge is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::SUCCEEDED;
    }

    /**
     * Check if charge requires action
     */
    public function requiresAction(): bool
    {
        return $this === self::REQUIRES_ACTION;
    }
}
