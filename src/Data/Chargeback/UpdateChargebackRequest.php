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
     * @param  string  $status  Updated status of the chargeback (accepted or declined)
     * @param  string|null  $uploadedProof  Optional URL to supporting proof
     * @param  string|null  $comment  Optional comment
     * @param  string|null  $provider  Optional chargeback provider
     * @param  string|null  $arn  Optional acquirer reference number
     * @param  string|null  $dueDatetime  Optional deadline in ISO 8601 format
     * @param  string|null  $proofData  Optional base64-encoded proof document
     */
    public function __construct(
        public string $status,
        public ?string $uploadedProof = null,
        public ?string $comment = null,
        public ?string $provider = null,
        public ?string $arn = null,
        public ?string $dueDatetime = null,
        public ?string $proofData = null,
    ) {}

    /**
     * Accept a chargeback.
     */
    public static function accept(?string $comment = null): self
    {
        return new self(status: 'accepted', comment: $comment);
    }

    /**
     * Decline a chargeback with optional evidence.
     */
    public static function decline(
        string $message,
        ?string $evidenceUrl = null,
        ?string $proofData = null,
        ?string $provider = null,
        ?string $arn = null,
        ?string $dueDatetime = null,
    ): self {
        return new self(
            status: 'declined',
            uploadedProof: $evidenceUrl,
            comment: $message,
            provider: $provider,
            arn: $arn,
            dueDatetime: $dueDatetime,
            proofData: $proofData,
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
            'status' => $this->status,
            'uploaded_proof' => $this->uploadedProof,
            'comment' => $this->comment,
            'provider' => $this->provider,
            'arn' => $this->arn,
            'due_datetime' => $this->dueDatetime,
            'proof_data' => $this->proofData,
        ], fn ($value) => $value !== null);
    }
}
