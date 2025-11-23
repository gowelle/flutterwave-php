<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;
use Gowelle\Flutterwave\Services\FlutterwaveWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post(
    config('flutterwave.webhook.route_path', 'webhooks/flutterwave'),
    function (Request $request, FlutterwaveWebhookService $webhookService) {
        // Verify signature if enabled
        if (config('flutterwave.webhook.verify_signature', true)) {
            $webhookService->verifyRequest($request);
        }

        // Extract payload from request
        $payload = $request->all();

        // Dispatch webhook event
        event(new FlutterwaveWebhookReceived($payload));

        // Return success response
        return response()->json(['status' => 'success'], 200);
    }
)
    ->name(config('flutterwave.webhook.route_name', 'flutterwave.webhook'))
    ->middleware(config('flutterwave.webhook.middleware', ['api']));

