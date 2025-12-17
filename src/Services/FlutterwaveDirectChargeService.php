<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Contracts\DirectChargeServiceInterface;
use Gowelle\Flutterwave\Data\AuthorizationData;
use Gowelle\Flutterwave\Data\DirectChargeData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Events\FlutterwaveChargeCreated;
use Gowelle\Flutterwave\Events\FlutterwaveChargeUpdated;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

/**
 * Flutterwave Direct Charge Service
 *
 * Handles direct charge orchestrator flow for Flutterwave v4 API.
 * This service manages the /orchestration/direct-charges endpoint which combines
 * customer, payment method, and charge creation in a single request.
 */
final class FlutterwaveDirectChargeService implements DirectChargeServiceInterface
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
     *
     * @example
     * $chargeData = $directChargeService->create([
     *     'amount' => 10000,
     *     'currency' => 'TZS',
     *     'reference' => 'ORDER-' . uniqid(),
     *     'customer' => [
     *         'email' => 'customer@example.com',
     *         'name' => 'John Doe',
     *     ],
     *     'payment_method' => [
     *         'type' => 'card',
     *         'card_number' => '5531886652142950',
     *         'cvv' => '564',
     *         'expiry_month' => '09',
     *         'expiry_year' => '32',
     *     ],
     *     'redirect_url' => route('payment.callback'),
     * ]);
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
        event(new FlutterwaveChargeCreated($chargeData, $data));

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
     *
     * @example
     * // For PIN authorization
     * $authorization = AuthorizationData::createPin($nonce, $encryptedPin);
     * $chargeData = $directChargeService->updateChargeAuthorization($chargeId, $authorization);
     *
     * // For OTP authorization
     * $authorization = AuthorizationData::createOtp($otpCode);
     * $chargeData = $directChargeService->updateChargeAuthorization($chargeId, $authorization);
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
        event(new FlutterwaveChargeUpdated($chargeData, $authorizationData));

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

    /**
     * Create a direct charge from DTO
     *
     * Type-safe alternative to create() using CreateDirectChargeRequest DTO.
     *
     * @param  \Gowelle\Flutterwave\Data\DirectCharge\CreateDirectChargeRequest  $request  Charge request DTO
     *
     * @throws FlutterwaveApiException
     *
     * @example
     * use Gowelle\Flutterwave\Data\DirectCharge\CreateDirectChargeRequest;
     *
     * $request = CreateDirectChargeRequest::make(
     *     amount: 10000,
     *     currency: 'NGN',
     *     reference: 'ORDER-' . uniqid(),
     *     customer: ['email' => 'user@example.com', 'name' => 'John Doe'],
     *     paymentMethod: ['type' => 'card', 'card' => [...encrypted data...]],
     * );
     * $charge = $service->createFromDto($request);
     */
    public function createFromDto(\Gowelle\Flutterwave\Data\DirectCharge\CreateDirectChargeRequest $request): DirectChargeData
    {
        return $this->create($request->toApiPayload());
    }

    /**
     * Retrieve a direct charge
     *
     * Alias for getting full charge data instead of just status.
     *
     * @param  string  $id  The charge ID
     *
     * @throws FlutterwaveApiException
     */
    public function retrieve(string $id): DirectChargeData
    {
        $wavable = $this->buildWavable(
            ['id' => $id],
            FlutterwaveApi::DIRECT_CHARGE,
            $this->flutterwaveBaseService->getConfig()->isProduction(),
        );

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::DIRECT_CHARGE, $wavable, $id);

        return DirectChargeData::fromApi($response->data ?? []);
    }
}
