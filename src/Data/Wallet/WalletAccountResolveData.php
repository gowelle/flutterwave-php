<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Wallet;

/**
 * Wallet Account Resolve Data Transfer Object
 *
 * @property-read string $provider Payment provider
 * @property-read string $identifier Unique identifier
 * @property-read string $name Account name
 */
final class WalletAccountResolveData
{
    public function __construct(
        public readonly string $provider,
        public readonly string $identifier,
        public readonly string $name,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            provider: $data['provider'] ?? '',
            identifier: $data['identifier'] ?? '',
            name: $data['name'] ?? '',
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'identifier' => $this->identifier,
            'name' => $this->name,
        ];
    }
}
