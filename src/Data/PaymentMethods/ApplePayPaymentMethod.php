<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\PaymentMethods;

use Gowelle\Flutterwave\Data\PaymentMethodData;

final readonly class ApplePayPaymentMethod extends PaymentMethodData
{
    public function __construct(
        string $id,
        string $customerId,
        array $data,
        public ?string $cardHolderName,
        ?string $createdAt = null,
    ) {
        parent::__construct(
            id: $id,
            type: 'applepay',
            customerId: $customerId,
            data: $data,
            createdAt: $createdAt,
        );
    }

    /**
     * Create from API response
     */
    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            customerId: $data['customer_id'] ?? '',
            data: $data['data'] ?? [],
            cardHolderName: $data['data']['applepay']['card_holder_name'] ?? null,
            createdAt: $data['created_datetime'] ?? $data['created_at'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'customer_id' => $this->customerId,
            'data' => [
                'applepay' => [
                    'card_holder_name' => $this->cardHolderName,
                ],
            ],
            'created_at' => $this->createdAt,
        ];
    }
}
