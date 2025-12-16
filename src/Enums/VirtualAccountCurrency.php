<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Virtual Account currency enumeration.
 *
 * Represents the supported currencies for virtual accounts (ISO 4217).
 */
enum VirtualAccountCurrency: string
{
    /**
     * Nigerian Naira
     */
    case NGN = 'NGN';

    /**
     * Ghanaian Cedi
     */
    case GHS = 'GHS';

    /**
     * Egyptian Pound
     */
    case EGP = 'EGP';

    /**
     * Kenyan Shilling
     */
    case KES = 'KES';

    /**
     * Create from Flutterwave API response currency
     */
    public static function fromApiResponse(string $currency): self
    {
        return match (mb_strtoupper($currency)) {
            'NGN' => self::NGN,
            'GHS' => self::GHS,
            'EGP' => self::EGP,
            'KES' => self::KES,
            default => self::NGN,
        };
    }

    /**
     * Get all supported currencies
     *
     * @return array<string>
     */
    public static function supported(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    /**
     * Check if currency requires account number
     * (EGP and KES require customer_account_number)
     */
    public function requiresAccountNumber(): bool
    {
        return \in_array($this, [self::EGP, self::KES], true);
    }
}
