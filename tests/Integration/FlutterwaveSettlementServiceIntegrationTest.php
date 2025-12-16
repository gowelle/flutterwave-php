<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\SettlementData;
use Gowelle\Flutterwave\Services\FlutterwaveSettlementService;
use Gowelle\Flutterwave\Tests\Integration\IntegrationTestCase;

uses(IntegrationTestCase::class);

describe('FlutterwaveSettlementService Integration', function () {
    it('can list settlements', function () {
        /** @var FlutterwaveSettlementService $settlementService */
        $settlementService = app(FlutterwaveSettlementService::class);

        $settlements = $settlementService->list();

        expect($settlements)->toBeArray();

        if (! empty($settlements)) {
            expect($settlements[0])->toBeInstanceOf(SettlementData::class);
        }
    });

    it('can get a settlement by ID when settlements exist', function () {
        /** @var FlutterwaveSettlementService $settlementService */
        $settlementService = app(FlutterwaveSettlementService::class);

        // First list settlements to see if any exist
        $settlements = $settlementService->list();

        if (empty($settlements)) {
            $this->markTestSkipped('No settlements available to test get by ID');
        }

        $settlementId = $settlements[0]->id;
        $settlement = $settlementService->get($settlementId);

        expect($settlement)
            ->toBeInstanceOf(SettlementData::class)
            ->id->toBe($settlementId);
    });
});
