<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Wallet;

/**
 * Wallet Balance Data Transfer Object
 *
 * @property-read string $currency Currency code
 * @property-read float $availableBalance Available balance
 */
final class WalletBalanceData
{
    public function __construct(
        public readonly string $currency,
        public readonly float $availableBalance,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            currency: $data['currency'] ?? '',
            availableBalance: (float) ($data['available_balance'] ?? 0),
        );
    }

    /**
     * Create collection from API response array
     *
     * @param  array<int, array<string, mixed>>  $data
     * @return array<int, self>
     */
    public static function collection(array $data): array
    {
        return array_map(fn (array $item) => self::fromApiResponse($item), $data);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'currency' => $this->currency,
            'available_balance' => $this->availableBalance,
        ];
    }
}
