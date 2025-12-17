<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\DirectCharge;

/**
 * Request DTO for creating direct charges via the Flutterwave orchestrator API.
 *
 * This DTO provides type-safe request construction for the /orchestration/direct-charges endpoint,
 * which combines customer, payment method, and charge creation in a single request.
 *
 * @see https://developer.flutterwave.com/reference/orchestration_direct_charge_post
 */
final readonly class CreateDirectChargeRequest
{
    /**
     * @param  float  $amount  Charge amount (must be >= 0.01)
     * @param  string  $currency  Currency code (e.g., NGN, USD, GHS)
     * @param  string  $reference  Unique reference (6-42 characters)
     * @param  array<string, mixed>  $customer  Customer details (email, name, phone_number)
     * @param  array<string, mixed>  $paymentMethod  Payment method details (type, card/mobile_money/etc.)
     * @param  string|null  $redirectUrl  Callback URL for redirect after charge (optional)
     * @param  array<string, mixed>|null  $meta  Additional metadata (optional)
     */
    public function __construct(
        public float $amount,
        public string $currency,
        public string $reference,
        public array $customer,
        public array $paymentMethod,
        public ?string $redirectUrl = null,
        public ?array $meta = null,
    ) {}

    /**
     * Create a new instance with static factory method.
     *
     * @param  float  $amount  Charge amount
     * @param  string  $currency  Currency code
     * @param  string  $reference  Unique reference
     * @param  array<string, mixed>  $customer  Customer details
     * @param  array<string, mixed>  $paymentMethod  Payment method details
     * @param  string|null  $redirectUrl  Callback URL (optional)
     * @param  array<string, mixed>|null  $meta  Metadata (optional)
     */
    public static function make(
        float $amount,
        string $currency,
        string $reference,
        array $customer,
        array $paymentMethod,
        ?string $redirectUrl = null,
        ?array $meta = null,
    ): self {
        return new self(
            amount: $amount,
            currency: $currency,
            reference: $reference,
            customer: $customer,
            paymentMethod: $paymentMethod,
            redirectUrl: $redirectUrl,
            meta: $meta,
        );
    }

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $payload = [
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'customer' => $this->customer,
            'payment_method' => $this->paymentMethod,
        ];

        if ($this->redirectUrl !== null) {
            $payload['redirect_url'] = $this->redirectUrl;
        }

        if ($this->meta !== null && $this->meta !== []) {
            $payload['meta'] = $this->meta;
        }

        return $payload;
    }
}
