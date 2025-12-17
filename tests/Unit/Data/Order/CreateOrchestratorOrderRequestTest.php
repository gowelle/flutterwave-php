<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Order\CreateOrchestratorOrderRequest;

describe('CreateOrchestratorOrderRequest', function () {
    it('creates with required fields', function () {
        $customer = ['name' => 'John Doe', 'email' => 'john@example.com'];
        $paymentMethod = ['type' => 'card', 'card' => ['number' => '4111111111111111']];

        $request = new CreateOrchestratorOrderRequest(
            amount: 1500.00,
            currency: 'NGN',
            reference: 'ORD-12345',
            customer: $customer,
            paymentMethod: $paymentMethod,
        );

        expect($request)
            ->toBeInstanceOf(CreateOrchestratorOrderRequest::class)
            ->amount->toBe(1500.00)
            ->currency->toBe('NGN')
            ->reference->toBe('ORD-12345')
            ->customer->toBe($customer)
            ->paymentMethod->toBe($paymentMethod);
    });

    it('creates with optional fields', function () {
        $request = new CreateOrchestratorOrderRequest(
            amount: 2000.00,
            currency: 'USD',
            reference: 'ORD-54321',
            customer: ['email' => 'test@test.com'],
            paymentMethod: ['type' => 'bank_transfer'],
            meta: ['order_type' => 'subscription'],
            redirectUrl: 'https://example.com/callback',
            authorization: ['mode' => '3ds'],
        );

        expect($request)
            ->meta->toBe(['order_type' => 'subscription'])
            ->redirectUrl->toBe('https://example.com/callback')
            ->authorization->toBe(['mode' => '3ds']);
    });

    it('creates using static make method', function () {
        $request = CreateOrchestratorOrderRequest::make(
            amount: 500.00,
            currency: 'EUR',
            reference: 'ORD-99999',
            customer: ['name' => 'Test User'],
            paymentMethod: ['type' => 'mobile_money'],
            meta: ['key' => 'value'],
        );

        expect($request)
            ->amount->toBe(500.00)
            ->currency->toBe('EUR')
            ->customer->toBe(['name' => 'Test User'])
            ->paymentMethod->toBe(['type' => 'mobile_money'])
            ->meta->toBe(['key' => 'value']);
    });

    it('converts to API payload with required fields only', function () {
        $customer = ['email' => 'john@example.com'];
        $paymentMethod = ['type' => 'card'];

        $request = new CreateOrchestratorOrderRequest(
            amount: 1500.00,
            currency: 'NGN',
            reference: 'ORD-12345',
            customer: $customer,
            paymentMethod: $paymentMethod,
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('amount', 1500.00)
            ->toHaveKey('currency', 'NGN')
            ->toHaveKey('reference', 'ORD-12345')
            ->toHaveKey('customer', $customer)
            ->toHaveKey('payment_method', $paymentMethod)
            ->not->toHaveKey('meta')
            ->not->toHaveKey('redirect_url')
            ->not->toHaveKey('authorization');
    });

    it('converts to API payload with all fields', function () {
        $customer = ['name' => 'Full Test', 'email' => 'full@test.com', 'phone' => '+1234567890'];
        $paymentMethod = ['type' => 'card', 'card' => ['cvv' => '123']];

        $request = new CreateOrchestratorOrderRequest(
            amount: 2000.00,
            currency: 'USD',
            reference: 'ORD-FULL',
            customer: $customer,
            paymentMethod: $paymentMethod,
            meta: ['source' => 'api'],
            redirectUrl: 'https://example.com/done',
            authorization: ['pin' => '1234'],
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('amount', 2000.00)
            ->toHaveKey('currency', 'USD')
            ->toHaveKey('reference', 'ORD-FULL')
            ->toHaveKey('customer', $customer)
            ->toHaveKey('payment_method', $paymentMethod)
            ->toHaveKey('meta', ['source' => 'api'])
            ->toHaveKey('redirect_url', 'https://example.com/done')
            ->toHaveKey('authorization', ['pin' => '1234']);
    });
});
