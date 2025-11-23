<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

final readonly class RefundData
{
    public function __construct(
        public string $id,
        public string $chargeId,
        public float $amount,
        public string $currency,
        public string $status,
        public ?string $reason = null,
        public ?string $createdAt = null,
    ) {}

    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            chargeId: $data['charge_id'] ?? '',
            amount: (float) ($data['amount'] ?? 0.0),
            currency: $data['currency'] ?? '',
            status: $data['status'] ?? 'unknown',
            reason: $data['reason'] ?? null,
            createdAt: $data['created_at'] ?? $data['created_datetime'] ?? null,
        );
    }

    public function isSuccessful(): bool
    {
        return \in_array(mb_strtolower($this->status), ['succeeded', 'successful', 'completed'], true);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'charge_id' => $this->chargeId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'reason' => $this->reason,
            'created_at' => $this->createdAt,
        ];
    }
}
