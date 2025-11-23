<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;


use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\ChargeData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwavePaymentsService
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Get payment methods
     */
    public function methods(array $data): ApiResponse
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::PAYMENT_METHODS, $this->flutterwaveBaseService->getConfig()->isProduction());

        return $this->flutterwaveBaseService->list(FlutterwaveApi::PAYMENT_METHODS, $wavable, $data);
    }

    /**
     * Create a payment method
     */
    public function createMethod(array $data): ApiResponse
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::PAYMENT_METHODS, $this->flutterwaveBaseService->getConfig()->isProduction());

        return $this->flutterwaveBaseService->create(FlutterwaveApi::PAYMENT_METHODS, $wavable, $data);
    }

    /**
     * Get a payment method
     */
    public function getMethod(string $id): ?ApiResponse
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::PAYMENT_METHODS, $this->flutterwaveBaseService->getConfig()->isProduction());

        return $this->flutterwaveBaseService->retrieve(FlutterwaveApi::PAYMENT_METHODS, $wavable, $id);
    }

    /**
     * Create a charge
     *
     * @param  array  $data  Must include: payment_method_type, customer_id, payment_method_id, amount, currency, reference, redirect_url
     * @param  callable  $callback  The callback to call with the trace ID if the charge is successful
     * @return ApiResponse
     *
     * @throws FlutterwaveApiException
     */
    public function process(array $data, callable $callback): ApiResponse
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::CHARGE, $this->flutterwaveBaseService->getConfig()->isProduction());

        // Include payment_method_type in meta for ChargeHandler to use
        $chargeData = $data;
        if (isset($data['payment_method_type'])) {
            $chargeData['meta'] = array_merge(
                $data['meta'] ?? [],
                ['payment_method_type' => $data['payment_method_type']],
            );
        }

        $response = $this->flutterwaveBaseService->create(FlutterwaveApi::CHARGE, $wavable, $chargeData);

        // Call the callback with the trace ID
       $response->isSuccessful() ? $callback($wavable->getTraceId()) : null;

        return $response;
    }

    /**
     * Get the status of a charge
     *
     * @param  string  $id  The charge ID
     * @return DirectChargeStatus
     *
     * @throws FlutterwaveApiException
     */
    public function status(string $id): DirectChargeStatus
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::CHARGE, $this->flutterwaveBaseService->getConfig()->isProduction());

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::CHARGE, $wavable, $id);

        $chargeData = ChargeData::fromApi($response->data ?? []);

        return DirectChargeStatus::fromApiResponse($chargeData->status);
    }
}
