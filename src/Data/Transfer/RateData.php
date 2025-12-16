<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Response DTO for transfer rate data.
 */
final readonly class RateData
{
    public function __construct(
        public string $sourceCurrency,
        public string $destinationCurrency,
        public float $rate,
        public float $sourceAmount,
        public float $destinationAmount,
        public ?float $fee = null,
    ) {}

    /**
     * Create from Flutterwave API response
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            sourceCurrency: $data['source_currency'] ?? $data['source'] ?? '',
            destinationCurrency: $data['destination_currency'] ?? $data['destination'] ?? '',
            rate: (float) ($data['rate'] ?? 0),
            sourceAmount: (float) ($data['source_amount'] ?? 0),
            destinationAmount: (float) ($data['destination_amount'] ?? 0),
            fee: isset($data['fee']) ? (float) $data['fee'] : null,
        );
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'source_currency' => $this->sourceCurrency,
            'destination_currency' => $this->destinationCurrency,
            'rate' => $this->rate,
            'source_amount' => $this->sourceAmount,
            'destination_amount' => $this->destinationAmount,
            'fee' => $this->fee,
        ];
    }
}
