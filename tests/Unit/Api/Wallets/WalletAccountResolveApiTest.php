<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Api\Wallets\WalletAccountResolveApi;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

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

    $this->api = new WalletAccountResolveApi(
        $this->headers,
        'test_access_token',
        $this->retryHandler,
        $this->rateLimiter,
    );
});

it('can resolve wallet account', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Wallet account resolved',
            'data' => [
                'provider' => 'flutterwave',
                'identifier' => 'wallet_123',
                'name' => 'John Doe',
            ],
        ], 200),
    ]);

    $response = $this->api->resolve('flutterwave', 'wallet_123');

    expect($response->status)->toBe('success');
    expect($response->data)->toBeArray();
    expect($response->data['provider'])->toBe('flutterwave');
    expect($response->data['identifier'])->toBe('wallet_123');
    expect($response->data['name'])->toBe('John Doe');
});

it('validates provider is required', function () {
    expect(function () {
        $this->api->resolve('', 'wallet_123');
    })->toThrow(ValidationException::class);
});

it('validates identifier is required', function () {
    expect(function () {
        $this->api->resolve('flutterwave', '');
    })->toThrow(ValidationException::class);
});

// Removed test: provider validation has been loosened to support multiple providers
it('throws exception for unimplemented methods', function () {
    expect(function () {
        $this->api->create([]);
    })->toThrow(ApiMethodNotImplementedException::class);

    expect(function () {
        $this->api->update('id', []);
    })->toThrow(ApiMethodNotImplementedException::class);

    expect(function () {
        $this->api->retrieve('id');
    })->toThrow(ApiMethodNotImplementedException::class);

    expect(function () {
        $this->api->list();
    })->toThrow(ApiMethodNotImplementedException::class);

    expect(function () {
        $this->api->search([]);
    })->toThrow(ApiMethodNotImplementedException::class);
});
