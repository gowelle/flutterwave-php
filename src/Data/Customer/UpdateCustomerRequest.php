<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Customer;

/**
 * Request DTO for updating customers via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/customers_update
 */
final readonly class UpdateCustomerRequest
{
    /**
     * @param  string  $email  Customer email address
     * @param  string  $firstName  Customer first name
     * @param  string  $lastName  Customer last name
     * @param  string  $phoneNumber  Customer phone number
     * @param  string|null  $middleName  Customer middle name (optional)
     */
    public function __construct(
        public string $email,
        public string $firstName,
        public string $lastName,
        public string $phoneNumber,
        public ?string $middleName = null,
    ) {}

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $name = array_filter([
            'first' => $this->firstName,
            'middle' => $this->middleName,
            'last' => $this->lastName,
        ], fn ($value) => $value !== null && $value !== '');

        return [
            'email' => $this->email,
            'name' => $name,
            'phone_number' => $this->phoneNumber,
        ];
    }
}
