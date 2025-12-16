<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

use Gowelle\Flutterwave\Enums\TransferType;

/**
 * Response DTO for transfer recipient data.
 */
final readonly class RecipientData
{
    /**
     * @param array<string, mixed>|null $bank
     * @param array<string, mixed>|null $mobileMoney
     * @param array<string, mixed>|null $name
     */
    public function __construct(
        public string $id,
        public TransferType $type,
        public string $currency,
        public ?array $bank = null,
        public ?array $mobileMoney = null,
        public ?array $name = null,
        public ?string $email = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * Create from Flutterwave API response
     *
     * @param array<string, mixed> $data
     */
    public static function fromApi(array $data): self
    {
        $type = TransferType::tryFrom($data['type'] ?? 'bank') ?? TransferType::BANK;

        return new self(
            id: (string) $data['id'],
            type: $type,
            currency: $data['currency'] ?? '',
            bank: isset($data['bank']) && \is_array($data['bank']) ? $data['bank'] : null,
            mobileMoney: isset($data['mobile_money']) && \is_array($data['mobile_money']) ? $data['mobile_money'] : null,
            name: isset($data['name']) && \is_array($data['name']) ? $data['name'] : null,
            email: $data['email'] ?? null,
            createdAt: $data['created_datetime'] ?? $data['created_at'] ?? null,
        );
    }

    /**
     * Get account number (for bank recipients)
     */
    public function getAccountNumber(): ?string
    {
        return $this->bank['account_number'] ?? null;
    }

    /**
     * Get bank code (for bank recipients)
     */
    public function getBankCode(): ?string
    {
        return $this->bank['code'] ?? null;
    }

    /**
     * Get phone number (for mobile money recipients)
     */
    public function getPhoneNumber(): ?string
    {
        return $this->mobileMoney['msisdn'] ?? null;
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'currency' => $this->currency,
            'bank' => $this->bank,
            'mobile_money' => $this->mobileMoney,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt,
        ];
    }
}
