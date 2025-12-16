<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    config(['flutterwave.secret_hash' => 'test_secret_hash']);
    config(['flutterwave.webhook.verify_signature' => true]);
    config(['flutterwave.webhook.route_path' => 'webhooks/flutterwave']);
});

it('accepts valid webhook request', function () {
    Event::fake();

    $payload = [
        'event' => 'charge.completed',
        'data' => [
            'id' => 'ch_123',
            'status' => 'successful',
            'amount' => 1000,
        ],
    ];

    $response = $this->postJson('/webhooks/flutterwave', $payload, [
        'flutterwave-signature' => 'test_secret_hash',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['status' => 'success']);

    Event::assertDispatched(FlutterwaveWebhookReceived::class, function ($event) use ($payload) {
        return $event->payload === $payload;
    });
});

it('rejects webhook request with invalid signature', function () {
    Event::fake();

    $payload = [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ];

    $response = $this->postJson('/webhooks/flutterwave', $payload, [
        'flutterwave-signature' => 'wrong_signature',
    ]);

    $response->assertStatus(500);
    Event::assertNotDispatched(FlutterwaveWebhookReceived::class);
});

it('rejects webhook request with missing signature', function () {
    Event::fake();

    $payload = [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ];

    $response = $this->postJson('/webhooks/flutterwave', $payload);

    $response->assertStatus(500);
    Event::assertNotDispatched(FlutterwaveWebhookReceived::class);
});

it('rejects webhook request with missing event', function () {
    Event::fake();

    $payload = [
        'data' => ['id' => 'ch_123'],
    ];

    $response = $this->postJson('/webhooks/flutterwave', $payload, [
        'flutterwave-signature' => 'test_secret_hash',
    ]);

    $response->assertStatus(500);
    Event::assertNotDispatched(FlutterwaveWebhookReceived::class);
});

it('rejects webhook request with missing data', function () {
    Event::fake();

    $payload = [
        'event' => 'charge.completed',
    ];

    $response = $this->postJson('/webhooks/flutterwave', $payload, [
        'flutterwave-signature' => 'test_secret_hash',
    ]);

    $response->assertStatus(500);
    Event::assertNotDispatched(FlutterwaveWebhookReceived::class);
});

it('accepts webhook when signature verification is disabled', function () {
    config(['flutterwave.webhook.verify_signature' => false]);
    Event::fake();

    $payload = [
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ];

    $response = $this->postJson('/webhooks/flutterwave', $payload);

    $response->assertStatus(200);
    Event::assertDispatched(FlutterwaveWebhookReceived::class);
});

it('dispatches webhook event with correct payload', function () {
    Event::fake();

    $payload = [
        'event' => 'charge.successful',
        'data' => [
            'id' => 'ch_456',
            'status' => 'successful',
            'amount' => 2000,
            'currency' => 'TZS',
        ],
    ];

    $this->postJson('/webhooks/flutterwave', $payload, [
        'flutterwave-signature' => 'test_secret_hash',
    ]);

    Event::assertDispatched(FlutterwaveWebhookReceived::class, function ($event) {
        return $event->getEventType() === 'charge.successful'
            && $event->getEventTypeEnum()?->value === 'charge.successful'
            && $event->getTransactionId() === 'ch_456'
            && $event->getStatus() === 'successful'
            && $event->isPaymentEvent() === true
            && $event->isSuccessful() === true;
    });
});

it('handles different webhook event types', function () {
    Event::fake();

    $events = [
        'charge.completed',
        'charge.failed',
        'charge.successful',
        'payment.completed',
    ];

    foreach ($events as $eventType) {
        $payload = [
            'event' => $eventType,
            'data' => ['id' => 'ch_123', 'status' => 'pending'],
        ];

        $this->postJson('/webhooks/flutterwave', $payload, [
            'flutterwave-signature' => 'test_secret_hash',
        ]);
    }

    Event::assertDispatched(FlutterwaveWebhookReceived::class, 4);
});

it('can get event type enum from webhook event', function () {
    $event = new FlutterwaveWebhookReceived([
        'event' => 'charge.completed',
        'data' => ['id' => 'ch_123'],
    ]);

    $enum = $event->getEventTypeEnum();

    expect($enum)->not->toBeNull();
    expect($enum->value)->toBe('charge.completed');
    expect($enum->isPaymentEvent())->toBeTrue();
    expect($enum->isChargeEvent())->toBeTrue();
});

it('returns null for invalid event type enum', function () {
    $event = new FlutterwaveWebhookReceived([
        'event' => 'invalid.event',
        'data' => ['id' => 'ch_123'],
    ]);

    $enum = $event->getEventTypeEnum();

    expect($enum)->toBeNull();
});
