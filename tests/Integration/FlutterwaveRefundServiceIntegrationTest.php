<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\RefundData;
use Gowelle\Flutterwave\Services\FlutterwaveRefundService;
use Gowelle\Flutterwave\Tests\Integration\IntegrationTestCase;

uses(IntegrationTestCase::class);

describe('FlutterwaveRefundService Integration', function () {
    it('can list refunds', function () {
        /** @var FlutterwaveRefundService $refundService */
        $refundService = app(FlutterwaveRefundService::class);

        $refunds = $refundService->list();

        expect($refunds)->toBeArray();

        if (! empty($refunds)) {
            expect($refunds[0])->toBeInstanceOf(RefundData::class);
        }
    });

    it('can get a refund by ID when refunds exist', function () {
        /** @var FlutterwaveRefundService $refundService */
        $refundService = app(FlutterwaveRefundService::class);

        // First list refunds to see if any exist
        $refunds = $refundService->list();

        if (empty($refunds)) {
            $this->markTestSkipped('No refunds available to test get by ID');
        }

        $refundId = $refunds[0]->id;
        $refund = $refundService->get($refundId);

        expect($refund)
            ->toBeInstanceOf(RefundData::class)
            ->id->toBe($refundId);
    });
});
