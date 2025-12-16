<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Contracts\PaymentsServiceInterface;
use Gowelle\Flutterwave\Data\ChargeData;
use Gowelle\Flutterwave\Data\PaymentMethodData;
use Gowelle\Flutterwave\Data\PaymentMethods\PaymentMethodFactory;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwavePaymentsService implements PaymentsServiceInterface
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Get payment methods
     *
     * @param  array<string, mixed>  $data  Query parameters
     * @return PaymentMethodData[]
     *
     * @example
     * $methods = $paymentsService->methods(['customer_id' => 'cust_123']);
     * foreach ($methods as $method) {
     *     echo $method->type; // 'card', 'bank_transfer', etc.
     * }
     */
    public function methods(array $data): array
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::PAYMENT_METHODS, $this->flutterwaveBaseService->getConfig()->isProduction());

        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::PAYMENT_METHODS, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_filter(array_map(
            fn (array $item) => PaymentMethodFactory::create($item),
            $response->data
        ));
    }

    /**
     * Create a payment method
     *
     * @param  array<string, mixed>  $data  Payment method data
     *
     * @example
     * $paymentMethod = $paymentsService->createMethod([
     *     'type' => 'card',
     *     'customer_id' => 'cust_123',
     *     'card_number' => '5531886652142950',
     *     'cvv' => '564',
     *     'expiry_month' => '09',
     *     'expiry_year' => '32',
     * ]);
     */
    public function createMethod(array $data): PaymentMethodData
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::PAYMENT_METHODS, $this->flutterwaveBaseService->getConfig()->isProduction());

        $response = $this->flutterwaveBaseService->create(FlutterwaveApi::PAYMENT_METHODS, $wavable, $data);

        return PaymentMethodFactory::create($response->data ?? []);
    }

    /**
     * Get a payment method
     *
     * @param  string  $id  Payment method ID
     *
     * @example
     * $method = $paymentsService->getMethod('pm_123456');
     * if ($method !== null) {
     *     echo "Type: {$method->type}";
     * }
     */
    public function getMethod(string $id): ?PaymentMethodData
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::PAYMENT_METHODS, $this->flutterwaveBaseService->getConfig()->isProduction());

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::PAYMENT_METHODS, $wavable, $id);

        if ($response->data === null) {
            return null;
        }

        return PaymentMethodFactory::create($response->data);
    }

    /**
     * Process a charge
     *
     * @param  array<string, mixed>  $data  Must include: payment_method_type, customer_id, payment_method_id, amount, currency, reference, redirect_url
     * @param  callable  $callback  The callback to call with the trace ID if the charge is successful
     *
     * @throws FlutterwaveApiException
     *
     * @example
     * $charge = $paymentsService->process([
     *     'payment_method_type' => 'card',
     *     'customer_id' => 'cust_123',
     *     'payment_method_id' => 'pm_456',
     *     'amount' => 10000,
     *     'currency' => 'TZS',
     *     'reference' => 'ORDER-' . uniqid(),
     *     'redirect_url' => route('payment.callback'),
     * ], function ($traceId) {
     *     // Store trace ID for tracking
     * });
     */
    public function process(array $data, callable $callback): ChargeData
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

        // Call the callback with the trace ID if successful
        if ($response->isSuccessful()) {
            $callback($wavable->getTraceId());
        }

        return ChargeData::fromApi($response->data ?? []);
    }

    /**
     * Get the status of a charge
     *
     * @param  string  $id  The charge ID
     *
     * @throws FlutterwaveApiException
     *
     * @example
     * $status = $paymentsService->status('chg_123456');
     * if ($status === DirectChargeStatus::SUCCEEDED) {
     *     // Payment completed
     * }
     */
    public function status(string $id): DirectChargeStatus
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::CHARGE, $this->flutterwaveBaseService->getConfig()->isProduction());

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::CHARGE, $wavable, $id);

        $chargeData = ChargeData::fromApi($response->data ?? []);

        return DirectChargeStatus::fromApiResponse($chargeData->status);
    }
}
