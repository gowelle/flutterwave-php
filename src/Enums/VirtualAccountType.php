<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Virtual Account type enumeration.
 *
 * Represents the type of virtual account being created.
 */
enum VirtualAccountType: string
{
    /**
     * Static account - Permanent account with a fixed account number
     * Can be reused for multiple transactions
     */
    case STATIC = 'static';

    /**
     * Dynamic account - Temporary account for a specific transaction
     * Expires after specified duration
     */
    case DYNAMIC = 'dynamic';

    /**
     * Create from Flutterwave API response type
     */
    public static function fromApiResponse(string $type): self
    {
        return match (mb_strtolower($type)) {
            'static' => self::STATIC,
            'dynamic' => self::DYNAMIC,
            default => self::STATIC,
        };
    }

    /**
     * Check if account is static
     */
    public function isStatic(): bool
    {
        return $this === self::STATIC;
    }

    /**
     * Check if account is dynamic
     */
    public function isDynamic(): bool
    {
        return $this === self::DYNAMIC;
    }
}

