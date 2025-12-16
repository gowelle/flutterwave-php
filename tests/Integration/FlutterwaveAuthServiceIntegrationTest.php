<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Services\FlutterwaveAuthService;
use Gowelle\Flutterwave\Tests\Integration\IntegrationTestCase;
use Illuminate\Support\Facades\Cache;

uses(IntegrationTestCase::class);

describe('FlutterwaveAuthService Integration', function () {
    it('can obtain an access token from staging', function () {
        /** @var FlutterwaveAuthService $authService */
        $authService = app(FlutterwaveAuthService::class);

        // Clear any cached token first
        $authService->clearTokenCache();

        $token = $authService->getAccessToken();

        expect($token)
            ->toBeString()
            ->not->toBeEmpty();
    });

    it('caches the access token', function () {
        /** @var FlutterwaveAuthService $authService */
        $authService = app(FlutterwaveAuthService::class);

        // Clear cache
        $authService->clearTokenCache();

        // First call should fetch new token
        $token1 = $authService->getAccessToken();

        // Second call should return cached token
        $token2 = $authService->getAccessToken();

        expect($token1)->toBe($token2);
    });

    it('can clear the token cache', function () {
        /** @var FlutterwaveAuthService $authService */
        $authService = app(FlutterwaveAuthService::class);

        // Ensure we have a cached token
        $authService->getAccessToken();

        // Clear the cache
        $authService->clearTokenCache();

        // The cache key should no longer exist
        expect(Cache::has('flutterwave_access_token'))->toBeFalse();
    });
});
