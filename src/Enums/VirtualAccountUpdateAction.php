<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Virtual Account update action enumeration.
 *
 * Represents the type of update operation to perform on a virtual account.
 */
enum VirtualAccountUpdateAction: string
{
    /**
     * Update the Bank Verification Number (BVN)
     */
    case UPDATE_BVN = 'update_bvn';

    /**
     * Update the account status (e.g., activate/deactivate)
     */
    case UPDATE_STATUS = 'update_status';

    /**
     * Create from Flutterwave API response action
     */
    public static function fromApiResponse(string $action): self
    {
        return match (mb_strtolower($action)) {
            'update_bvn' => self::UPDATE_BVN,
            'update_status' => self::UPDATE_STATUS,
            default => self::UPDATE_STATUS,
        };
    }

    /**
     * Check if action is BVN update
     */
    public function isBvnUpdate(): bool
    {
        return $this === self::UPDATE_BVN;
    }

    /**
     * Check if action is status update
     */
    public function isStatusUpdate(): bool
    {
        return $this === self::UPDATE_STATUS;
    }
}
