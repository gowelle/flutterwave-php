<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Request DTO for creating a transfer recipient.
 *
 * Supports all Flutterwave recipient types:
 * - Bank: bank_ngn, bank_etb, bank_eur, bank_gbp, bank_ghs, bank_kes, bank_mwk, bank_rwf, bank_sll, bank_ugx, bank_usd, bank_xaf, bank_xof, bank_zar
 * - Mobile Money: mobile_money_etb, mobile_money_ghs, mobile_money_kes, mobile_money_rwf, mobile_money_tzs, mobile_money_ugx, mobile_money_xaf, mobile_money_xof, mobile_money_zmw
 *
 * @see https://developer.flutterwave.com/reference/transfers_recipients_create
 */
final readonly class CreateRecipientRequest
{
    /**
     * @param string $type Recipient type (e.g., "bank_ngn", "bank_usd", "mobile_money_tzs")
     * @param array<string, mixed>|null $bank Bank details (for bank_* types)
     * @param array<string, mixed>|null $mobileMoney Mobile money details (for mobile_money_* types)
     * @param array<string, mixed>|null $name Customer name (first, middle, last)
     * @param array<string, mixed>|null $phone Customer phone (country_code, number)
     * @param array<string, mixed>|null $address Customer address
     * @param string|null $email Customer email
     */
    public function __construct(
        public string $type,
        public ?array $bank = null,
        public ?array $mobileMoney = null,
        public ?array $name = null,
        public ?array $phone = null,
        public ?array $address = null,
        public ?string $email = null,
    ) {}

    // ==================== SIMPLE BANK RECIPIENTS ====================
    // These only require bank.account_number and bank.code

    /**
     * Create a Nigerian (NGN) bank recipient.
     */
    public static function bankNgn(string $accountNumber, string $bankCode): self
    {
        return new self(
            type: 'bank_ngn',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
            ],
        );
    }

    // ==================== BANK RECIPIENTS WITH NAME ====================
    // These require name + bank.account_number + bank.code

    /**
     * Create an Ethiopian (ETB) bank recipient.
     */
    public static function bankEtb(
        string $accountNumber,
        string $bankCode,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_etb',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    /**
     * Create a Kenyan (KES) bank recipient.
     */
    public static function bankKes(
        string $accountNumber,
        string $bankCode,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_kes',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    /**
     * Create a Malawian (MWK) bank recipient.
     */
    public static function bankMwk(
        string $accountNumber,
        string $bankCode,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_mwk',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    /**
     * Create a Rwandan (RWF) bank recipient.
     */
    public static function bankRwf(
        string $accountNumber,
        string $bankCode,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_rwf',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    /**
     * Create a Sierra Leonean (SLL) bank recipient.
     */
    public static function bankSll(
        string $accountNumber,
        string $bankCode,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_sll',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    /**
     * Create a Ugandan (UGX) bank recipient.
     */
    public static function bankUgx(
        string $accountNumber,
        string $bankCode,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_ugx',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    // ==================== BANK RECIPIENTS WITH NAME + BRANCH ====================
    // These require name + bank.account_number + bank.code + bank.branch

    /**
     * Create a Ghanaian (GHS) bank recipient.
     */
    public static function bankGhs(
        string $accountNumber,
        string $bankCode,
        string $branch,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_ghs',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
                'branch' => $branch,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    /**
     * Create a Central African (XAF) bank recipient.
     */
    public static function bankXaf(
        string $accountNumber,
        string $bankCode,
        string $branch,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_xaf',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
                'branch' => $branch,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    /**
     * Create a West African (XOF) bank recipient.
     */
    public static function bankXof(
        string $accountNumber,
        string $bankCode,
        string $branch,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'bank_xof',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
                'branch' => $branch,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
        );
    }

    // ==================== INTERNATIONAL BANK RECIPIENTS ====================
    // These require name + phone + email + address + bank details

    /**
     * Create a European (EUR) bank recipient.
     *
     * @param array{city: string, country: string, line1: string, postal_code: string, state?: string, line2?: string} $address
     * @param array{country_code: string, number: string} $phone
     */
    public static function bankEur(
        string $accountNumber,
        string $bankName,
        string $swiftCode,
        string $firstName,
        string $lastName,
        array $phone,
        string $email,
        array $address,
    ): self {
        return new self(
            type: 'bank_eur',
            bank: [
                'account_number' => $accountNumber,
                'name' => $bankName,
                'swift_code' => $swiftCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
            phone: $phone,
            email: $email,
            address: $address,
        );
    }

    /**
     * Create a UK (GBP) bank recipient.
     *
     * @param string $accountType "individual" or "corporate"
     * @param array{city: string, country: string, line1: string, postal_code: string, state?: string, line2?: string} $address
     * @param array{country_code: string, number: string} $phone
     */
    public static function bankGbp(
        string $accountNumber,
        string $accountType,
        string $bankName,
        string $sortCode,
        string $firstName,
        string $lastName,
        array $phone,
        string $email,
        array $address,
    ): self {
        return new self(
            type: 'bank_gbp',
            bank: [
                'account_number' => $accountNumber,
                'account_type' => $accountType,
                'name' => $bankName,
                'sort_code' => $sortCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
            phone: $phone,
            email: $email,
            address: $address,
        );
    }

    /**
     * Create a US (USD) bank recipient.
     *
     * @param string $accountType "checking" or "savings"
     * @param array{city: string, country: string, line1: string, postal_code: string, state?: string, line2?: string} $address
     * @param array{country_code: string, number: string} $phone
     */
    public static function bankUsd(
        string $accountNumber,
        string $bankCode,
        string $accountType,
        string $routingNumber,
        string $swiftCode,
        string $firstName,
        string $lastName,
        array $phone,
        string $email,
        array $address,
    ): self {
        return new self(
            type: 'bank_usd',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
                'account_type' => $accountType,
                'routing_number' => $routingNumber,
                'swift_code' => $swiftCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
            phone: $phone,
            email: $email,
            address: $address,
        );
    }

    /**
     * Create a South African (ZAR) bank recipient.
     *
     * @param array{city: string, country: string, line1: string, postal_code: string, state?: string, line2?: string} $address
     * @param array{country_code: string, number: string} $phone
     */
    public static function bankZar(
        string $accountNumber,
        string $bankCode,
        string $firstName,
        string $lastName,
        array $phone,
        string $email,
        array $address,
    ): self {
        return new self(
            type: 'bank_zar',
            bank: [
                'account_number' => $accountNumber,
                'code' => $bankCode,
            ],
            name: ['first' => $firstName, 'last' => $lastName],
            phone: $phone,
            email: $email,
            address: $address,
        );
    }

    // ==================== MOBILE MONEY RECIPIENTS ====================

    /**
     * Create a mobile money recipient.
     *
     * Supported currencies: ETB, GHS, KES, RWF, TZS, UGX, XAF, XOF, ZMW
     *
     * @param string $currency Currency code (e.g., "TZS", "GHS", "KES")
     */
    public static function mobileMoney(
        string $currency,
        string $network,
        string $phoneNumber,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: 'mobile_money_' . strtolower($currency),
            mobileMoney: [
                'network' => $network,
                'msisdn' => $phoneNumber,
            ],
            name: [
                'first' => $firstName,
                'last' => $lastName,
            ],
        );
    }

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return array_filter([
            'type' => $this->type,
            'bank' => $this->bank,
            'mobile_money' => $this->mobileMoney,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,
            'email' => $this->email,
        ], fn ($value) => $value !== null);
    }
}
