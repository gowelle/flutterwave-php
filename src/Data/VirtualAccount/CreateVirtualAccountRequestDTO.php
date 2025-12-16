<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\VirtualAccount;

use Gowelle\Flutterwave\Enums\VirtualAccountCurrency;
use Gowelle\Flutterwave\Enums\VirtualAccountType;

/**
 * Create Virtual Account Request DTO
 *
 * Represents the payload for creating a virtual account.
 */
final class CreateVirtualAccountRequestDTO
{
    /**
     * @param  string  $reference  Unique transaction reference (6-42 chars)
     * @param  string  $customerId  Associated customer ID
     * @param  float  $amount  Amount to be collected (0 for static accounts)
     * @param  VirtualAccountCurrency  $currency  ISO 4217 currency code
     * @param  VirtualAccountType  $accountType  Type of account (static/dynamic)
     * @param  ?int  $expiry  Account expiry in seconds (60-31536000)
     * @param  ?array  $meta  Custom metadata
     * @param  ?string  $narration  Name shown when account is resolved
     * @param  ?string  $bvn  Customer's Bank Verification Number
     * @param  ?string  $nin  Customer's National Identity Number
     * @param  ?string  $customerAccountNumber  Bank account for transfers (required for EGP/KES)
     */
    public function __construct(
        public string $reference,
        public string $customerId,
        public float $amount,
        public VirtualAccountCurrency $currency,
        public VirtualAccountType $accountType,
        public ?int $expiry = null,
        public ?array $meta = null,
        public ?string $narration = null,
        public ?string $bvn = null,
        public ?string $nin = null,
        public ?string $customerAccountNumber = null,
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            reference: $data['reference'],
            customerId: $data['customer_id'],
            amount: (float) $data['amount'],
            currency: $data['currency'] instanceof VirtualAccountCurrency
                ? $data['currency']
                : VirtualAccountCurrency::fromApiResponse($data['currency']),
            accountType: $data['account_type'] instanceof VirtualAccountType
                ? $data['account_type']
                : VirtualAccountType::fromApiResponse($data['account_type']),
            expiry: $data['expiry'] ?? null,
            meta: $data['meta'] ?? null,
            narration: $data['narration'] ?? null,
            bvn: $data['bvn'] ?? null,
            nin: $data['nin'] ?? null,
            customerAccountNumber: $data['customer_account_number'] ?? null,
        );
    }

    /**
     * Convert to API request format
     */
    public function toArray(): array
    {
        $data = [
            'reference' => $this->reference,
            'customer_id' => $this->customerId,
            'amount' => $this->amount,
            'currency' => $this->currency->value,
            'account_type' => $this->accountType->value,
        ];

        if ($this->expiry !== null) {
            $data['expiry'] = $this->expiry;
        }

        if ($this->meta !== null) {
            $data['meta'] = $this->meta;
        }

        if ($this->narration !== null) {
            $data['narration'] = $this->narration;
        }

        if ($this->bvn !== null) {
            $data['bvn'] = $this->bvn;
        }

        if ($this->nin !== null) {
            $data['nin'] = $this->nin;
        }

        if ($this->customerAccountNumber !== null) {
            $data['customer_account_number'] = $this->customerAccountNumber;
        }

        return $data;
    }
}
