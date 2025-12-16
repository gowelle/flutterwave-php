<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Api\Wallets\WalletStatementApi;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;
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

    $this->api = new WalletStatementApi(
        $this->headers,
        'test_access_token',
        $this->retryHandler,
        $this->rateLimiter,
    );
});

it('can get wallet statement', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Wallet statement retrieved',
            'data' => [
                'cursor' => [
                    'next' => 'next_cursor',
                    'previous' => 'prev_cursor',
                    'limit' => 10,
                    'total' => 100,
                    'has_more_items' => true,
                ],
                'transactions' => [
                    [
                        'transaction_direction' => 'credit',
                        'amount' => ['value' => 1000, 'currency' => 'NGN'],
                        'balance' => ['currency' => 'NGN', 'before' => 0, 'after' => 1000],
                    ],
                ],
            ],
        ], 200),
    ]);

    $response = $this->api->getStatement(['currency' => 'NGN']);

    expect($response->status)->toBe('success');
    expect($response->data)->toBeArray();
    expect($response->data['cursor'])->toBeArray();
    expect($response->data['transactions'])->toBeArray();
});

it('validates currency is required', function () {
    expect(function () {
        $this->api->getStatement([]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('validates currency must be 3 characters', function () {
    expect(function () {
        $this->api->getStatement(['currency' => 'NG']);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('validates size must be between 10 and 50', function () {
    expect(function () {
        $this->api->getStatement(['currency' => 'NGN', 'size' => 5]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);

    expect(function () {
        $this->api->getStatement(['currency' => 'NGN', 'size' => 100]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('accepts valid query parameters', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'data' => ['cursor' => [], 'transactions' => []],
        ], 200),
    ]);

    $response = $this->api->getStatement([
        'currency' => 'NGN',
        'size' => 20,
        'from' => '2024-01-01T00:00:00Z',
        'to' => '2024-12-31T23:59:59Z',
        'next' => 'next_cursor',
        'previous' => 'prev_cursor',
    ]);

    expect($response->status)->toBe('success');
});

it('throws exception for unimplemented methods', function () {
    expect(function () {
        $this->api->create([]);
    })->toThrow(\Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException::class);

    expect(function () {
        $this->api->update('id', []);
    })->toThrow(\Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException::class);

    expect(function () {
        $this->api->retrieve('id');
    })->toThrow(\Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException::class);

    expect(function () {
        $this->api->list();
    })->toThrow(\Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException::class);

    expect(function () {
        $this->api->search([]);
    })->toThrow(\Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException::class);
});
