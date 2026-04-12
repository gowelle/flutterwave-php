<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Chargeback;

/**
 * Request DTO for creating a chargeback via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/chargebacks_post
 */
final readonly class CreateChargebackRequest
{
    /**
     * @param  string  $chargeId  ID of the charge to raise a chargeback against
     * @param  string  $reason    Reason for the chargeback
     * @param  array<string, mixed>|null  $meta  Optional metadata
     */
    public function __construct(
        public string $chargeId,
        public string $reason,
        public ?array $meta = null,
    ) {}

    /**
     * Convert to API payload
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return array_filter([
            'charge_id' => $this->chargeId,
            'reason'    => $this->reason,
            'meta'      => $this->meta,
        ], fn ($value) => $value !== null);
    }
}
