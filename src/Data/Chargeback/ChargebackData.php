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
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public string $id,
        public string $chargeId,
        public string $reason,
        public string $status,
        public ?string $comment = null,
        public ?array $meta = null,
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
            reason: $data['reason'] ?? '',
            status: $data['status'] ?? 'unknown',
            comment: $data['comment'] ?? null,
            meta: isset($data['meta']) && \is_array($data['meta']) ? $data['meta'] : null,
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
            'id'         => $this->id,
            'charge_id'  => $this->chargeId,
            'reason'     => $this->reason,
            'status'     => $this->status,
            'comment'    => $this->comment,
            'meta'       => $this->meta,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
