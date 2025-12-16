<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\BankData;
use Gowelle\Flutterwave\Services\FlutterwaveBanksService;
use Gowelle\Flutterwave\Tests\Integration\IntegrationTestCase;

uses(IntegrationTestCase::class);

describe('FlutterwaveBanksService Integration', function () {
    it('can retrieve banks for Tanzania', function () {
        // Tanzania banks not supported yet - skip this test
        $this->markTestSkipped('Tanzania banks not supported in Flutterwave staging yet');
    });

    it('can retrieve banks for Nigeria', function () {
        /** @var FlutterwaveBanksService $banksService */
        $banksService = app(FlutterwaveBanksService::class);

        $banks = $banksService->get('NG');

        expect($banks)->toBeArray();
        if (! empty($banks)) {
            expect($banks)->each->toBeInstanceOf(BankData::class);
        }
    });

    it('can retrieve banks for Kenya', function () {
        /** @var FlutterwaveBanksService $banksService */
        $banksService = app(FlutterwaveBanksService::class);

        $banks = $banksService->get('KE');

        expect($banks)->toBeArray();
        if (! empty($banks)) {
            expect($banks)->each->toBeInstanceOf(BankData::class);
        }
    });

    it('can retrieve banks for Ghana', function () {
        /** @var FlutterwaveBanksService $banksService */
        $banksService = app(FlutterwaveBanksService::class);

        $banks = $banksService->get('GH');

        expect($banks)->toBeArray();
        if (! empty($banks)) {
            expect($banks)->each->toBeInstanceOf(BankData::class);
        }
    });

    it('can retrieve bank branches for a valid bank', function () {
        /** @var FlutterwaveBanksService $banksService */
        $banksService = app(FlutterwaveBanksService::class);

        // First get a bank to get its ID
        $banks = $banksService->get('NG');

        if (empty($banks)) {
            $this->markTestSkipped('No banks returned from API');
        }

        $bankId = $banks[0]->id;

        if ($bankId === null) {
            $this->markTestSkipped('Bank ID not available');
        }

        $branches = $banksService->branches($bankId);

        expect($branches)->toBeArray();
        // Note: Some banks may not have branches, so we don't assert not empty
    });
});
