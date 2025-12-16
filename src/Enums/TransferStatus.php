<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Transfer status for Flutterwave transfers.
 *
 * Represents the current state of a transfer.
 */
enum TransferStatus: string
{
    /**
     * Transfer created, not yet processed
     */
    case NEW = 'NEW';

    /**
     * Transfer is being processed
     */
    case PENDING = 'PENDING';

    /**
     * Transfer is in progress
     */
    case PROCESSING = 'PROCESSING';

    /**
     * Transfer completed successfully
     */
    case SUCCEEDED = 'SUCCEEDED';

    /**
     * Transfer failed
     */
    case FAILED = 'FAILED';

    /**
     * Transfer was reversed
     */
    case REVERSED = 'REVERSED';

    /**
     * Create from Flutterwave API response status
     */
    public static function fromApiResponse(string $status): self
    {
        return match (mb_strtoupper($status)) {
            'NEW' => self::NEW,
            'PENDING' => self::PENDING,
            'PROCESSING' => self::PROCESSING,
            'SUCCEEDED', 'SUCCESSFUL', 'SUCCESS', 'COMPLETED' => self::SUCCEEDED,
            'FAILED', 'DECLINED' => self::FAILED,
            'REVERSED' => self::REVERSED,
            default => self::FAILED,
        };
    }

    /**
     * Check if transfer is in a terminal state
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::SUCCEEDED,
            self::FAILED,
            self::REVERSED => true,
            self::NEW,
            self::PENDING,
            self::PROCESSING => false,
        };
    }

    /**
     * Check if transfer is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::SUCCEEDED;
    }

    /**
     * Check if transfer is pending/processing
     */
    public function isPending(): bool
    {
        return \in_array($this, [self::NEW, self::PENDING, self::PROCESSING], true);
    }
}
