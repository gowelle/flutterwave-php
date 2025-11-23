<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

/**
 * Bank Account Resolve Data Transfer Object
 *
 * @property-read string $bankCode Bank code
 * @property-read string $accountNumber Bank account number
 * @property-read string $accountName Bank account name
 */
final class BankAccountResolveData
{
    public function __construct(
        public readonly string $bankCode,
        public readonly string $accountNumber,
        public readonly string $accountName,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            bankCode: $data['bank_code'] ?? '',
            accountNumber: $data['account_number'] ?? '',
            accountName: $data['account_name'] ?? '',
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'bank_code' => $this->bankCode,
            'account_number' => $this->accountNumber,
            'account_name' => $this->accountName,
        ];
    }
}
