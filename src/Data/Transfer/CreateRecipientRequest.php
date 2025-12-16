<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

use Gowelle\Flutterwave\Enums\TransferType;

/**
 * Request DTO for creating a transfer recipient.
 */
final readonly class CreateRecipientRequest
{
    public function __construct(
        public TransferType $type,
        public string $currency,
        public ?string $accountNumber = null,
        public ?string $bankCode = null,
        public ?string $network = null,
        public ?string $phoneNumber = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
    ) {}

    /**
     * Create a bank recipient request
     */
    public static function bank(
        string $currency,
        string $accountNumber,
        string $bankCode,
    ): self {
        return new self(
            type: TransferType::BANK,
            currency: $currency,
            accountNumber: $accountNumber,
            bankCode: $bankCode,
        );
    }

    /**
     * Create a mobile money recipient request
     */
    public static function mobileMoney(
        string $currency,
        string $network,
        string $phoneNumber,
        string $firstName,
        string $lastName,
    ): self {
        return new self(
            type: TransferType::MOBILE_MONEY,
            currency: $currency,
            network: $network,
            phoneNumber: $phoneNumber,
            firstName: $firstName,
            lastName: $lastName,
        );
    }

    /**
     * Convert to API payload
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $payload = [
            'type' => $this->type->value,
            'currency' => $this->currency,
        ];

        if ($this->type === TransferType::BANK) {
            $payload['bank'] = [
                'account_number' => $this->accountNumber,
                'code' => $this->bankCode,
            ];
        }

        if ($this->type === TransferType::MOBILE_MONEY) {
            $payload['mobile_money'] = [
                'network' => $this->network,
                'msisdn' => $this->phoneNumber,
            ];
            $payload['name'] = [
                'first' => $this->firstName,
                'last' => $this->lastName,
            ];
        }

        if ($this->email !== null) {
            $payload['email'] = $this->email;
        }

        return $payload;
    }
}
