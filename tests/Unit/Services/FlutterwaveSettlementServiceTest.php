<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Data\SettlementData;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveSettlementService;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

beforeEach(function () {
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->service = new FlutterwaveSettlementService($this->baseService);
});

it('can get a settlement by id', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Settlement retrieved',
        data: [
            'id' => 'set_123',
            'amount' => 10000,
            'currency' => 'TZS',
            'status' => 'completed',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('retrieve')
        ->once()
        ->with(FlutterwaveApi::SETTLEMENT, \Mockery::any(), 'set_123')
        ->andReturn($response);

    $result = $this->service->get('set_123');

    expect($result)->toBeInstanceOf(SettlementData::class);
});

it('can list settlements', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Settlements retrieved',
        data: [
            ['id' => 'set_1', 'amount' => 5000],
            ['id' => 'set_2', 'amount' => 10000],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('list')
        ->once()
        ->andReturn($response);

    $result = $this->service->list();

    expect($result)->toBeArray();
    expect(count($result))->toBe(2);
    expect($result[0])->toBeInstanceOf(SettlementData::class);
});

it('returns empty array when list response has no data', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'No settlements found',
        data: null,
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('list')
        ->once()
        ->andReturn($response);

    $result = $this->service->list();

    expect($result)->toBeArray();
    expect($result)->toBeEmpty();
});

