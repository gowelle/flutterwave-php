<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Api\Wallets\WalletBalancesApi;
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

    $this->api = new WalletBalancesApi(
        $this->headers,
        'test_access_token',
        $this->retryHandler,
        $this->rateLimiter,
    );
});

it('can get wallet balances for multiple currencies', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Wallet balances retrieved',
            'data' => [
                [
                    'currency' => 'NGN',
                    'available_balance' => 1200.09,
                ],
                [
                    'currency' => 'USD',
                    'available_balance' => 3.29,
                ],
            ],
        ], 200),
    ]);

    $response = $this->api->getBalances();

    expect($response->status)->toBe('success');
    expect($response->data)->toBeArray();
    expect(\count($response->data))->toBe(2);
    expect($response->data[0]['currency'])->toBe('NGN');
    expect($response->data[1]['currency'])->toBe('USD');
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
