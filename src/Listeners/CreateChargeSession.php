<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Listeners;

use Gowelle\Flutterwave\Events\FlutterwaveChargeCreated;
use Gowelle\Flutterwave\Models\ChargeSession;

/**
 * Create Charge Session Listener
 *
 * Automatically creates ChargeSession records when direct charges are created.
 * This listener is only active if charge_sessions.auto_create = true.
 * Applications can override or disable this behavior.
 */
final class CreateChargeSession
{
    /**
     * Handle the event.
     */
    public function handle(FlutterwaveChargeCreated $event): void
    {
        // Only create if auto_create is enabled
        if (!config('flutterwave.charge_sessions.auto_create', false)) {
            return;
        }

        // Only create if charge sessions are enabled
        if (!config('flutterwave.charge_sessions.enabled', true)) {
            return;
        }

        $chargeData = $event->chargeData;
        $requestData = $event->requestData;

        // Extract user_id and payment_id from request data
        // Applications should provide these in the request
        $userId = $requestData['user_id'] ?? null;
        $paymentId = $requestData['payment_id'] ?? null;

        if (!$userId || !$paymentId) {
            // Cannot create session without required IDs
            return;
        }

        // Check if session already exists (avoid duplicates)
        $existingSession = ChargeSession::byRemoteChargeId($chargeData->id)->first();
        if ($existingSession) {
            return;
        }

        // Create new charge session
        ChargeSession::create([
            'user_id' => $userId,
            'payment_id' => $paymentId,
            'remote_charge_id' => $chargeData->id,
            'remote_customer_id' => $chargeData->customerId,
            'status' => $chargeData->status->value,
            'next_action_type' => $chargeData->nextAction->type->value,
            'next_action_data' => $chargeData->nextAction->data,
            'payment_method_type' => $chargeData->getPaymentMethodType(),
            'payment_method_details' => $chargeData->paymentMethodDetails,
            'meta' => [
                'amount' => $chargeData->amount,
                'currency' => $chargeData->currency,
                'reference' => $chargeData->reference,
                'created_via' => 'auto_create_listener',
            ],
        ]);
    }
}

