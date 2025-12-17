<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Order;

/**
 * Request DTO for updating orders via the Flutterwave API.
 *
 * The update endpoint only supports setting metadata and performing
 * actions (void or capture) on the order.
 *
 * @see https://developer.flutterwave.com/reference/orders_put
 */
final readonly class UpdateOrderRequest
{
    /**
     * @param  array<string, mixed>|null  $meta  Optional metadata to update
     * @param  OrderAction|null  $action  Action to perform (void or capture)
     */
    public function __construct(
        public ?array $meta = null,
        public ?OrderAction $action = null,
    ) {}

    /**
     * Create a void action request.
     */
    public static function void(?array $meta = null): self
    {
        return new self(meta: $meta, action: OrderAction::Void);
    }

    /**
     * Create a capture action request.
     */
    public static function capture(?array $meta = null): self
    {
        return new self(meta: $meta, action: OrderAction::Capture);
    }

    /**
     * Create a metadata-only update request.
     *
     * @param  array<string, mixed>  $meta
     */
    public static function withMeta(array $meta): self
    {
        return new self(meta: $meta);
    }

    /**
     * Convert to API payload.
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $payload = [];

        if ($this->meta !== null) {
            $payload['meta'] = $this->meta;
        }

        if ($this->action !== null) {
            $payload['action'] = $this->action->value;
        }

        return $payload;
    }
}
