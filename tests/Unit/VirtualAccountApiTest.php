<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Api\VirtualAccount\VirtualAccountApi;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config([
        'services.flutterwave.client_id' => 'test_client_id',
        'services.flutterwave.client_secret' => 'test_secret',
        'services.flutterwave.environment' => 'sandbox',
    ]);

    $this->headers = AbstractHeadersConfig::fromArray([
        'Content-Type' => 'application/json',
        'X-Idempotency-Key' => 'test-key',
        'X-Trace-Id' => 'test-trace-id',
    ]);

    $this->retryHandler = new RetryHandler();
    $this->rateLimiter = new RateLimiter();

    $this->api = new VirtualAccountApi(
        $this->headers,
        'test_access_token',
        $this->retryHandler,
        $this->rateLimiter,
    );
});

it('can list virtual accounts', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'message' => 'Virtual accounts fetched',
            'meta' => [
                'page_info' => [
                    'total' => 1,
                    'current_page' => 1,
                    'total_pages' => 1,
                ],
            ],
            'data' => [
                [
                    'id' => 'va_123',
                    'amount' => 0,
                    'account_number' => '7824822527',
                    'reference' => 'ref_123',
                    'account_bank_name' => 'WEMA BANK',
                    'account_type' => 'static',
                    'status' => 'active',
                    'account_expiration_datetime' => '2025-12-31T23:59:59Z',
                    'customer_id' => 'cus_123',
                    'currency' => 'NGN',
                    'created_datetime' => '2024-12-16T10:00:00Z',
                ],
            ],
        ]),
    ]);

    $response = $this->api->list();

    expect($response->status)->toBe('success');
    expect($response->data)->toBeArray();
});

it('validates create request reference length', function () {
    expect(function () {
        $this->api->create([
            'reference' => 'short',
            'customer_id' => 'cus_123',
            'amount' => 0,
            'currency' => 'NGN',
            'account_type' => 'static',
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('validates required fields for create', function () {
    expect(function () {
        $this->api->create([
            'reference' => 'test_ref_123',
            // Missing customer_id, amount, currency, account_type
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('requires customer_account_number for EGP currency', function () {
    expect(function () {
        $this->api->create([
            'reference' => 'test_ref_123',
            'customer_id' => 'cus_123',
            'amount' => 100,
            'currency' => 'EGP',
            'account_type' => 'static',
            // Missing customer_account_number
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('requires customer_account_number for KES currency', function () {
    expect(function () {
        $this->api->create([
            'reference' => 'test_ref_123',
            'customer_id' => 'cus_123',
            'amount' => 100,
            'currency' => 'KES',
            'account_type' => 'dynamic',
            // Missing customer_account_number
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('validates update request action_type', function () {
    expect(function () {
        $this->api->update('va_123', [
            'action_type' => 'invalid_action',
            'bvn' => '12345678901',
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('requires bvn for update_bvn action', function () {
    expect(function () {
        $this->api->update('va_123', [
            'action_type' => 'update_bvn',
            // Missing bvn
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('requires status for update_status action', function () {
    expect(function () {
        $this->api->update('va_123', [
            'action_type' => 'update_status',
            // Missing status
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('can validate create with optional fields', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'data' => [
                'id' => 'va_123',
                'amount' => 0,
                'account_number' => '7824822527',
                'reference' => 'test_ref_123',
                'account_bank_name' => 'WEMA BANK',
                'account_type' => 'static',
                'status' => 'active',
                'account_expiration_datetime' => '2025-12-31T23:59:59Z',
                'customer_id' => 'cus_123',
                'currency' => 'NGN',
                'created_datetime' => '2024-12-16T10:00:00Z',
            ],
        ]),
    ]);

    $response = $this->api->create([
        'reference' => 'test_ref_123',
        'customer_id' => 'cus_123',
        'amount' => 0,
        'currency' => 'NGN',
        'account_type' => 'static',
        'narration' => 'Payment for Order #123',
        'meta' => ['order_id' => '123'],
        'bvn' => '12345678901',
    ]);

    expect($response->status)->toBe('success');
});

it('throws exception for search operation', function () {
    expect(function () {
        $this->api->search([]);
    })->toThrow(\Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException::class);
});

it('validates expiry seconds range', function () {
    expect(function () {
        $this->api->create([
            'reference' => 'test_ref_123',
            'customer_id' => 'cus_123',
            'amount' => 100,
            'currency' => 'NGN',
            'account_type' => 'dynamic',
            'expiry' => 30, // Too low, minimum is 60
        ]);
    })->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('accepts valid currency enum values', function () {
    Http::fake([
        '*' => Http::response([
            'status' => 'success',
            'data' => [
                'id' => 'va_123',
                'amount' => 100,
                'account_number' => '7824822527',
                'reference' => 'test_ref_123',
                'account_bank_name' => 'WEMA BANK',
                'account_type' => 'dynamic',
                'status' => 'active',
                'account_expiration_datetime' => '2025-12-31T23:59:59Z',
                'customer_id' => 'cus_123',
                'currency' => 'GHS',
                'created_datetime' => '2024-12-16T10:00:00Z',
            ],
        ]),
    ]);

    $response = $this->api->create([
        'reference' => 'test_ref_ghs',
        'customer_id' => 'cus_456',
        'amount' => 100,
        'currency' => 'GHS',
        'account_type' => 'dynamic',
        'expiry' => 3600,
    ]);

    expect($response->status)->toBe('success');
});

