<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Refund status for Flutterwave refunds.
 *
 * Represents the current state of a refund.
 *
 * @see https://developer.flutterwave.com/docs/refunds
 */
enum RefundStatus: string
{
    /**
     * Refund created, not yet processed
     */
    case NEW = 'new';

    /**
     * Refund is being processed
     */
    case PENDING = 'pending';

    /**
     * Refund completed successfully
     */
    case SUCCEEDED = 'succeeded';

    /**
     * Refund failed
     */
    case FAILED = 'failed';

    /**
     * Create from Flutterwave API response status
     */
    public static function fromApiResponse(string $status): self
    {
        return match (mb_strtolower($status)) {
            'new' => self::NEW,
            'pending' => self::PENDING,
            'succeeded', 'successful', 'success', 'completed' => self::SUCCEEDED,
            'failed', 'declined' => self::FAILED,
            default => self::FAILED,
        };
    }

    /**
     * Check if refund is in a terminal state
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::SUCCEEDED,
            self::FAILED => true,
            self::NEW,
            self::PENDING => false,
        };
    }

    /**
     * Check if refund is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::SUCCEEDED;
    }

    /**
     * Check if refund is pending/processing
     */
    public function isPending(): bool
    {
        return \in_array($this, [self::NEW, self::PENDING], true);
    }
}

