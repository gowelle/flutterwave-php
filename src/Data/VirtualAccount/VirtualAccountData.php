<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\VirtualAccount;

use Gowelle\Flutterwave\Enums\VirtualAccountCurrency;
use Gowelle\Flutterwave\Enums\VirtualAccountStatus;
use Gowelle\Flutterwave\Enums\VirtualAccountType;

/**
 * Virtual Account Data Transfer Object
 *
 * Represents a virtual account response from the Flutterwave API.
 */
final readonly class VirtualAccountData
{
    /**
     * @param  string  $id  Virtual account ID
     * @param  float  $amount  Amount to be collected
     * @param  string  $accountNumber  Virtual bank account number
     * @param  string  $reference  Custom transaction reference
     * @param  string  $accountBankName  Name of the bank (e.g., WEMA BANK)
     * @param  VirtualAccountType  $accountType  Type of account (static/dynamic)
     * @param  VirtualAccountStatus  $status  Account status (active/inactive)
     * @param  string  $accountExpirationDatetime  ISO 8601 expiration datetime
     * @param  ?string  $note  Custom note for the account
     * @param  string  $customerId  Associated customer ID
     * @param  VirtualAccountCurrency  $currency  ISO 4217 currency code
     * @param  ?string  $customerReference  Customer's reference identifier
     * @param  ?array  $meta  Custom metadata
     * @param  ?string  $createdDatetime  ISO 8601 creation datetime
     */
    public function __construct(
        public string $id,
        public float $amount,
        public string $accountNumber,
        public string $reference,
        public string $accountBankName,
        public VirtualAccountType $accountType,
        public VirtualAccountStatus $status,
        public string $accountExpirationDatetime,
        public ?string $note = null,
        public string $customerId = '',
        public VirtualAccountCurrency $currency = VirtualAccountCurrency::NGN,
        public ?string $customerReference = null,
        public ?array $meta = null,
        public ?string $createdDatetime = null,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            amount: (float) ($data['amount'] ?? 0.0),
            accountNumber: (string) ($data['account_number'] ?? ''),
            reference: (string) ($data['reference'] ?? ''),
            accountBankName: (string) ($data['account_bank_name'] ?? ''),
            accountType: VirtualAccountType::fromApiResponse($data['account_type'] ?? 'static'),
            status: VirtualAccountStatus::fromApiResponse($data['status'] ?? 'active'),
            accountExpirationDatetime: (string) ($data['account_expiration_datetime'] ?? ''),
            note: $data['note'] ?? null,
            customerId: (string) ($data['customer_id'] ?? ''),
            currency: VirtualAccountCurrency::fromApiResponse($data['currency'] ?? 'NGN'),
            customerReference: $data['customer_reference'] ?? null,
            meta: $data['meta'] ?? null,
            createdDatetime: $data['created_datetime'] ?? null,
        );
    }

    /**
     * Create collection from API response
     *
     * @return VirtualAccountData[]
     */
    public static function collection(array $items): array
    {
        return array_map(fn (array $item) => self::fromApi($item), $items);
    }

    /**
     * Convert to array for general serialization
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'account_number' => $this->accountNumber,
            'reference' => $this->reference,
            'account_bank_name' => $this->accountBankName,
            'account_type' => $this->accountType->value,
            'status' => $this->status->value,
            'account_expiration_datetime' => $this->accountExpirationDatetime,
            'note' => $this->note,
            'customer_id' => $this->customerId,
            'currency' => $this->currency->value,
            'customer_reference' => $this->customerReference,
            'meta' => $this->meta,
            'created_datetime' => $this->createdDatetime,
        ];
    }

    /**
     * Check if account is active
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Check if account is static
     */
    public function isStatic(): bool
    {
        return $this->accountType->isStatic();
    }

    /**
     * Check if account is expired
     */
    public function isExpired(): bool
    {
        if (empty($this->accountExpirationDatetime)) {
            return false;
        }

        try {
            $expirationDate = new \DateTime($this->accountExpirationDatetime);

            return new \DateTime > $expirationDate;
        } catch (\Exception) {
            return false;
        }
    }
}
