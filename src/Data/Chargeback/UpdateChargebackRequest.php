<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Chargeback;

/**
 * Request DTO for updating a chargeback via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/chargeback_put
 */
final readonly class UpdateChargebackRequest
{
    /**
     * @param  string  $status  Updated status of the chargeback (e.g. 'accepted', 'declined')
     * @param  string|null  $comment  Optional comment/note for the update
     * @param  array<string, mixed>|null  $meta  Optional metadata
     */
    public function __construct(
        public string $status,
        public ?string $comment = null,
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
            'status'  => $this->status,
            'comment' => $this->comment,
            'meta'    => $this->meta,
        ], fn ($value) => $value !== null);
    }
}
