<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\AuthorizationData;
use Gowelle\Flutterwave\Data\DirectChargeData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Events\DirectChargeCreated;
use Gowelle\Flutterwave\Events\DirectChargeUpdated;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

/**
 * Flutterwave Direct Charge Service
 *
 * Handles direct charge orchestrator flow for Flutterwave v4 API.
 * This service manages the /orchestration/direct-charges endpoint which combines
 * customer, payment method, and charge creation in a single request.
 */
final class FlutterwaveDirectChargeService
{
    use BuildsWavable;

    public function __construct(
        private readonly FlutterwaveBaseService $flutterwaveBaseService,
    ) {}

    /**
     * Create a direct charge
     *
     * Initiates a charge using the orchestrator endpoint. This combines customer
     * and payment method creation with the charge request.
     *
     * @param  array<string, mixed>  $data  Charge data including amount, currency, reference, customer, payment_method, redirect_url
     *
     * @throws FlutterwaveApiException
     */
    public function create(array $data): DirectChargeData
    {
        $wavable = $this->buildWavable(
            $data,
            FlutterwaveApi::DIRECT_CHARGE,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $chargeData = DirectChargeData::fromApi($this->flutterwaveBaseService->create(FlutterwaveApi::DIRECT_CHARGE, $wavable, $data)->data);

        // Dispatch event for applications to listen to
        event(new DirectChargeCreated($chargeData, $data));

        return $chargeData;
    }

    /**
     * Update charge authorization
     *
     * Submits customer authorization (PIN, OTP, AVS) to complete a charge.
     *
     * @param  string  $chargeId  The charge ID from Flutterwave
     * @param  AuthorizationData  $authorizationData  Authorization payload
     *
     * @throws FlutterwaveApiException
     */
    public function updateChargeAuthorization(string $chargeId, AuthorizationData $authorizationData): DirectChargeData
    {
        $wavable = $this->buildWavable(
            ['charge_id' => $chargeId],
            FlutterwaveApi::DIRECT_CHARGE,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $chargeData = DirectChargeData::fromApi($this->flutterwaveBaseService->update(FlutterwaveApi::DIRECT_CHARGE, $wavable, $chargeId, $authorizationData->toApiPayload())->data);

        // Dispatch event for applications to listen to
        event(new DirectChargeUpdated($chargeData, $authorizationData));

        return $chargeData;
    }

    public function status(string $id): DirectChargeStatus
    {
        $wavable = $this->buildWavable(
            ['id' => $id],
            FlutterwaveApi::DIRECT_CHARGE,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::DIRECT_CHARGE, $wavable, $id);

        $chargeData = DirectChargeData::fromApi($response->data ?? []);

        return $chargeData->status;
    }
}
