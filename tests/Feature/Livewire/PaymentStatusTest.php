<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Livewire\PaymentStatus;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(PaymentStatus::class, ['chargeId' => 'dc_123'])
        ->assertStatus(200);
});

it('can be mounted with charge context', function () {
    Livewire::test(PaymentStatus::class, [
        'chargeId' => 'dc_456',
        'startPolling' => true,
        'pollInterval' => 5000,
        'maxPolls' => 30,
    ])
        ->assertSet('chargeId', 'dc_456')
        ->assertSet('pollInterval', 5000)
        ->assertSet('maxPolls', 30);
});

it('has correct default values', function () {
    Livewire::test(PaymentStatus::class, ['chargeId' => 'dc_123'])
        ->assertSet('status', 'pending')
        ->assertSet('pollInterval', 3000)
        ->assertSet('maxPolls', 60)
        ->assertSet('pollCount', 0);
});

it('increments poll count on each poll', function () {
    Livewire::test(PaymentStatus::class, ['chargeId' => 'dc_123'])
        ->set('polling', true)
        ->call('poll')
        ->assertSet('pollCount', 1)
        ->call('poll')
        ->assertSet('pollCount', 2);
});

it('stops polling when max polls reached', function () {
    Livewire::test(PaymentStatus::class, ['chargeId' => 'dc_123'])
        ->set('polling', true)
        ->set('pollCount', 59)
        ->set('maxPolls', 60)
        ->call('poll')
        ->assertSet('polling', false)
        ->assertDispatched('polling-timeout', chargeId: 'dc_123');
});

it('stops polling when terminal status reached', function () {
    Livewire::test(PaymentStatus::class, ['chargeId' => 'dc_123'])
        ->set('polling', true)
        ->set('status', 'succeeded')
        ->call('poll')
        ->assertSet('polling', false);
});

it('can start and stop polling', function () {
    Livewire::test(PaymentStatus::class, ['chargeId' => 'dc_123'])
        ->call('startPolling')
        ->assertSet('polling', true)
        ->assertSet('pollCount', 0)
        ->call('stopPolling')
        ->assertSet('polling', false);
});

it('returns correct status icon', function () {
    $component = Livewire::test(PaymentStatus::class, ['chargeId' => 'dc_123']);

    $component->set('status', 'succeeded');
    expect($component->instance()->getStatusIcon())->toBe('check-circle');

    $component->set('status', 'failed');
    expect($component->instance()->getStatusIcon())->toBe('x-circle');

    $component->set('status', 'pending');
    expect($component->instance()->getStatusIcon())->toBe('clock');
});

it('returns correct status color', function () {
    $component = Livewire::test(PaymentStatus::class, ['chargeId' => 'dc_123']);

    $component->set('status', 'succeeded');
    expect($component->instance()->getStatusColor())->toBe('text-green-500');

    $component->set('status', 'failed');
    expect($component->instance()->getStatusColor())->toBe('text-red-500');

    $component->set('status', 'pending');
    expect($component->instance()->getStatusColor())->toBe('text-yellow-500');
});
