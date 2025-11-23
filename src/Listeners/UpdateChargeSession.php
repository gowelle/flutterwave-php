<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Listeners;

use Gowelle\Flutterwave\Events\DirectChargeUpdated;
use Gowelle\Flutterwave\Models\ChargeSession;

/**
 * Update Charge Session Listener
 *
 * Automatically updates ChargeSession records when charge authorization is submitted.
 * This listener is only active if charge_sessions.auto_create = true.
 * Applications can override or disable this behavior.
 */
final class UpdateChargeSession
{
    /**
     * Handle the event.
     */
    public function handle(DirectChargeUpdated $event): void
    {
        // Only update if auto_create is enabled
        if (!config('flutterwave.charge_sessions.auto_create', false)) {
            return;
        }

        // Only update if charge sessions are enabled
        if (!config('flutterwave.charge_sessions.enabled', true)) {
            return;
        }

        $chargeData = $event->chargeData;
        $authorizationData = $event->authorizationData;

        // Find existing session by remote charge ID
        $session = ChargeSession::byRemoteChargeId($chargeData->id)->first();

        if (!$session) {
            // Session doesn't exist - this is expected if auto_create is disabled
            // Applications can create sessions manually if needed
            return;
        }

        // Update session status
        $session->updateStatus($chargeData->status);

        // Update next action
        $session->updateNextAction($chargeData->nextAction);

        // Store authorization attempt metadata
        $session->setMeta('last_authorization_type', $authorizationData->type->value);
        $session->setMeta('last_authorization_at', now()->toIso8601String());
        $session->save();
    }
}

