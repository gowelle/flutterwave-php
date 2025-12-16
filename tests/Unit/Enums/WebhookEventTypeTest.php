<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Enums\WebhookEventType;

it('can create enum from valid string', function () {
    $enum = WebhookEventType::fromString('charge.completed');

    expect($enum)->toBe(WebhookEventType::CHARGE_COMPLETED);
    expect($enum->value)->toBe('charge.completed');
});

it('returns null for invalid string', function () {
    $enum = WebhookEventType::fromString('invalid.event');

    expect($enum)->toBeNull();
});

it('returns null for null input', function () {
    $enum = WebhookEventType::fromString(null);

    expect($enum)->toBeNull();
});

it('identifies payment events correctly', function () {
    expect(WebhookEventType::CHARGE_COMPLETED->isPaymentEvent())->toBeTrue();
    expect(WebhookEventType::CHARGE_FAILED->isPaymentEvent())->toBeTrue();
    expect(WebhookEventType::CHARGE_SUCCESSFUL->isPaymentEvent())->toBeTrue();
    expect(WebhookEventType::PAYMENT_COMPLETED->isPaymentEvent())->toBeTrue();
    expect(WebhookEventType::PAYMENT_FAILED->isPaymentEvent())->toBeTrue();
    expect(WebhookEventType::PAYMENT_SUCCESSFUL->isPaymentEvent())->toBeTrue();
    expect(WebhookEventType::TRANSFER_COMPLETED->isPaymentEvent())->toBeFalse();
});

it('identifies transfer events correctly', function () {
    expect(WebhookEventType::TRANSFER_COMPLETED->isTransferEvent())->toBeTrue();
    expect(WebhookEventType::CHARGE_COMPLETED->isTransferEvent())->toBeFalse();
    expect(WebhookEventType::PAYMENT_COMPLETED->isTransferEvent())->toBeFalse();
});

it('identifies charge events correctly', function () {
    expect(WebhookEventType::CHARGE_COMPLETED->isChargeEvent())->toBeTrue();
    expect(WebhookEventType::CHARGE_FAILED->isChargeEvent())->toBeTrue();
    expect(WebhookEventType::CHARGE_SUCCESSFUL->isChargeEvent())->toBeTrue();
    expect(WebhookEventType::PAYMENT_COMPLETED->isChargeEvent())->toBeFalse();
    expect(WebhookEventType::TRANSFER_COMPLETED->isChargeEvent())->toBeFalse();
});

it('identifies successful events correctly', function () {
    expect(WebhookEventType::CHARGE_SUCCESSFUL->isSuccessful())->toBeTrue();
    expect(WebhookEventType::PAYMENT_SUCCESSFUL->isSuccessful())->toBeTrue();
    expect(WebhookEventType::CHARGE_COMPLETED->isSuccessful())->toBeFalse();
    expect(WebhookEventType::CHARGE_FAILED->isSuccessful())->toBeFalse();
    expect(WebhookEventType::PAYMENT_FAILED->isSuccessful())->toBeFalse();
    expect(WebhookEventType::TRANSFER_COMPLETED->isSuccessful())->toBeFalse();
});
