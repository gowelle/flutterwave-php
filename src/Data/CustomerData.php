<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

final readonly class CustomerData
{
    /**
     * @param  array{first: string, middle?: string, last: string}|null  $name
     * @param  array{line1: string, line2?: string, city: string, state: string, postal_code: string, country: string}|null  $address
     */
    public function __construct(
        public string $id,
        public string $email,
        public ?array $name = null,
        public ?string $phoneNumber = null,
        public ?array $address = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApi(array $data): self
    {
        $name = null;
        if (isset($data['name'])) {
            if (\is_array($data['name'])) {
                $name = [
                    'first' => $data['name']['first'] ?? '',
                    'middle' => $data['name']['middle'] ?? null,
                    'last' => $data['name']['last'] ?? '',
                ];
            } else {
                // Handle legacy string format - try to parse or use as-is
                $name = ['first' => (string) $data['name'], 'middle' => null, 'last' => ''];
            }
        }

        $phoneNumber = $data['phone_number'] ?? $data['phonenumber'] ?? null;
        if (isset($data['phone']) && \is_array($data['phone'])) {
            $countryCode = $data['phone']['country_code'] ?? '';
            $number = $data['phone']['number'] ?? '';
            $phoneNumber = $countryCode ? "{$countryCode}{$number}" : $number;
        }

        $address = null;
        if (isset($data['address']) && \is_array($data['address'])) {
            $address = [
                'line1' => $data['address']['line1'] ?? '',
                'line2' => $data['address']['line2'] ?? null,
                'city' => $data['address']['city'] ?? '',
                'state' => $data['address']['state'] ?? '',
                'postal_code' => $data['address']['postal_code'] ?? '',
                'country' => $data['address']['country'] ?? '',
            ];
        }

        return new self(
            id: (string) $data['id'],
            email: $data['email'],
            name: $name,
            phoneNumber: $phoneNumber,
            address: $address,
            createdAt: $data['created_at'] ?? $data['created_datetime'] ?? null,
        );
    }

    /**
     * Create collection from API response
     *
     * @return CustomerData[]
     */
    public static function collection(array $items): array
    {
        return array_map(fn (array $item) => self::fromApi($item), $items);
    }

    /**
     * Get full name as a string
     */
    public function getFullName(): ?string
    {
        if ($this->name === null) {
            return null;
        }

        $parts = array_filter([
            $this->name['first'] ?? '',
            $this->name['middle'] ?? null,
            $this->name['last'] ?? '',
        ]);

        return implode(' ', $parts) ?: null;
    }

    /**
     * Convert to array for general serialization
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->name,
            'phone_number' => $this->phoneNumber,
            'address' => $this->address,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * Convert to API request format for creating/updating customers
     */
    public function toRequestArray(): array
    {
        $data = [
            'email' => $this->email,
        ];

        if ($this->name !== null) {
            $data['name'] = array_filter([
                'first' => $this->name['first'] ?? '',
                'middle' => $this->name['middle'] ?? null,
                'last' => $this->name['last'] ?? '',
            ], fn ($value) => $value !== null && $value !== '');
        }

        if ($this->phoneNumber !== null) {
            // Build the structured phone object expected by the Flutterwave API:
            //   { "phone": { "country_code": "+234", "number": "8012345678" } }
            // We also emit the legacy phone_number key for backward compatibility.
            $phoneData = $this->parsePhoneNumber($this->phoneNumber);
            $data['phone']        = $phoneData;
            $data['phone_number'] = $this->phoneNumber; // @deprecated: kept for backward compat
        }

        if ($this->address !== null) {
            $data['address'] = array_filter([
                'line1' => $this->address['line1'] ?? '',
                'line2' => $this->address['line2'] ?? null,
                'city' => $this->address['city'] ?? '',
                'state' => $this->address['state'] ?? '',
                'postal_code' => $this->address['postal_code'] ?? '',
                'country' => $this->address['country'] ?? '',
            ], fn ($value) => $value !== null && $value !== '');
        }

        return $data;
    }

    /**
     * Parse a phone number string into the structured format expected by the Flutterwave API.
     *
     * Handles formats like "+2348012345678", "2348012345678", or "08012345678".
     *
     * @return array{country_code?: string, number: string}
     */
    protected function parsePhoneNumber(string $phoneNumber): array
    {
        // Match an optional leading + and country code (1–4 digits), then the subscriber number
        if (preg_match('/^(\+?\d{1,4})(\d{6,12})$/', preg_replace('/[\s\-().]+/', '', $phoneNumber), $matches)) {
            return [
                'country_code' => '+'.\ltrim($matches[1], '+'),
                'number'       => $matches[2],
            ];
        }

        // Could not split — return the full number without a country_code prefix
        return ['number' => preg_replace('/[\s\-().]+/', '', $phoneNumber)];
    }
}
