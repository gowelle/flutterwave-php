<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Tests\Integration\IntegrationTestCase;

uses(IntegrationTestCase::class);

describe('FlutterwaveDirectChargeService Integration', function () {
    it('can create a direct charge with test card', function () {
        $this->markTestSkipped(
            'Card charges require encrypted card data (AES-256-GCM). '.
            'See https://developer.flutterwave.com/docs/encryption for details. '.
            'Integration tests for card payments require implementing encryption helpers.'
        );
    })->skip();

    it('can retrieve charge status', function () {
        $this->markTestSkipped(
            'Card charges require encrypted card data (AES-256-GCM). '.
            'See https://developer.flutterwave.com/docs/encryption for details. '.
            'Integration tests for card payments require implementing encryption helpers.'
        );
    })->skip();

    it('handles mobile money charge creation', function () {
        $this->markTestSkipped(
            'Mobile money charges require proper network configuration and testing setup. '.
            'The staging API may not support all mobile money networks. '.
            'Manual testing recommended for mobile money integrations.'
        );
    })->skip();
});
