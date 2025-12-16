<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Refund;

use Gowelle\Flutterwave\Enums\RefundReason;

/**
 * Request DTO for creating refunds via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/refunds_post
 */
final readonly class CreateRefundRequest
{
    /**
     * @param float $amount The amount to be refunded (must be >= 0.01)
     * @param string $chargeId ID of the charge to refund
     * @param RefundReason $reason Reason for the refund
     * @param array<string, mixed>|null $meta Optional metadata
     */
    public function __construct(
        public float $amount,
        public string $chargeId,
        public RefundReason $reason,
        public ?array $meta = null,
    ) {}

    /**
     * Convert to API payload
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $payload = array_filter([
            'amount' => $this->amount,
            'charge_id' => $this->chargeId,
            'reason' => $this->reason->value,
            'meta' => $this->meta,
        ], fn ($value) => $value !== null);

        return $payload;
    }
}

