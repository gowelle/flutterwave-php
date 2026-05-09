<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Api\Chargeback\ChargebackApi;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;

beforeEach(function () {
    $this->headers = AbstractHeadersConfig::fromArray([
        'Content-Type' => 'application/json',
        'X-Trace-Id' => 'test-trace-id',
    ]);

    $this->api = new ChargebackApi(
        $this->headers,
        'test_token',
        app(RetryHandler::class),
        app(RateLimiter::class),
    );
});

it('validates required create chargeback parameters', function () {
    expect(fn () => $this->api->create([
        'charge_id' => 'chg_123',
        'amount' => 12.34,
    ]))->toThrow(Exception::class, 'type field is required');
});

it('accepts documented create chargeback parameters', function () {
    $reflection = new ReflectionClass($this->api);
    $method = $reflection->getMethod('validateCreateData');
    $method->setAccessible(true);

    $validated = $method->invoke($this->api, [
        'charge_id' => 'chg_123',
        'amount' => 12.34,
        'stage' => 'new',
        'status' => 'pending',
        'type' => 'local',
        'uploaded_proof' => 'https://example.com/proof.pdf',
        'comment' => 'Customer claims the charge was unauthorized.',
        'provider' => 'Visa',
        'arn' => '1243453453434234534443423',
        'initiator' => 'customer',
        'expiry' => 72,
    ]);

    expect($validated)->toBe([
        'charge_id' => 'chg_123',
        'amount' => 12.34,
        'stage' => 'new',
        'status' => 'pending',
        'type' => 'local',
        'uploaded_proof' => 'https://example.com/proof.pdf',
        'comment' => 'Customer claims the charge was unauthorized.',
        'provider' => 'Visa',
        'arn' => '1243453453434234534443423',
        'initiator' => 'customer',
        'expiry' => 72,
    ]);
});

it('accepts documented update chargeback parameters', function () {
    $reflection = new ReflectionClass($this->api);
    $method = $reflection->getMethod('validateUpdateData');
    $method->setAccessible(true);

    $validated = $method->invoke($this->api, [
        'status' => 'declined',
        'uploaded_proof' => 'https://example.com/proof.pdf',
        'comment' => 'Service was securely delivered.',
        'provider' => 'Visa',
        'arn' => '1243453453434234534443423',
        'due_datetime' => '2025-05-30T23:59:59Z',
        'proof_data' => 'JVBERi0xLjIgCjkgMAojX',
    ]);

    expect($validated)->toBe([
        'status' => 'declined',
        'uploaded_proof' => 'https://example.com/proof.pdf',
        'comment' => 'Service was securely delivered.',
        'provider' => 'Visa',
        'arn' => '1243453453434234534443423',
        'due_datetime' => '2025-05-30T23:59:59Z',
        'proof_data' => 'JVBERi0xLjIgCjkgMAojX',
    ]);
});
