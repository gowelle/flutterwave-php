<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Order;

/**
 * Request DTO for updating orders via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/orders_update
 */
final readonly class UpdateOrderRequest
{
    /**
     * @param  string|null  $orderReference  Updated order reference
     * @param  float|null  $amount  Updated amount
     * @param  string|null  $status  Updated status
     */
    public function __construct(
        public ?string $orderReference = null,
        public ?float $amount = null,
        public ?string $status = null,
    ) {}

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return array_filter([
            'order_reference' => $this->orderReference,
            'amount' => $this->amount,
            'status' => $this->status,
        ], fn ($value) => $value !== null);
    }
}
