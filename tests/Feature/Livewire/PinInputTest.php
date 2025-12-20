<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Livewire\PinInput;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(PinInput::class, ['chargeId' => 'dc_123'])
        ->assertStatus(200);
});

it('can be mounted with charge context', function () {
    Livewire::test(PinInput::class, [
        'chargeId' => 'dc_456',
        'pinLength' => 6,
    ])
        ->assertSet('chargeId', 'dc_456')
        ->assertSet('pinLength', 6)
        ->assertSet('processing', false)
        ->assertSet('error', '');
});

it('uses default pin length of 4', function () {
    Livewire::test(PinInput::class, ['chargeId' => 'dc_123'])
        ->assertSet('pinLength', 4);
});

it('requires encrypted pin data on submission', function () {
    Livewire::test(PinInput::class, ['chargeId' => 'dc_123'])
        ->call('submitPin', [])
        ->assertSet('error', 'PIN encryption failed. Please try again.')
        ->assertSet('processing', false);
});

it('dispatches submit-pin event with valid encrypted data', function () {
    Livewire::test(PinInput::class, ['chargeId' => 'dc_123'])
        ->call('submitPin', [
            'encrypted_pin' => 'encrypted-pin-value',
            'nonce' => 'test-nonce-123',
        ])
        ->assertDispatched('submit-pin', encryptedPin: 'encrypted-pin-value', nonce: 'test-nonce-123');
});

it('dispatches pin-cancelled event on cancel', function () {
    Livewire::test(PinInput::class, ['chargeId' => 'dc_789'])
        ->call('cancel')
        ->assertDispatched('pin-cancelled', chargeId: 'dc_789');
});

it('returns encryption key', function () {
    config(['flutterwave.encryption_key' => 'pin-test-key']);

    Livewire::test(PinInput::class, ['chargeId' => 'dc_123'])
        ->assertOk();
});
