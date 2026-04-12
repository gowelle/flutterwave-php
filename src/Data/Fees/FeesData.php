<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Fees;

/**
 * Data model for the Flutterwave fees API response.
 *
 * @see https://developer.flutterwave.com/reference/fees_get
 */
final readonly class FeesData
{
    public function __construct(
        public float $chargeAmount,
        public float $fee,
        public float $merchantFee,
        public float $flutterwaveFee,
        public string $currency,
    ) {}

    /**
     * Create from API response data
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            chargeAmount: (float) ($data['charge_amount'] ?? 0.0),
            fee: (float) ($data['fee'] ?? 0.0),
            merchantFee: (float) ($data['merchant_fee'] ?? 0.0),
            flutterwaveFee: (float) ($data['flutterwave_fee'] ?? 0.0),
            currency: $data['currency'] ?? '',
        );
    }

    /**
     * Get the total fee (merchant + Flutterwave)
     */
    public function totalFee(): float
    {
        return $this->merchantFee + $this->flutterwaveFee;
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'charge_amount'    => $this->chargeAmount,
            'fee'              => $this->fee,
            'merchant_fee'     => $this->merchantFee,
            'flutterwave_fee'  => $this->flutterwaveFee,
            'currency'         => $this->currency,
        ];
    }
}
