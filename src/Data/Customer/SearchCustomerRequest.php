<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Customer;

/**
 * Request DTO for searching customers via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/customers_search
 */
final readonly class SearchCustomerRequest
{
    /**
     * @param  string|null  $email  Customer email to search for (optional)
     */
    public function __construct(
        public ?string $email = null,
    ) {}

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return array_filter([
            'email' => $this->email,
        ], fn ($value) => $value !== null);
    }
}
