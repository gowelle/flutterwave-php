<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Request DTO for retrying or duplicating a transfer.
 *
 * @see https://developer.flutterwave.com/reference/transfer_post_retry
 */
final readonly class RetryTransferRequest
{
    /**
     * @param  string  $transferId   ID of the initial transfer to retry or duplicate
     * @param  string  $action       Action to perform: 'retry' or 'duplicate'
     * @param  string  $reference    A unique identifier for this retry/duplicate transaction
     * @param  string|null  $callbackUrl  Optional webhook URL for transfer status updates
     */
    public function __construct(
        public string $transferId,
        public string $action,
        public string $reference,
        public ?string $callbackUrl = null,
    ) {}

    /**
     * Create a retry request
     */
    public static function retry(string $transferId, string $reference, ?string $callbackUrl = null): self
    {
        return new self(
            transferId: $transferId,
            action: 'retry',
            reference: $reference,
            callbackUrl: $callbackUrl,
        );
    }

    /**
     * Create a duplicate request
     */
    public static function duplicate(string $transferId, string $reference, ?string $callbackUrl = null): self
    {
        return new self(
            transferId: $transferId,
            action: 'duplicate',
            reference: $reference,
            callbackUrl: $callbackUrl,
        );
    }

    /**
     * Convert to API payload
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return array_filter([
            'action'       => $this->action,
            'reference'    => $this->reference,
            'callback_url' => $this->callbackUrl,
        ], fn ($value) => $value !== null);
    }
}
