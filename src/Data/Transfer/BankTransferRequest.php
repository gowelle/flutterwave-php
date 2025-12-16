<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

use Gowelle\Flutterwave\Enums\TransferAction;

/**
 * Request DTO for bank transfers via direct transfer orchestrator.
 */
final readonly class BankTransferRequest
{
    public function __construct(
        public float $amount,
        public string $sourceCurrency,
        public string $destinationCurrency,
        public string $accountNumber,
        public string $bankCode,
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
            'type' => 'bank',
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
                    'bank' => [
                        'account_number' => $this->accountNumber,
                        'code' => $this->bankCode,
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
