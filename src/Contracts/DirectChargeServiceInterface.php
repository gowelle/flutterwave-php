<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Contracts;

use Gowelle\Flutterwave\Data\AuthorizationData;
use Gowelle\Flutterwave\Data\DirectChargeData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;

/**
 * Direct Charge Service Interface
 *
 * Contract for managing direct charge operations via Flutterwave's
 * orchestration/direct-charges endpoint.
 */
interface DirectChargeServiceInterface
{
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
    public function create(array $data): DirectChargeData;

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
    public function updateChargeAuthorization(string $chargeId, AuthorizationData $authorizationData): DirectChargeData;

    /**
     * Get charge status
     *
     * @param  string  $id  The charge ID
     */
    public function status(string $id): DirectChargeStatus;
}
