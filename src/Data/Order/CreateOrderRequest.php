<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Order;

/**
 * Request DTO for creating orders via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/orders_create
 */
final readonly class CreateOrderRequest
{
    /**
     * @param  string  $orderReference  Unique order reference
     * @param  float  $amount  Order total amount
     * @param  string  $currency  ISO 4217 currency code
     * @param  array{name: string, email: string, phone_number?: string}  $customer  Customer information
     * @param  array<int, array{name: string, quantity: int, amount: float}>  $items  Order items
     */
    public function __construct(
        public string $orderReference,
        public float $amount,
        public string $currency,
        public array $customer,
        public array $items,
    ) {}

    /**
     * Create order request with fluent builder pattern.
     *
     * @param  array<int, array{name: string, quantity: int, amount: float}>  $items
     */
    public static function make(
        string $orderReference,
        float $amount,
        string $currency,
        string $customerName,
        string $customerEmail,
        array $items,
        ?string $customerPhone = null,
    ): self {
        $customer = [
            'name' => $customerName,
            'email' => $customerEmail,
        ];

        if ($customerPhone !== null) {
            $customer['phone_number'] = $customerPhone;
        }

        return new self(
            orderReference: $orderReference,
            amount: $amount,
            currency: $currency,
            customer: $customer,
            items: $items,
        );
    }

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return [
            'order_reference' => $this->orderReference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer' => $this->customer,
            'items' => $this->items,
        ];
    }
}
