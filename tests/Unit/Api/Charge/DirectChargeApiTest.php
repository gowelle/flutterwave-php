<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Api\Charge\DirectChargeApi;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.flutterwave.client_id' => 'test_client_id',
        'services.flutterwave.client_secret' => 'test_secret',
        'services.flutterwave.environment' => 'sandbox',
        'flutterwave.timeout' => 30,
    ]);

    $this->headers = AbstractHeadersConfig::fromArray([
        'Content-Type' => 'application/json',
        'X-Idempotency-Key' => 'test-key',
        'X-Trace-Id' => 'test-trace-id',
    ]);

    $this->retryHandler = new RetryHandler;
    $this->rateLimiter = new RateLimiter;

    $this->api = new DirectChargeApi(
        $this->headers,
        'test_access_token',
        $this->retryHandler,
        $this->rateLimiter,
    );
});

it('posts direct charge creation to the orchestration endpoint', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Charge created',
            'data' => ['id' => 'chg_123'],
        ], 201),
    ]);

    $response = $this->api->create([
        'amount' => 1000,
        'currency' => 'NGN',
        'reference' => 'ORDER-123',
        'customer' => ['email' => 'user@example.com'],
        'payment_method' => ['type' => 'card'],
        'redirect_url' => 'https://example.com/callback',
    ]);

    expect($response->status)->toBe('success');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === 'https://developersandbox-api.flutterwave.com/orchestration/direct-charges';
    });
});

it('retrieves direct charges from the charges endpoint', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Charge retrieved',
            'data' => ['id' => 'chg_123'],
        ], 200),
    ]);

    $response = $this->api->retrieve('chg_123');

    expect($response->status)->toBe('success');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://developersandbox-api.flutterwave.com/charges/chg_123';
    });
});

it('updates direct charges through the charges endpoint', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Charge updated',
            'data' => ['id' => 'chg_123'],
        ], 200),
    ]);

    $response = $this->api->update('chg_123', [
        'authorization' => [
            'type' => 'otp',
            'otp' => '123456',
        ],
    ]);

    expect($response->status)->toBe('success');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'PUT'
            && $request->url() === 'https://developersandbox-api.flutterwave.com/charges/chg_123';
    });
});

it('lists direct charges through the charges endpoint', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Charges listed',
            'data' => [],
        ], 200),
    ]);

    $response = $this->api->list();

    expect($response->status)->toBe('success');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'GET'
            && $request->url() === 'https://developersandbox-api.flutterwave.com/charges';
    });
});

it('keeps search unsupported', function () {
    expect(function () {
        $this->api->search([]);
    })->toThrow(ApiMethodNotImplementedException::class);
});
