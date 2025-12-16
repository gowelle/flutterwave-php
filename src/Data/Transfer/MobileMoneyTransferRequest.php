<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

use Gowelle\Flutterwave\Enums\TransferAction;

/**
 * Request DTO for mobile money transfers via direct transfer orchestrator.
 */
final readonly class MobileMoneyTransferRequest
{
    public function __construct(
        public float $amount,
        public string $sourceCurrency,
        public string $destinationCurrency,
        public string $network,
        public string $phoneNumber,
        public string $firstName,
        public string $lastName,
        public string $reference,
        public TransferAction $action = TransferAction::INSTANT,
        public ?string $narration = null,
        public ?string $scenarioKey = null,
    ) {}

    /**
     * Convert to API payload
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $payload = [
            'action' => $this->action->value,
            'type' => 'mobile_money',
            'reference' => $this->reference,
            'narration' => $this->narration,
            'payment_instruction' => [
                'source_currency' => $this->sourceCurrency,
                'destination_currency' => $this->destinationCurrency,
                'amount' => [
                    'value' => $this->amount,
                    'applies_to' => 'destination_currency',
                ],
                'recipient' => [
                    'name' => [
                        'first' => $this->firstName,
                        'last' => $this->lastName,
                    ],
                    'mobile_money' => [
                        'network' => $this->network,
                        'msisdn' => $this->phoneNumber,
                    ],
                ],
            ],
        ];

        // Include scenario_key in payload if specified for testing
        if ($this->scenarioKey !== null) {
            $payload['scenario_key'] = $this->scenarioKey;
        }

        return $payload;
    }
}
