<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

abstract readonly class PaymentMethodData
{
    public function __construct(
        public string $id,
        public string $type,
        public string $customerId,
        public array $data,
        public ?string $createdAt = null,
    ) {}

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'customer_id' => $this->customerId,
            'data' => $this->data,
            'created_at' => $this->createdAt,
        ];
    }
}
