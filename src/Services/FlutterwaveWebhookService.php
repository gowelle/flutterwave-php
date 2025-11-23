<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Enums\WebhookEventType;
use Gowelle\Flutterwave\Exceptions\WebhookVerificationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class FlutterwaveWebhookService
{
    public function __construct(
        private readonly string $secretHash,
    ) {}

    /**
     * Verify webhook signature
     */
    public function verifySignature(Request $request): bool
    {
        $signature = $request->header('flutterwave-signature');

        if (empty($signature)) {
            Log::warning('Flutterwave webhook missing signature header', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return false;
        }

        // Flutterwave v4 uses simple hash comparison
        $isValid = hash_equals($this->secretHash, $signature);

        if (! $isValid) {
            Log::warning('Flutterwave webhook signature verification failed', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $isValid;
    }

    /**
     * Verify webhook request (throws exception if invalid)
     *
     * @throws WebhookVerificationException
     */
    public function verifyRequest(Request $request): void
    {
        if (! $this->verifySignature($request)) {
            throw WebhookVerificationException::invalidSignature();
        }

        if (! $request->has('event')) {
            throw WebhookVerificationException::missingEvent();
        }

        if (! $request->has('data')) {
            throw WebhookVerificationException::missingData();
        }
    }

    /**
     * Check if webhook event should be processed
     */
    public function shouldProcess(Request $request): bool
    {
        $eventType = $this->getEventTypeEnum($request);

        if ($eventType === null) {
            return false;
        }

        // Only process payment-related events
        return \in_array($eventType, [
            WebhookEventType::CHARGE_COMPLETED,
            WebhookEventType::CHARGE_FAILED,
            WebhookEventType::CHARGE_SUCCESSFUL,
            WebhookEventType::PAYMENT_COMPLETED,
            WebhookEventType::PAYMENT_FAILED,
            WebhookEventType::PAYMENT_SUCCESSFUL,
        ], true);
    }

    /**
     * Get event type from request
     */
    public function getEventType(Request $request): ?string
    {
        return $request->input('event');
    }

    /**
     * Get event type as an enum from request
     */
    public function getEventTypeEnum(Request $request): ?WebhookEventType
    {
        return WebhookEventType::fromString($this->getEventType($request));
    }

    /**
     * Get event data from request
     */
    public function getEventData(Request $request): ?array
    {
        return $request->input('data');
    }
}
