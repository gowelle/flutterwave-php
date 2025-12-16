<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Listeners;

use Gowelle\Flutterwave\Data\NextActionData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;
use Gowelle\Flutterwave\Models\ChargeSession;

/**
 * Update Charge Session From Webhook Listener
 *
 * Automatically updates ChargeSession records when webhook events are received.
 * This listener is registered automatically if charge_sessions.enabled = true.
 */
final class UpdateChargeSessionFromWebhook
{
    /**
     * Handle the event.
     */
    public function handle(FlutterwaveWebhookReceived $event): void
    {
        // Only process if charge sessions are enabled
        if (! config('flutterwave.charge_sessions.enabled', true)) {
            return;
        }

        // Only process charge-related events
        if (! $event->isPaymentEvent()) {
            return;
        }

        $data = $event->getTransactionData();
        $chargeId = $data['id'] ?? null;

        if (! $chargeId) {
            return;
        }

        // Find existing session by remote charge ID
        $session = ChargeSession::byRemoteChargeId((string) $chargeId)->first();

        if (! $session) {
            return;
        }

        // Update status from webhook
        $status = DirectChargeStatus::fromApiResponse($data['status'] ?? 'failed');
        $session->updateStatus($status);

        // Update next action if present
        if (isset($data['next_action'])) {
            $nextAction = NextActionData::fromApi($data['next_action']);
            $session->updateNextAction($nextAction);
        }

        // Store webhook event metadata
        $session->setMeta('last_webhook_event', $event->getEventType());
        $session->setMeta('last_webhook_at', now()->toIso8601String());
        $session->save();
    }
}
