<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Order\CreateOrderRequest;

describe('CreateOrderRequest', function () {
    it('creates with required fields', function () {
        $request = new CreateOrderRequest(
            orderReference: 'ORD-12345',
            amount: 1500.00,
            currency: 'NGN',
            customer: [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            items: [
                ['name' => 'Product A', 'quantity' => 2, 'amount' => 750.00],
            ],
        );

        expect($request)
            ->toBeInstanceOf(CreateOrderRequest::class)
            ->orderReference->toBe('ORD-12345')
            ->amount->toBe(1500.00)
            ->currency->toBe('NGN');
    });

    it('creates using static make method', function () {
        $request = CreateOrderRequest::make(
            orderReference: 'ORD-54321',
            amount: 2000.00,
            currency: 'USD',
            customerName: 'Jane Smith',
            customerEmail: 'jane@example.com',
            items: [
                ['name' => 'Product B', 'quantity' => 1, 'amount' => 2000.00],
            ],
            customerPhone: '+255123456789',
        );

        expect($request->customer)
            ->toHaveKey('name', 'Jane Smith')
            ->toHaveKey('email', 'jane@example.com')
            ->toHaveKey('phone_number', '+255123456789');
    });

    it('creates using make method without phone', function () {
        $request = CreateOrderRequest::make(
            orderReference: 'ORD-99999',
            amount: 500.00,
            currency: 'EUR',
            customerName: 'Test User',
            customerEmail: 'test@example.com',
            items: [
                ['name' => 'Item', 'quantity' => 1, 'amount' => 500.00],
            ],
        );

        expect($request->customer)
            ->toHaveKey('name', 'Test User')
            ->toHaveKey('email', 'test@example.com')
            ->not->toHaveKey('phone_number');
    });

    it('converts to API payload', function () {
        $request = new CreateOrderRequest(
            orderReference: 'ORD-12345',
            amount: 1500.00,
            currency: 'NGN',
            customer: [
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ],
            items: [
                ['name' => 'Product A', 'quantity' => 2, 'amount' => 750.00],
            ],
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('order_reference', 'ORD-12345')
            ->toHaveKey('amount', 1500.00)
            ->toHaveKey('currency', 'NGN')
            ->toHaveKey('customer')
            ->toHaveKey('items');

        expect($payload['customer'])
            ->toHaveKey('name', 'John Doe')
            ->toHaveKey('email', 'john@example.com');

        expect($payload['items'])->toHaveCount(1);
        expect($payload['items'][0])
            ->toHaveKey('name', 'Product A')
            ->toHaveKey('quantity', 2)
            ->toHaveKey('amount', 750.00);
    });

    it('handles multiple items', function () {
        $items = [
            ['name' => 'Product A', 'quantity' => 1, 'amount' => 100.00],
            ['name' => 'Product B', 'quantity' => 3, 'amount' => 50.00],
            ['name' => 'Product C', 'quantity' => 2, 'amount' => 75.00],
        ];

        $request = new CreateOrderRequest(
            orderReference: 'ORD-MULTI',
            amount: 400.00,
            currency: 'USD',
            customer: ['name' => 'Test', 'email' => 'test@test.com'],
            items: $items,
        );

        $payload = $request->toApiPayload();

        expect($payload['items'])->toHaveCount(3);
    });
});
