<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\PaymentMethods;

use Gowelle\Flutterwave\Data\PaymentMethodData;

final readonly class MobileMoneyPaymentMethod extends PaymentMethodData
{
    public function __construct(
        string $id,
        string $customerId,
        array $data,
        public ?string $network,
        public ?string $phoneNumber,
        public ?string $countryCode,
        ?string $createdAt = null,
    ) {
        parent::__construct(
            id: $id,
            type: 'mobile_money',
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
            network: $data['data']['mobile_money']['network'] ?? null,
            phoneNumber: $data['data']['mobile_money']['phone_number'] ?? null,
            countryCode: $data['data']['mobile_money']['country_code'] ?? null,
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
                'mobile_money' => [
                    'network' => $this->network,
                    'phone_number' => $this->phoneNumber,
                    'country_code' => $this->countryCode,
                ],
            ],
            'created_at' => $this->createdAt,
        ];
    }
}
