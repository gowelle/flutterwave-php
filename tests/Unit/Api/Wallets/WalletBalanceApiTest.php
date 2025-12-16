<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Api\Wallets\WalletBalanceApi;
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

    $this->api = new WalletBalanceApi(
        $this->headers,
        'test_access_token',
        $this->retryHandler,
        $this->rateLimiter,
    );
});

it('can get wallet balance for a currency', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Wallet balance retrieved',
            'data' => [
                'currency' => 'NGN',
                'available_balance' => 1200.09,
            ],
        ], 200),
    ]);

    $response = $this->api->getBalance('NGN');

    expect($response->status)->toBe('success');
    expect($response->data)->toBeArray();
    expect($response->data['currency'])->toBe('NGN');
    expect($response->data['available_balance'])->toBe(1200.09);
});

it('validates currency is required', function () {
    expect(function () {
        $this->api->getBalance('');
    })->toThrow(\Exception::class);
});

it('validates currency must be 3 characters', function () {
    expect(function () {
        $this->api->getBalance('NG');
    })->toThrow(\Exception::class);
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
