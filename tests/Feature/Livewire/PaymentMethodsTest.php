<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Livewire\PaymentMethods;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123'])
        ->assertStatus(200);
});

it('can be mounted with customer context', function () {
    Livewire::test(PaymentMethods::class, [
        'customerId' => 'cust_456',
        'currency' => 'USD',
    ])
        ->assertSet('customerId', 'cust_456')
        ->assertSet('currency', 'USD');
});

it('uses default currency', function () {
    config(['flutterwave.default_currency' => 'TZS']);

    Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123'])
        ->assertSet('currency', 'TZS');
});

it('finishes loading after mount', function () {
    // loadMethods() is called in mount(), so loading will be false after
    // (an error is set since no API is mocked)
    Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123'])
        ->assertSet('loading', false);
});

it('can select a payment method', function () {
    Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123'])
        ->set('loading', false)
        ->call('selectMethod', 'pm_456')
        ->assertSet('selectedMethodId', 'pm_456')
        ->assertSet('showAddNew', false)
        ->assertDispatched('method-selected', methodId: 'pm_456');
});

it('can toggle add new form', function () {
    Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123'])
        ->set('loading', false)
        ->set('selectedMethodId', 'pm_123')
        ->call('toggleAddNew')
        ->assertSet('showAddNew', true)
        ->assertSet('selectedMethodId', null)
        ->assertDispatched('show-add-method');
});

it('toggles add new form off when called again', function () {
    Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123'])
        ->set('loading', false)
        ->set('showAddNew', true)
        ->call('toggleAddNew')
        ->assertSet('showAddNew', false);
});

it('formats card display correctly', function () {
    $component = Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123']);

    $method = [
        'card' => [
            'last4' => '4242',
            'brand' => 'visa',
        ],
    ];

    expect($component->instance()->getCardDisplay($method))->toBe('Visa •••• 4242');
});

it('returns card icon based on brand', function () {
    $component = Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123']);

    expect($component->instance()->getCardIcon(['card' => ['brand' => 'visa']]))->toBe('visa');
    expect($component->instance()->getCardIcon(['card' => ['brand' => 'mastercard']]))->toBe('mastercard');
    expect($component->instance()->getCardIcon(['card' => ['brand' => 'verve']]))->toBe('verve');
    expect($component->instance()->getCardIcon(['card' => ['brand' => 'amex']]))->toBe('amex');
    expect($component->instance()->getCardIcon(['card' => ['brand' => 'unknown']]))->toBe('credit-card');
});

it('detects expired cards', function () {
    $component = Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123']);

    // Past year - expired
    $expiredMethod = [
        'card' => [
            'exp_year' => (string) (now()->year - 1),
            'exp_month' => '12',
        ],
    ];
    expect($component->instance()->isExpired($expiredMethod))->toBeTrue();

    // Future year - not expired
    $validMethod = [
        'card' => [
            'exp_year' => (string) (now()->year + 1),
            'exp_month' => '12',
        ],
    ];
    expect($component->instance()->isExpired($validMethod))->toBeFalse();
});

it('handles missing expiry data gracefully', function () {
    $component = Livewire::test(PaymentMethods::class, ['customerId' => 'cust_123']);

    $methodWithNoExpiry = ['card' => []];
    expect($component->instance()->isExpired($methodWithNoExpiry))->toBeFalse();
});
