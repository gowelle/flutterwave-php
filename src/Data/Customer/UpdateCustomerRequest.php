<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Customer;

/**
 * Request DTO for updating customers via the Flutterwave API.
 *
 * Only email is required per v4; name, phone and address are optional but recommended.
 * Phone must be an object with country_code (ISO 3166 alpha-3) and number (7-10 digits without country code).
 *
 * @see https://developer.flutterwave.com/reference/customers_put
 */
final readonly class UpdateCustomerRequest
{
    /**
     * @param  string  $email  Customer email address (required)
     * @param  string|null  $firstName  Customer first name (optional)
     * @param  string|null  $lastName  Customer last name (optional)
     * @param  array{country_code: string, number: string}|null  $phone  Customer phone: country_code (ISO 3166 alpha-3), number (7-10 digits, no country code) (optional)
     * @param  string|null  $middleName  Customer middle name (optional)
     * @param  array{line1: string, line2?: string, city: string, state: string, postal_code: string, country: string}|null  $address  Customer address (optional)
     */
    public function __construct(
        public string $email,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?array $phone = null,
        public ?string $middleName = null,
        public ?array $address = null,
    ) {}

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $payload = ['email' => $this->email];

        $name = array_filter([
            'first' => $this->firstName,
            'middle' => $this->middleName,
            'last' => $this->lastName,
        ], fn ($value) => $value !== null && $value !== '');

        if ($name !== []) {
            $payload['name'] = $name;
        }

        if ($this->phone !== null && $this->phone !== []) {
            $countryCode = trim((string) ($this->phone['country_code'] ?? ''));
            $number = trim((string) ($this->phone['number'] ?? ''));
            if ($countryCode !== '' && $number !== '') {
                $payload['phone'] = [
                    'country_code' => $countryCode,
                    'number' => $number,
                ];
            }
        }

        if ($this->address !== null && $this->address !== []) {
            $payload['address'] = array_filter([
                'line1' => $this->address['line1'] ?? '',
                'line2' => $this->address['line2'] ?? null,
                'city' => $this->address['city'] ?? '',
                'state' => $this->address['state'] ?? '',
                'postal_code' => $this->address['postal_code'] ?? '',
                'country' => $this->address['country'] ?? '',
            ], fn ($value) => $value !== null && $value !== '');
        }

        return $payload;
    }
}
