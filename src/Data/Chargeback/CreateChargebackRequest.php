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
     * @param  float  $amount  The disputed payment amount
     * @param  string  $type  local or international
     * @param  int  $expiry  Duration in hours used to calculate due_datetime
     * @param  string|null  $stage  Optional dispute stage
     * @param  string|null  $status  Optional initial status
     * @param  string|null  $uploadedProof  Optional URL to supporting proof
     * @param  string|null  $comment  Optional comment
     * @param  string|null  $provider  Optional chargeback provider
     * @param  string|null  $arn  Optional acquirer reference number
     * @param  string|null  $initiator  Optional chargeback initiator
     */
    public function __construct(
        public string $chargeId,
        public float $amount,
        public string $type,
        public int $expiry,
        public ?string $stage = null,
        public ?string $status = null,
        public ?string $uploadedProof = null,
        public ?string $comment = null,
        public ?string $provider = null,
        public ?string $arn = null,
        public ?string $initiator = null,
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
            'amount' => $this->amount,
            'stage' => $this->stage,
            'status' => $this->status,
            'type' => $this->type,
            'uploaded_proof' => $this->uploadedProof,
            'comment' => $this->comment,
            'provider' => $this->provider,
            'arn' => $this->arn,
            'initiator' => $this->initiator,
            'expiry' => $this->expiry,
        ], fn ($value) => $value !== null);
    }
}
