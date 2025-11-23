<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

final readonly class SettlementData
{
    public function __construct(
        public string $id,
        public float $amount,
        public string $currency,
        public string $status,
        public ?string $settlementDate = null,
        public ?array $meta = null,
        public ?string $createdAt = null,
    ) {}

    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            amount: (float) ($data['amount'] ?? 0.0),
            currency: $data['currency'] ?? '',
            status: $data['status'] ?? 'unknown',
            settlementDate: $data['settlement_date'] ?? null,
            meta: $data['meta'] ?? null,
            createdAt: $data['created_at'] ?? $data['created_datetime'] ?? null,
        );
    }

    public static function collection(array $items): array
    {
        return array_map(fn (array $item) => self::fromApi($item), $items);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'settlement_date' => $this->settlementDate,
            'meta' => $this->meta,
            'created_at' => $this->createdAt,
        ];
    }
}
