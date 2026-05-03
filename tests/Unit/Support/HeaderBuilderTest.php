<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Objects\Wavable;
use Gowelle\Flutterwave\Support\HeaderBuilder;

it('adds x-scenario-key header in staging when wavable has scenario key', function () {
    $config = new FlutterwaveConfig(
        clientId: 'test_client_id',
        clientSecret: 'test_client_secret',
        secretHash: 'test_secret_hash',
        environment: FlutterwaveEnvironment::STAGING,
    );

    $builder = new HeaderBuilder($config);
    $headers = $builder->build(new Wavable('idem-123', 'trace-123', 'scenario:manual_review'));

    expect($headers['X-Scenario-Key'])->toBe('scenario:manual_review');
});

it('omits x-scenario-key header in production even when wavable has scenario key', function () {
    $config = new FlutterwaveConfig(
        clientId: 'test_client_id',
        clientSecret: 'test_client_secret',
        secretHash: 'test_secret_hash',
        environment: FlutterwaveEnvironment::PRODUCTION,
    );

    $builder = new HeaderBuilder($config);
    $headers = $builder->build(new Wavable('idem-123', 'trace-123', 'scenario:manual_review'));

    expect($headers)->not->toHaveKey('X-Scenario-Key');
});
