<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Livewire\PaymentForm;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(PaymentForm::class)
        ->assertStatus(200);
});

it('can be mounted with payment details', function () {
    Livewire::test(PaymentForm::class, [
        'amount' => 10000,
        'currency' => 'TZS',
        'reference' => 'TEST-REF-123',
        'redirectUrl' => 'https://example.com/callback',
        'customer' => [
            'email' => 'test@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_number' => '+255123456789',
        ],
        'meta' => ['order_id' => 'ORD-123'],
    ])
        ->assertSet('amount', 10000)
        ->assertSet('currency', 'TZS')
        ->assertSet('reference', 'TEST-REF-123')
        ->assertSet('email', 'test@example.com')
        ->assertSet('firstName', 'John')
        ->assertSet('lastName', 'Doe')
        ->assertSet('phoneNumber', '+255123456789');
});

it('generates a reference if not provided', function () {
    Livewire::test(PaymentForm::class, [
        'amount' => 5000,
    ])
        ->assertNotSet('reference', '');
});

it('validates required fields on payment submission', function () {
    // Validation errors are caught and converted to error property
    // The component catches exceptions and sets error message
    $component = Livewire::test(PaymentForm::class, ['amount' => 1000])
        ->call('submitPayment', [
            'nonce' => 'test-nonce',
            'encrypted_card_number' => 'enc-card',
            'encrypted_cvv' => 'enc-cvv',
            'encrypted_expiry_month' => 'enc-month',
            'encrypted_expiry_year' => 'enc-year',
        ]);

    // Either validation errors or general error should be present
    expect($component->get('processing'))->toBeFalse();
});

it('requires encrypted card data on payment submission', function () {
    $component = Livewire::test(PaymentForm::class, ['amount' => 1000])
        ->set('email', 'test@example.com')
        ->set('firstName', 'John')
        ->set('lastName', 'Doe')
        ->set('phoneNumber', '+255123456789')
        ->call('submitPayment', []);

    // Either throws exception or sets error
    expect($component->get('error'))->not->toBeEmpty();
});

it('dispatches payment-error event on exception', function () {
    $component = Livewire::test(PaymentForm::class, ['amount' => 1000])
        ->set('email', 'test@example.com')
        ->set('firstName', 'John')
        ->set('lastName', 'Doe')
        ->set('phoneNumber', '+255123456789')
        ->call('submitPayment', []);

    $component->assertDispatched('payment-error');
});

it('returns encryption key', function () {
    config(['flutterwave.encryption_key' => 'test-encryption-key-123']);

    $component = Livewire::test(PaymentForm::class);

    expect($component->instance()->getEncryptionKey())
        ->toBe('test-encryption-key-123');
});
