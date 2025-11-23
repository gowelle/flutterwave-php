<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Services\FlutterwaveAuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
});

it('retrieves and caches access token', function () {
    Http::fake([
        'idp.flutterwave.com/*' => Http::response([
            'access_token' => 'test_token_12345',
            'expires_in' => 3600,
        ]),
    ]);

    $authService = app(FlutterwaveAuthService::class);
    $token = $authService->getAccessToken();

    expect($token)->toBe('test_token_12345');
    expect(Cache::has('flutterwave_access_token'))->toBeTrue();
});

it('reuses cached token if still valid', function () {
    Http::fake([
        'idp.flutterwave.com/*' => Http::response([
            'access_token' => 'test_token_12345',
            'expires_in' => 3600,
        ]),
    ]);

    $authService = app(FlutterwaveAuthService::class);

    // First call
    $token1 = $authService->getAccessToken();

    // Second call should not hit API
    $token2 = $authService->getAccessToken();

    expect($token1)->toBe($token2);

    Http::assertSentCount(1);
});
