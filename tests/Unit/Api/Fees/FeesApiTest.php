<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Api\Fees\FeesApi;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;

beforeEach(function () {
    $this->headers = AbstractHeadersConfig::fromArray([
        'Content-Type' => 'application/json',
        'X-Trace-Id' => 'test-trace-id',
    ]);

    $this->api = new FeesApi(
        $this->headers,
        'test_token',
        app(RetryHandler::class),
        app(RateLimiter::class),
    );
});

it('validates required fees query parameters', function () {
    expect(fn () => $this->api->getFees([
        'amount' => 5000,
        'currency' => 'TZS',
    ]))->toThrow(Exception::class, 'payment method field is required');
});

it('accepts documented optional fees query parameters', function () {
    $reflection = new ReflectionClass($this->api);
    $method = $reflection->getMethod('validateParams');
    $method->setAccessible(true);

    $validated = $method->invoke($this->api, [
        'amount' => 5000,
        'currency' => 'TZS',
        'payment_method' => 'mobile_money',
        'country' => 'TZ',
        'network' => 'M-PESA',
        'card6' => '123456',
    ]);

    expect($validated)->toBe([
        'amount' => 5000,
        'currency' => 'TZS',
        'payment_method' => 'mobile_money',
        'card6' => '123456',
        'country' => 'TZ',
        'network' => 'M-PESA',
    ]);
});
