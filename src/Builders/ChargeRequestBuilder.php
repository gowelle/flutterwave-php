<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Builders;

use Gowelle\Flutterwave\Data\DirectChargeRequestDTO;
use Gowelle\Flutterwave\Support\EncryptionService;

final class ChargeRequestBuilder
{
    private string $reference;

    private float $amount = 0;

    private string $currency;

    private ?array $customer = null;

    private ?array $paymentMethod = null;

    private string $redirectUrl = '';

    private ?array $meta = null;

    private ?array $customizations = null;

    private ?string $paymentOptions = null;

    private ?string $idempotencyKey = null;

    private ?string $traceId = null;

    private ?string $scenarioKey = null;

    private ?int $userId = null;

    private ?int $paymentId = null;

    private ?EncryptionService $encryptionService = null;

    private function __construct(string $reference)
    {
        $this->reference = $reference;
        $this->currency = config('flutterwave.default_currency', 'TZS');
    }

    /**
     * Create a new charge request builder
     */
    public static function for(string $reference): self
    {
        return new self($reference);
    }

    /**
     * Set the amount and currency
     */
    public function amount(float $amount, ?string $currency = null): self
    {
        $this->amount = $amount;
        if ($currency !== null) {
            $this->currency = $currency;
        }

        return $this;
    }

    /**
     * Set customer information
     *
     * @param  string  $email  Customer email address (required)
     * @param  string  $firstName  Customer first name (required)
     * @param  string  $lastName  Customer last name (required)
     * @param  string|null  $phoneNumber  Customer phone number (required for mobile money)
     */
    public function customer(string $email, string $firstName, string $lastName, ?string $phoneNumber = null): self
    {
        $this->customer = [
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];

        if ($phoneNumber) {
            $this->customer['phone_number'] = $phoneNumber;
        }

        return $this;
    }

    /**
     * Set redirect URL
     */
    public function redirectUrl(string $url): self
    {
        $this->redirectUrl = $url;

        return $this;
    }

    /**
     * Set payment options
     */
    public function paymentOptions(string $options): self
    {
        $this->paymentOptions = $options;

        return $this;
    }

    /**
     * Set metadata
     */
    public function meta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * Set customizations
     */
    public function customizations(string $title, ?string $description = null, ?string $logo = null): self
    {
        $this->customizations = [
            'title' => $title,
        ];

        if ($description) {
            $this->customizations['description'] = $description;
        }

        if ($logo) {
            $this->customizations['logo'] = $logo;
        }

        return $this;
    }

    /**
     * Set idempotency key for request deduplication
     */
    public function idempotencyKey(string $key): self
    {
        $this->idempotencyKey = $key;

        return $this;
    }

    /**
     * Set trace ID for request tracking
     */
    public function traceId(string $id): self
    {
        $this->traceId = $id;

        return $this;
    }

    /**
     * Set scenario key for testing
     */
    public function scenarioKey(string $key): self
    {
        $this->scenarioKey = $key;

        return $this;
    }

    /**
     * Set user ID for charge session tracking
     */
    public function userId(int $id): self
    {
        $this->userId = $id;

        return $this;
    }

    /**
     * Set payment ID for charge session tracking
     */
    public function paymentId(int $id): self
    {
        $this->paymentId = $id;

        return $this;
    }

    /**
     * Set card payment method with automatic encryption
     *
     * @param  string  $cardNumber  Card number (13-19 digits)
     * @param  string  $expiryMonth  Card expiry month (MM format: 01-12)
     * @param  string  $expiryYear  Card expiry year (YY or YYYY format)
     * @param  string  $cvv  Card CVV (3-4 digits)
     * @param  array|null  $billingAddress  Optional billing address
     * @return $this
     *
     * @throws \Gowelle\Flutterwave\Exceptions\EncryptionException
     */
    public function card(
        string $cardNumber,
        string $expiryMonth,
        string $expiryYear,
        string $cvv,
        ?array $billingAddress = null,
    ): self {
        // Initialize encryption service if needed
        if ($this->encryptionService === null) {
            $encryptionKey = config('flutterwave.encryption_key');
            $this->encryptionService = new EncryptionService($encryptionKey ?? '');
        }

        // Encrypt card data
        $encryptedCard = $this->encryptionService->encryptCardData([
            'card_number' => $cardNumber,
            'expiry_month' => $expiryMonth,
            'expiry_year' => $expiryYear,
            'cvv' => $cvv,
        ]);

        // Build payment method with encrypted data
        $this->paymentMethod = [
            'type' => 'card',
            'card' => $encryptedCard,
        ];

        // Add billing address if provided
        if ($billingAddress) {
            $this->paymentMethod['card']['billing_address'] = $billingAddress;
        }

        return $this;
    }

    /**
     * Set mobile money payment method
     *
     * @param  string  $network  Mobile network (e.g., VODACOM, AIRTEL, M-PESA)
     * @param  string  $phoneNumber  Phone number for the payment
     * @return $this
     */
    public function mobileMoney(string $network, string $phoneNumber): self
    {
        $this->paymentMethod = [
            'type' => 'mobile_money',
            'mobile_money' => [
                'network' => $network,
                'phone_number' => $phoneNumber,
            ],
        ];

        return $this;
    }

    /**
     * Set bank account payment method
     *
     * @param  string  $accountNumber  Bank account number
     * @param  string  $bankCode  Bank code
     * @return $this
     */
    public function bankAccount(string $accountNumber, string $bankCode): self
    {
        $this->paymentMethod = [
            'type' => 'bank_account',
            'bank_account' => [
                'account_number' => $accountNumber,
                'bank_code' => $bankCode,
            ],
        ];

        return $this;
    }

    /**
     * Set encryption service (for testing or custom encryption)
     *
     * @internal For testing purposes
     */
    public function withEncryptionService(EncryptionService $encryptionService): self
    {
        $this->encryptionService = $encryptionService;

        return $this;
    }

    /**
     * Build and return the charge request DTO
     *
     * @throws \InvalidArgumentException
     */
    public function build(): DirectChargeRequestDTO
    {
        // Validate required fields
        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Amount is required and must be greater than 0');
        }

        if ($this->customer === null) {
            throw new \InvalidArgumentException('Customer information is required');
        }

        if ($this->paymentMethod === null) {
            throw new \InvalidArgumentException('Payment method is required');
        }

        if (empty($this->redirectUrl)) {
            throw new \InvalidArgumentException('Redirect URL is required');
        }

        $dto = new DirectChargeRequestDTO(
            reference: $this->reference,
            amount: $this->amount,
            currency: $this->currency,
            customer: $this->customer,
            paymentMethod: $this->paymentMethod,
            redirectUrl: $this->redirectUrl,
            meta: $this->meta,
            customizations: $this->customizations,
            paymentOptions: $this->paymentOptions,
            idempotencyKey: $this->idempotencyKey,
            traceId: $this->traceId,
            scenarioKey: $this->scenarioKey,
            userId: $this->userId,
            paymentId: $this->paymentId,
        );

        // Validate the DTO
        $dto->validate();

        return $dto;
    }

    /**
     * Build and return the request as an array
     *
     * @throws \InvalidArgumentException
     */
    public function buildArray(): array
    {
        return $this->build()->toArray();
    }
}
