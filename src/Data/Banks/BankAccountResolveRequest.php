<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Banks;

/**
 * Request DTO for resolving bank account details via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/banks_account_resolve
 */
final readonly class BankAccountResolveRequest
{
    /**
     * @param  string  $bankCode  Bank code
     * @param  string  $accountNumber  Account number to resolve
     * @param  string  $currency  ISO 4217 currency code (default: NGN)
     */
    public function __construct(
        public string $bankCode,
        public string $accountNumber,
        public string $currency = 'NGN',
    ) {}

    /**
     * Convert to API payload.
     *
     * @return array<string, string>
     */
    public function toApiPayload(): array
    {
        return [
            'bank_code' => $this->bankCode,
            'account_number' => $this->accountNumber,
            'currency' => mb_strtoupper($this->currency),
        ];
    }
}
