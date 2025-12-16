<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Response DTO for transfer recipient data.
 *
 * @see https://developer.flutterwave.com/reference/transfers_recipients_get
 */
final readonly class RecipientData
{
    /**
     * @param string $id Recipient ID
     * @param string $type Recipient type (e.g., "bank", "mobile_money")
     * @param string $currency Currency code (e.g., "NGN", "USD")
     * @param array<string, mixed> $raw Raw API response data for accessing all fields
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $currency,
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
            type: $data['type'] ?? '',
            currency: $data['currency'] ?? '',
            raw: $data,
        );
    }

    /**
     * Get bank details (for bank recipients).
     *
     * @return array<string, mixed>|null
     */
    public function getBank(): ?array
    {
        return isset($this->raw['bank']) && \is_array($this->raw['bank']) ? $this->raw['bank'] : null;
    }

    /**
     * Get mobile money details (for mobile money recipients).
     *
     * @return array<string, mixed>|null
     */
    public function getMobileMoney(): ?array
    {
        return isset($this->raw['mobile_money']) && \is_array($this->raw['mobile_money']) ? $this->raw['mobile_money'] : null;
    }

    /**
     * Get recipient name.
     *
     * @return array<string, mixed>|null
     */
    public function getName(): ?array
    {
        return isset($this->raw['name']) && \is_array($this->raw['name']) ? $this->raw['name'] : null;
    }

    /**
     * Get recipient email.
     */
    public function getEmail(): ?string
    {
        return $this->raw['email'] ?? null;
    }

    /**
     * Get account number (for bank recipients).
     */
    public function getAccountNumber(): ?string
    {
        return $this->raw['bank']['account_number'] ?? null;
    }

    /**
     * Get bank code (for bank recipients).
     */
    public function getBankCode(): ?string
    {
        return $this->raw['bank']['code'] ?? null;
    }

    /**
     * Get phone number (for mobile money recipients).
     */
    public function getPhoneNumber(): ?string
    {
        return $this->raw['mobile_money']['msisdn'] ?? null;
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
