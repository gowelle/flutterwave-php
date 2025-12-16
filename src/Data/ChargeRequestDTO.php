<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

/**
 * Data Transfer Object for charge requests
 *
 * Represents a complete charge request with all required and optional fields.
 * This DTO is used by the ChargeRequestBuilder to construct and validate charge data.
 */
final readonly class ChargeRequestDTO
{
    /**
     * @param  string  $reference  Unique reference for the charge
     * @param  float  $amount  Amount in smallest currency unit
     * @param  string  $currency  Currency code (e.g., TZS, NGN, USD)
     * @param  array{email: string, name: string, phone_number?: string}  $customer  Customer information
     * @param  array{type: string, card?: array, mobile_money?: array, bank_account?: array}  $paymentMethod  Payment method details
     * @param  string  $redirectUrl  URL to redirect after payment
     * @param  array<string, mixed>|null  $meta  Optional metadata
     * @param  array{title: string, description?: string, logo?: string}|null  $customizations  Optional customizations
     * @param  string|null  $paymentOptions  Optional payment options
     * @param  string|null  $idempotencyKey  Optional idempotency key
     * @param  string|null  $traceId  Optional trace ID
     */
    public function __construct(
        public string $reference,
        public float $amount,
        public string $currency,
        public array $customer,
        public array $paymentMethod,
        public string $redirectUrl,
        public ?array $meta = null,
        public ?array $customizations = null,
        public ?string $paymentOptions = null,
        public ?string $idempotencyKey = null,
        public ?string $traceId = null,
    ) {}

    /**
     * Convert DTO to API payload array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer' => $this->customer,
            'payment_method' => $this->paymentMethod,
            'redirect_url' => $this->redirectUrl,
        ];

        if ($this->meta !== null) {
            $data['meta'] = $this->meta;
        }

        if ($this->customizations !== null) {
            $data['customizations'] = $this->customizations;
        }

        if ($this->paymentOptions !== null) {
            $data['payment_options'] = $this->paymentOptions;
        }

        if ($this->idempotencyKey !== null) {
            $data['idempotency_key'] = $this->idempotencyKey;
        }

        if ($this->traceId !== null) {
            $data['trace_id'] = $this->traceId;
        }

        return $data;
    }

    /**
     * Validate the DTO has all required fields
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if (empty($this->reference)) {
            throw new \InvalidArgumentException('Reference is required');
        }

        if ($this->amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        if (empty($this->currency)) {
            throw new \InvalidArgumentException('Currency is required');
        }

        if (empty($this->customer['email']) || empty($this->customer['name'])) {
            throw new \InvalidArgumentException('Customer email and name are required');
        }

        if (empty($this->paymentMethod['type'])) {
            throw new \InvalidArgumentException('Payment method type is required');
        }

        if (empty($this->redirectUrl)) {
            throw new \InvalidArgumentException('Redirect URL is required');
        }
    }
}

