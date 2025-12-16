<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Virtual Account status enumeration.
 *
 * Represents the current state of a virtual account.
 */
enum VirtualAccountStatus: string
{
    /**
     * Virtual account is active and can receive payments
     */
    case ACTIVE = 'active';

    /**
     * Virtual account is inactive and cannot receive payments
     */
    case INACTIVE = 'inactive';

    /**
     * Create from Flutterwave API response status
     */
    public static function fromApiResponse(string $status): self
    {
        return match (mb_strtolower($status)) {
            'active' => self::ACTIVE,
            'inactive' => self::INACTIVE,
            default => self::ACTIVE,
        };
    }

    /**
     * Check if account is active
     */
    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Check if account is inactive
     */
    public function isInactive(): bool
    {
        return $this === self::INACTIVE;
    }
}

