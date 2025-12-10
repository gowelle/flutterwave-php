<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Contracts;

use Gowelle\Flutterwave\Data\ChargeData;
use Gowelle\Flutterwave\Data\PaymentMethodData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;

/**
 * Payments Service Interface
 *
 * Contract for managing payment method and charge operations in Flutterwave.
 */
interface PaymentsServiceInterface
{
    /**
     * Get payment methods
     *
     * @param  array<string, mixed>  $data  Query parameters
     * @return PaymentMethodData[]
     */
    public function methods(array $data): array;

    /**
     * Create a payment method
     *
     * @param  array<string, mixed>  $data  Payment method data
     */
    public function createMethod(array $data): PaymentMethodData;

    /**
     * Get a payment method
     *
     * @param  string  $id  Payment method ID
     */
    public function getMethod(string $id): ?PaymentMethodData;

    /**
     * Process a charge
     *
     * @param  array<string, mixed>  $data  Charge data
     * @param  callable  $callback  Callback for trace ID on success
     *
     * @throws FlutterwaveApiException
     */
    public function process(array $data, callable $callback): ChargeData;

    /**
     * Get charge status
     *
     * @param  string  $id  Charge ID
     *
     * @throws FlutterwaveApiException
     */
    public function status(string $id): DirectChargeStatus;
}
