<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Request DTO for creating a transfer sender.
 *
 * Supports all Flutterwave sender types:
 * - generic_sender: Basic sender for most transfers
 * - bank_gbp: Sender for GBP bank transfers (requires full KYC)
 * - bank_eur: Sender for EUR bank transfers (requires full KYC)
 *
 * @see https://developer.flutterwave.com/reference/transfers_senders_create
 */
final readonly class CreateSenderRequest
{
    /**
     * @param string $type Sender type (generic_sender, bank_gbp, bank_eur)
     * @param array<string, mixed> $name Name object (first, middle?, last)
     * @param array<string, mixed>|null $phone Phone object (country_code, number)
     * @param string|null $email Customer email
     * @param array<string, mixed>|null $address Address object (city, country, line1, line2?, postal_code, state)
     */
    public function __construct(
        public string $type,
        public array $name,
        public ?array $phone = null,
        public ?string $email = null,
        public ?array $address = null,
    ) {}

    /**
     * Create a generic sender (basic sender for most transfers).
     *
     * Phone, email, and address are optional for generic senders.
     */
    public static function generic(
        string $firstName,
        string $lastName,
        ?string $middleName = null,
        ?array $phone = null,
        ?string $email = null,
        ?array $address = null,
    ): self {
        $name = array_filter([
            'first' => $firstName,
            'middle' => $middleName,
            'last' => $lastName,
        ], fn ($v) => $v !== null);

        return new self(
            type: 'generic_sender',
            name: $name,
            phone: $phone,
            email: $email,
            address: $address,
        );
    }

    /**
     * Create a GBP bank sender (for GBP bank transfers).
     *
     * Requires full KYC: name, phone, email, and address.
     *
     * @param array{country_code: string, number: string} $phone
     * @param array{city: string, country: string, line1: string, postal_code: string, state: string, line2?: string} $address
     */
    public static function bankGbp(
        string $firstName,
        string $lastName,
        array $phone,
        string $email,
        array $address,
        ?string $middleName = null,
    ): self {
        $name = array_filter([
            'first' => $firstName,
            'middle' => $middleName,
            'last' => $lastName,
        ], fn ($v) => $v !== null);

        return new self(
            type: 'bank_gbp',
            name: $name,
            phone: $phone,
            email: $email,
            address: $address,
        );
    }

    /**
     * Create a EUR bank sender (for EUR bank transfers).
     *
     * Requires full KYC: name, phone, email, and address.
     *
     * @param array{country_code: string, number: string} $phone
     * @param array{city: string, country: string, line1: string, postal_code: string, state: string, line2?: string} $address
     */
    public static function bankEur(
        string $firstName,
        string $lastName,
        array $phone,
        string $email,
        array $address,
        ?string $middleName = null,
    ): self {
        $name = array_filter([
            'first' => $firstName,
            'middle' => $middleName,
            'last' => $lastName,
        ], fn ($v) => $v !== null);

        return new self(
            type: 'bank_eur',
            name: $name,
            phone: $phone,
            email: $email,
            address: $address,
        );
    }

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return array_filter([
            'type' => $this->type,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
        ], fn ($value) => $value !== null);
    }
}
