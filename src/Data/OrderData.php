<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

final readonly class OrderData
{
    /**
     * @param  array<int, array{name: string, quantity: int, amount: float}>  $items
     * @param  array{name: string, email: string, phone_number?: string}  $customer
     */
    public function __construct(
        public string $id,
        public string $orderReference,
        public float $amount,
        public string $currency,
        public array $customer,
        public array $items,
        public ?string $status = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApi(array $data): self
    {
        $items = [];
        if (isset($data['items']) && \is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $items[] = [
                    'name' => $item['name'] ?? '',
                    'quantity' => (int) ($item['quantity'] ?? 0),
                    'amount' => (float) ($item['amount'] ?? 0),
                ];
            }
        }

        $customer = [];
        if (isset($data['customer']) && \is_array($data['customer'])) {
            $customer = [
                'name' => $data['customer']['name'] ?? '',
                'email' => $data['customer']['email'] ?? '',
                'phone_number' => $data['customer']['phone_number'] ?? null,
            ];
        }

        return new self(
            id: (string) $data['id'],
            orderReference: $data['order_reference'] ?? $data['orderReference'] ?? '',
            amount: (float) ($data['amount'] ?? 0),
            currency: $data['currency'] ?? '',
            customer: $customer,
            items: $items,
            status: $data['status'] ?? null,
            createdAt: $data['created_at'] ?? $data['createdAt'] ?? null,
        );
    }

    /**
     * Create collection from API response
     *
     * @return OrderData[]
     */
    public static function collection(array $items): array
    {
        return array_map(fn (array $item) => self::fromApi($item), $items);
    }

    /**
     * Convert to array for general serialization
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'order_reference' => $this->orderReference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer' => $this->customer,
            'items' => $this->items,
            'status' => $this->status,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * Convert to API request format for creating/updating orders
     */
    public function toRequestArray(): array
    {
        $data = [
            'order_reference' => $this->orderReference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'customer' => $this->customer,
            'items' => $this->items,
        ];

        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        return $data;
    }
}
