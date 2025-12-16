<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Response DTO for transfer sender data.
 *
 * @see https://developer.flutterwave.com/reference/transfers_senders_get
 */
final readonly class SenderData
{
    /**
     * @param string $id Sender ID
     * @param string $type Sender type (generic_sender, bank_gbp, bank_eur)
     * @param array<string, mixed> $raw Raw API response data for accessing all fields
     */
    public function __construct(
        public string $id,
        public string $type,
        public array $raw = [],
    ) {}

    /**
     * Create from Flutterwave API response.
     *
     * @param array<string, mixed> $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            type: $data['type'] ?? 'generic_sender',
            raw: $data,
        );
    }

    /**
     * Get name object.
     *
     * @return array<string, mixed>|null
     */
    public function getName(): ?array
    {
        return isset($this->raw['name']) && \is_array($this->raw['name']) ? $this->raw['name'] : null;
    }

    /**
     * Get full name.
     */
    public function getFullName(): string
    {
        $name = $this->getName();
        if ($name === null) {
            return '';
        }

        $parts = array_filter([
            $name['first'] ?? null,
            $name['middle'] ?? null,
            $name['last'] ?? null,
        ]);

        return implode(' ', $parts);
    }

    /**
     * Get first name.
     */
    public function getFirstName(): ?string
    {
        return $this->raw['name']['first'] ?? null;
    }

    /**
     * Get last name.
     */
    public function getLastName(): ?string
    {
        return $this->raw['name']['last'] ?? null;
    }

    /**
     * Get email.
     */
    public function getEmail(): ?string
    {
        return $this->raw['email'] ?? null;
    }

    /**
     * Get phone object.
     *
     * @return array<string, mixed>|null
     */
    public function getPhone(): ?array
    {
        return isset($this->raw['phone']) && \is_array($this->raw['phone']) ? $this->raw['phone'] : null;
    }

    /**
     * Get phone number (formatted with country code).
     */
    public function getPhoneNumber(): ?string
    {
        $phone = $this->getPhone();
        if ($phone === null) {
            return $this->raw['phone_number'] ?? null;
        }

        $countryCode = $phone['country_code'] ?? '';
        $number = $phone['number'] ?? '';

        return $countryCode ? "+{$countryCode}{$number}" : $number;
    }

    /**
     * Get address object.
     *
     * @return array<string, mixed>|null
     */
    public function getAddress(): ?array
    {
        return isset($this->raw['address']) && \is_array($this->raw['address']) ? $this->raw['address'] : null;
    }

    /**
     * Get country.
     */
    public function getCountry(): ?string
    {
        return $this->raw['address']['country'] ?? $this->raw['country'] ?? null;
    }

    /**
     * Get national identification.
     *
     * @return array<string, mixed>|null
     */
    public function getNationalIdentification(): ?array
    {
        return isset($this->raw['national_identification']) && \is_array($this->raw['national_identification'])
            ? $this->raw['national_identification']
            : null;
    }

    /**
     * Get date of birth.
     */
    public function getDateOfBirth(): ?string
    {
        return $this->raw['date_of_birth'] ?? null;
    }

    /**
     * Get created datetime.
     */
    public function getCreatedAt(): ?string
    {
        return $this->raw['created_datetime'] ?? $this->raw['created_at'] ?? null;
    }

    /**
     * Convert to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->raw;
    }
}
