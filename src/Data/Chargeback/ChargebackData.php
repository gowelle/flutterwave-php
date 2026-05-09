<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Chargeback;

/**
 * Data model for Flutterwave chargeback API responses.
 *
 * @see https://developer.flutterwave.com/reference/chargebacks_list
 */
final readonly class ChargebackData
{
    /**
     * @param  float|null  $amount
     */
    public function __construct(
        public string $id,
        public string $chargeId,
        public string $status,
        public ?float $amount = null,
        public ?string $stage = null,
        public ?string $type = null,
        public ?string $uploadedProof = null,
        public ?string $comment = null,
        public ?string $provider = null,
        public ?string $arn = null,
        public ?string $initiator = null,
        public ?int $expiry = null,
        public ?string $dueDatetime = null,
        public ?string $proofData = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}

    /**
     * Create from API response data
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            chargeId: (string) ($data['charge_id'] ?? ''),
            status: $data['status'] ?? 'unknown',
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            stage: isset($data['stage']) ? (string) $data['stage'] : null,
            type: isset($data['type']) ? (string) $data['type'] : null,
            uploadedProof: isset($data['uploaded_proof']) ? (string) $data['uploaded_proof'] : null,
            comment: $data['comment'] ?? null,
            provider: isset($data['provider']) ? (string) $data['provider'] : null,
            arn: isset($data['arn']) ? (string) $data['arn'] : null,
            initiator: isset($data['initiator']) ? (string) $data['initiator'] : null,
            expiry: isset($data['expiry']) ? (int) $data['expiry'] : null,
            dueDatetime: isset($data['due_datetime']) ? (string) $data['due_datetime'] : null,
            proofData: isset($data['proof_data']) ? (string) $data['proof_data'] : null,
            createdAt: $data['created_at'] ?? $data['created_datetime'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    /**
     * Check if chargeback has been accepted
     */
    public function isAccepted(): bool
    {
        return mb_strtolower($this->status) === 'accepted';
    }

    /**
     * Check if chargeback has been declined
     */
    public function isDeclined(): bool
    {
        return mb_strtolower($this->status) === 'declined';
    }

    /**
     * Check if chargeback is pending
     */
    public function isPending(): bool
    {
        return \in_array(mb_strtolower($this->status), ['pending', 'open', 'initiated'], true);
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
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
            'due_datetime' => $this->dueDatetime,
            'proof_data' => $this->proofData,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
