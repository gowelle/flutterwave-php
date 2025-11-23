<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\PaymentMethods;

use Gowelle\Flutterwave\Data\PaymentMethodData;

final readonly class BankAccountPaymentMethod extends PaymentMethodData
{
    public function __construct(
        string $id,
        string $customerId,
        array $data,
        public ?string $bankName,
        public ?string $accountNumber,
        public ?string $accountName,
        public ?string $bankCode,
        public ?string $country,
        ?string $createdAt = null,
    ) {
        parent::__construct(
            id: $id,
            type: 'bank_account',
            customerId: $customerId,
            data: $data,
            createdAt: $createdAt,
        );
    }

    /**
     * Create from API response
     */
    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            customerId: $data['customer_id'],
            data: $data['data'] ?? [],
            bankName: $data['data']['bank_account']['bank_name'] ?? null,
            accountNumber: $data['data']['bank_account']['account_number'] ?? null,
            accountName: $data['data']['bank_account']['account_name'] ?? null,
            bankCode: $data['data']['bank_account']['bank_code'] ?? null,
            country: $data['data']['bank_account']['country'] ?? null,
            createdAt: $data['created_datetime'] ?? $data['created_at'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'customer_id' => $this->customerId,
            'data' => [
                'bank_account' => [
                    'bank_name' => $this->bankName,
                    'account_number' => $this->accountNumber,
                    'account_name' => $this->accountName,
                    'bank_code' => $this->bankCode,
                    'country' => $this->country,
                ],
            ],
            'created_at' => $this->createdAt,
        ];
    }
}
