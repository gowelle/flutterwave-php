<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Http\Controllers;

use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;
use Gowelle\Flutterwave\Exceptions\WebhookVerificationException;
use Gowelle\Flutterwave\Services\FlutterwaveWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles incoming Flutterwave webhook requests.
 */
final class FlutterwaveWebhookController
{
    public function __construct(
        private readonly FlutterwaveWebhookService $webhookService
    ) {}

    /**
     * Handle the incoming webhook request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Verify signature if enabled
        if (config('flutterwave.webhook.verify_signature', true)) {
            try {
                $this->webhookService->verifyRequest($request);
            } catch (WebhookVerificationException $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Extract payload from request
        $payload = $request->all();

        // Dispatch webhook event
        event(new FlutterwaveWebhookReceived($payload));

        // Return success response
        return response()->json(['status' => 'success'], 200);
    }
}
