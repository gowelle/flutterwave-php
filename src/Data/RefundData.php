<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use Gowelle\Flutterwave\Enums\RefundStatus;

final readonly class RefundData
{
    /**
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public string $id,
        public string $chargeId,
        public float $amountRefunded,
        public RefundStatus $status,
        public ?string $reason = null,
        public ?array $meta = null,
        public ?string $createdAt = null,
    ) {}

    public static function fromApi(array $data): self
    {
        $status = RefundStatus::fromApiResponse($data['status'] ?? 'failed');

        return new self(
            id: (string) $data['id'],
            chargeId: $data['charge_id'] ?? '',
            amountRefunded: (float) ($data['amount_refunded'] ?? $data['amount'] ?? 0.0),
            status: $status,
            reason: $data['reason'] ?? null,
            meta: isset($data['meta']) && \is_array($data['meta']) ? $data['meta'] : null,
            createdAt: $data['created_datetime'] ?? $data['created_at'] ?? null,
        );
    }

    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'charge_id' => $this->chargeId,
            'amount_refunded' => $this->amountRefunded,
            'status' => $this->status->value,
            'reason' => $this->reason,
            'meta' => $this->meta,
            'created_at' => $this->createdAt,
        ];
    }
}
