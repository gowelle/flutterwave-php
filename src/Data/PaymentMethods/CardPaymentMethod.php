<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\PaymentMethods;

use Gowelle\Flutterwave\Data\PaymentMethodData;

final readonly class CardPaymentMethod extends PaymentMethodData
{
    public function __construct(
        string $id,
        string $customerId,
        array $data,
        public ?string $last4,
        public ?string $network,
        public ?string $expiryMonth,
        public ?string $expiryYear,
        public ?array $billingAddress,
        public ?string $cardHolderName,
        ?string $createdAt = null,
    ) {
        parent::__construct(
            id: $id,
            type: 'card',
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
            customerId: $data['customer_id'] ?? $data['customerId'] ?? '',
            data: $data['data'] ?? [],
            last4: $data['data']['card']['last4'] ?? null,
            network: $data['data']['card']['network'] ?? null,
            expiryMonth: $data['data']['card']['expiry_month'] ?? null,
            expiryYear: $data['data']['card']['expiry_year'] ?? null,
            billingAddress: $data['data']['card']['billing_address'] ?? null,
            cardHolderName: $data['data']['card']['card_holder_name'] ?? null,
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
                'card' => [
                    'last4' => $this->last4,
                    'network' => $this->network,
                    'expiry_month' => $this->expiryMonth,
                    'expiry_year' => $this->expiryYear,
                    'billing_address' => $this->billingAddress,
                    'card_holder_name' => $this->cardHolderName,
                ],
            ],
            'created_at' => $this->createdAt,
        ];
    }
}
