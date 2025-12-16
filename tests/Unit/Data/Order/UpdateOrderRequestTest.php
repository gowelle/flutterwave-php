<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Order\UpdateOrderRequest;

describe('UpdateOrderRequest', function () {
    it('creates with all fields', function () {
        $request = new UpdateOrderRequest(
            orderReference: 'ORD-UPDATED',
            amount: 2500.00,
            status: 'completed',
        );

        expect($request)
            ->toBeInstanceOf(UpdateOrderRequest::class)
            ->orderReference->toBe('ORD-UPDATED')
            ->amount->toBe(2500.00)
            ->status->toBe('completed');
    });

    it('creates with partial fields', function () {
        $request = new UpdateOrderRequest(
            status: 'cancelled',
        );

        expect($request)
            ->orderReference->toBeNull()
            ->amount->toBeNull()
            ->status->toBe('cancelled');
    });

    it('creates with no fields', function () {
        $request = new UpdateOrderRequest;

        expect($request)
            ->orderReference->toBeNull()
            ->amount->toBeNull()
            ->status->toBeNull();
    });

    it('converts to API payload with all fields', function () {
        $request = new UpdateOrderRequest(
            orderReference: 'ORD-UPDATED',
            amount: 2500.00,
            status: 'completed',
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('order_reference', 'ORD-UPDATED')
            ->toHaveKey('amount', 2500.00)
            ->toHaveKey('status', 'completed');
    });

    it('excludes null fields from API payload', function () {
        $request = new UpdateOrderRequest(
            status: 'processing',
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('status', 'processing')
            ->not->toHaveKey('order_reference')
            ->not->toHaveKey('amount');
    });

    it('returns empty payload when no fields set', function () {
        $request = new UpdateOrderRequest;

        $payload = $request->toApiPayload();

        expect($payload)->toBeEmpty();
    });
});
