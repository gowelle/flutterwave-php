<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Order\OrderAction;
use Gowelle\Flutterwave\Data\Order\UpdateOrderRequest;

describe('UpdateOrderRequest', function () {
    it('creates with meta only', function () {
        $request = new UpdateOrderRequest(
            meta: ['key' => 'value'],
        );

        expect($request)
            ->toBeInstanceOf(UpdateOrderRequest::class)
            ->meta->toBe(['key' => 'value'])
            ->action->toBeNull();
    });

    it('creates with action only', function () {
        $request = new UpdateOrderRequest(
            action: OrderAction::Void,
        );

        expect($request)
            ->meta->toBeNull()
            ->action->toBe(OrderAction::Void);
    });

    it('creates with both meta and action', function () {
        $request = new UpdateOrderRequest(
            meta: ['reason' => 'customer request'],
            action: OrderAction::Capture,
        );

        expect($request)
            ->meta->toBe(['reason' => 'customer request'])
            ->action->toBe(OrderAction::Capture);
    });

    it('creates with no fields', function () {
        $request = new UpdateOrderRequest;

        expect($request)
            ->meta->toBeNull()
            ->action->toBeNull();
    });

    it('creates void request using static method', function () {
        $request = UpdateOrderRequest::void(['reason' => 'cancelled']);

        expect($request)
            ->action->toBe(OrderAction::Void)
            ->meta->toBe(['reason' => 'cancelled']);
    });

    it('creates capture request using static method', function () {
        $request = UpdateOrderRequest::capture();

        expect($request)
            ->action->toBe(OrderAction::Capture)
            ->meta->toBeNull();
    });

    it('creates meta-only request using static method', function () {
        $request = UpdateOrderRequest::withMeta(['notes' => 'updated']);

        expect($request)
            ->meta->toBe(['notes' => 'updated'])
            ->action->toBeNull();
    });

    it('converts to API payload with all fields', function () {
        $request = new UpdateOrderRequest(
            meta: ['key' => 'value'],
            action: OrderAction::Capture,
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('meta', ['key' => 'value'])
            ->toHaveKey('action', 'capture');
    });

    it('excludes null fields from API payload', function () {
        $request = new UpdateOrderRequest(
            action: OrderAction::Void,
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('action', 'void')
            ->not->toHaveKey('meta');
    });

    it('returns empty payload when no fields set', function () {
        $request = new UpdateOrderRequest;

        $payload = $request->toApiPayload();

        expect($payload)->toBeEmpty();
    });
});
