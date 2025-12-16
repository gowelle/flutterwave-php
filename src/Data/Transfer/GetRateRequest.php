<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Request DTO for getting transfer rates.
 */
final readonly class GetRateRequest
{
    public function __construct(
        public string $sourceCurrency,
        public string $destinationCurrency,
        public float $amount,
    ) {}

    /**
     * Convert to API payload
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        return [
            'source_currency' => $this->sourceCurrency,
            'destination_currency' => $this->destinationCurrency,
            'amount' => $this->amount,
        ];
    }
}
