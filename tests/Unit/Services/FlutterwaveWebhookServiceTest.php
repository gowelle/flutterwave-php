<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Exceptions\WebhookVerificationException;
use Gowelle\Flutterwave\Services\FlutterwaveWebhookService;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->service = new FlutterwaveWebhookService('test_secret_hash');
});

it('verifies valid webhook signature', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ], [], [], [
        'HTTP_FLUTTERWAVE_SIGNATURE' => 'test_secret_hash',
    ]);

    $result = $this->service->verifySignature($request);

    expect($result)->toBeTrue();
});

it('rejects invalid webhook signature', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ], [], [], [
        'HTTP_FLUTTERWAVE_SIGNATURE' => 'wrong_signature',
    ]);

    $result = $this->service->verifySignature($request);

    expect($result)->toBeFalse();
});

it('rejects request with missing signature', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ]);

    $result = $this->service->verifySignature($request);

    expect($result)->toBeFalse();
});

it('verifies request with valid signature and required fields', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ], [], [], [
        'HTTP_FLUTTERWAVE_SIGNATURE' => 'test_secret_hash',
    ]);

    $this->service->verifyRequest($request);

    // Should not throw exception
    expect(true)->toBeTrue();
});

it('throws exception when signature is invalid', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ], [], [], [
        'HTTP_FLUTTERWAVE_SIGNATURE' => 'wrong_signature',
    ]);

    expect(fn () => $this->service->verifyRequest($request))
        ->toThrow(WebhookVerificationException::class);
});

it('throws exception when event is missing', function () {
    $request = Request::create('/webhook', 'POST', [
        'data' => ['id' => 'ch_123'],
    ], [], [], [
        'HTTP_FLUTTERWAVE_SIGNATURE' => 'test_secret_hash',
    ]);

    expect(fn () => $this->service->verifyRequest($request))
        ->toThrow(WebhookVerificationException::class);
});

it('throws exception when data is missing', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
    ], [], [], [
        'HTTP_FLUTTERWAVE_SIGNATURE' => 'test_secret_hash',
    ]);

    expect(fn () => $this->service->verifyRequest($request))
        ->toThrow(WebhookVerificationException::class);
});

it('can check if event should be processed', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ]);

    expect($this->service->shouldProcess($request))->toBeTrue();

    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.failed',
        'data' => ['id' => 'ch_123'],
    ]);

    expect($this->service->shouldProcess($request))->toBeTrue();

    $request = Request::create('/webhook', 'POST', [
        'event' => 'other.event',
        'data' => ['id' => 'ch_123'],
    ]);

    expect($this->service->shouldProcess($request))->toBeFalse();
});

it('can get event type from request', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ]);

    expect($this->service->getEventType($request))->toBe('charge.completed');
});

it('can get event type enum from request', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ]);

    $enum = $this->service->getEventTypeEnum($request);

    expect($enum)->not->toBeNull();
    expect($enum->value)->toBe('charge.completed');
});

it('returns null for invalid event type enum', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'invalid.event',
        'data' => ['id' => 'ch_123'],
    ]);

    $enum = $this->service->getEventTypeEnum($request);

    expect($enum)->toBeNull();
});

it('can get event data from request', function () {
    $request = Request::create('/webhook', 'POST', [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123', 'amount' => 1000],
    ]);

    $data = $this->service->getEventData($request);

    expect($data)->toBeArray();
    expect($data['id'])->toBe('ch_123');
    expect($data['amount'])->toBe(1000);
});

