<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Http\Controllers\FlutterwaveWebhookController;
use Illuminate\Support\Facades\Route;

Route::post(
    config('flutterwave.webhook.route_path', 'webhooks/flutterwave'),
    FlutterwaveWebhookController::class
)
    ->name(config('flutterwave.webhook.route_name', 'flutterwave.webhook'))
    ->middleware(config('flutterwave.webhook.middleware', ['api']));
