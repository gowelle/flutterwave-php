<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Order\ListOrdersRequest;
use Gowelle\Flutterwave\Data\Order\OrderStatus;

describe('ListOrdersRequest', function () {
    it('creates with defaults', function () {
        $request = new ListOrdersRequest;

        expect($request)
            ->toBeInstanceOf(ListOrdersRequest::class)
            ->status->toBeNull()
            ->from->toBeNull()
            ->to->toBeNull()
            ->customerId->toBeNull()
            ->paymentMethodId->toBeNull()
            ->page->toBe(1)
            ->size->toBe(10);
    });

    it('creates with all filters', function () {
        $from = new DateTimeImmutable('2024-01-01');
        $to = new DateTimeImmutable('2024-12-31');

        $request = new ListOrdersRequest(
            status: OrderStatus::Completed,
            from: $from,
            to: $to,
            customerId: 'cust_123',
            paymentMethodId: 'pm_456',
            page: 3,
            size: 25,
        );

        expect($request)
            ->status->toBe(OrderStatus::Completed)
            ->from->toBe($from)
            ->to->toBe($to)
            ->customerId->toBe('cust_123')
            ->paymentMethodId->toBe('pm_456')
            ->page->toBe(3)
            ->size->toBe(25);
    });

    it('converts to query params with defaults only', function () {
        $request = new ListOrdersRequest;

        $params = $request->toQueryParams();

        expect($params)
            ->toHaveKey('page', 1)
            ->toHaveKey('size', 10)
            ->not->toHaveKey('status')
            ->not->toHaveKey('from')
            ->not->toHaveKey('to')
            ->not->toHaveKey('customer_id')
            ->not->toHaveKey('payment_method_id');
    });

    it('converts to query params with all filters', function () {
        $from = new DateTimeImmutable('2024-01-01T00:00:00+00:00');
        $to = new DateTimeImmutable('2024-12-31T23:59:59+00:00');

        $request = new ListOrdersRequest(
            status: OrderStatus::Pending,
            from: $from,
            to: $to,
            customerId: 'cust_abc',
            paymentMethodId: 'pm_xyz',
            page: 2,
            size: 20,
        );

        $params = $request->toQueryParams();

        expect($params)
            ->toHaveKey('status', 'pending')
            ->toHaveKey('from', '2024-01-01T00:00:00+00:00')
            ->toHaveKey('to', '2024-12-31T23:59:59+00:00')
            ->toHaveKey('customer_id', 'cust_abc')
            ->toHaveKey('payment_method_id', 'pm_xyz')
            ->toHaveKey('page', 2)
            ->toHaveKey('size', 20);
    });

    it('enforces minimum page of 1', function () {
        $request = new ListOrdersRequest(page: 0);

        $params = $request->toQueryParams();

        expect($params['page'])->toBe(1);
    });

    it('enforces size bounds 10-50', function () {
        $requestSmall = new ListOrdersRequest(size: 5);
        $requestLarge = new ListOrdersRequest(size: 100);

        expect($requestSmall->toQueryParams()['size'])->toBe(10);
        expect($requestLarge->toQueryParams()['size'])->toBe(50);
    });

    it('handles all order statuses', function () {
        $statuses = [
            [OrderStatus::Completed, 'completed'],
            [OrderStatus::Pending, 'pending'],
            [OrderStatus::Authorized, 'authorized'],
            [OrderStatus::PartiallyCompleted, 'partially-completed'],
            [OrderStatus::Voided, 'voided'],
            [OrderStatus::Failed, 'failed'],
        ];

        foreach ($statuses as [$status, $expected]) {
            $request = new ListOrdersRequest(status: $status);
            $params = $request->toQueryParams();

            expect($params['status'])->toBe($expected);
        }
    });
});
