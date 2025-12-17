<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Order;

/**
 * Request DTO for creating orders via the Orchestrator endpoint.
 *
 * This DTO is for the orchestrator order creation that accepts
 * full customer and payment method objects instead of IDs.
 *
 * @see https://developer.flutterwave.com/reference/orchestration_direct_order_post
 */
final readonly class CreateOrchestratorOrderRequest
{
    /**
     * @param  float  $amount  Payment amount in decimals (>= 0.01)
     * @param  string  $currency  ISO 4217 currency code
     * @param  string  $reference  Unique transaction identifier (6-42 chars)
     * @param  array<string, mixed>  $customer  Customer details object
     * @param  array<string, mixed>  $paymentMethod  Payment method details object
     * @param  array<string, mixed>|null  $meta  Optional metadata object
     * @param  string|null  $redirectUrl  URL to redirect to after payment
     * @param  array<string, mixed>|null  $authorization  Authorization details (e.g., for 3DS)
     */
    public function __construct(
        public float $amount,
        public string $currency,
        public string $reference,
        public array $customer,
        public array $paymentMethod,
        public ?array $meta = null,
        public ?string $redirectUrl = null,
        public ?array $authorization = null,
    ) {}

    /**
     * Create orchestrator order request with fluent builder pattern.
     *
     * @param  array<string, mixed>  $customer  Customer details (name, email, phone, etc.)
     * @param  array<string, mixed>  $paymentMethod  Payment method details
     */
    public static function make(
        float $amount,
        string $currency,
        string $reference,
        array $customer,
        array $paymentMethod,
        ?array $meta = null,
        ?string $redirectUrl = null,
        ?array $authorization = null,
    ): self {
        return new self(
            amount: $amount,
            currency: $currency,
            reference: $reference,
            customer: $customer,
            paymentMethod: $paymentMethod,
            meta: $meta,
            redirectUrl: $redirectUrl,
            authorization: $authorization,
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

        if ($this->meta !== null) {
            $payload['meta'] = $this->meta;
        }

        if ($this->redirectUrl !== null) {
            $payload['redirect_url'] = $this->redirectUrl;
        }

        if ($this->authorization !== null) {
            $payload['authorization'] = $this->authorization;
        }

        return $payload;
    }
}
