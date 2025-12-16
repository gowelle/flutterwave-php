<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Request DTO for creating a transfer sender.
 */
final readonly class CreateSenderRequest
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $phoneNumber,
        public string $country,
        public ?string $address = null,
        public ?string $city = null,
    ) {}

    /**
     * Convert to API payload
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return array_filter([
            'name' => [
                'first' => $this->firstName,
                'last' => $this->lastName,
            ],
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
            'country' => $this->country,
            'address' => $this->address,
            'city' => $this->city,
        ], fn ($value) => $value !== null);
    }
}
