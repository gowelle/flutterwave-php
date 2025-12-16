<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Services\FlutterwavePaymentsService;
use Gowelle\Flutterwave\Tests\Integration\IntegrationTestCase;

uses(IntegrationTestCase::class);

describe('FlutterwavePaymentsService Integration', function () {
    it('can list payment methods', function () {
        /** @var FlutterwavePaymentsService $paymentsService */
        $paymentsService = app(FlutterwavePaymentsService::class);

        $methods = $paymentsService->methods([]);

        expect($methods)->toBeArray();
        // Note: May be empty if no payment methods have been created
    });

    it('can create and retrieve a card payment method', function () {
        $this->markTestSkipped(
            'Creating card payment methods requires encrypted card data (AES-256-GCM). '.
            'See https://developer.flutterwave.com/docs/encryption for details. '.
            'Integration tests for card payment methods require implementing encryption helpers.'
        );
    })->skip();
});
