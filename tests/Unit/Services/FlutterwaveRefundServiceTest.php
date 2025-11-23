<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Data\RefundData;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveRefundService;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

beforeEach(function () {
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->service = new FlutterwaveRefundService($this->baseService);
});

it('can create a refund', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Refund created',
        data: [
            'id' => 'ref_123',
            'charge_id' => 'ch_123',
            'amount' => 500,
            'status' => 'pending',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->andReturn($response);

    $result = $this->service->create([
        'charge_id' => 'ch_123',
        'amount' => 500,
        'reason' => 'Customer requested refund',
    ]);

    expect($result)->toBeInstanceOf(RefundData::class);
});

it('can get a refund by id', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Refund retrieved',
        data: [
            'id' => 'ref_123',
            'charge_id' => 'ch_123',
            'amount' => 500,
            'status' => 'completed',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('retrieve')
        ->once()
        ->with(FlutterwaveApi::REFUND, \Mockery::any(), 'ref_123')
        ->andReturn($response);

    $result = $this->service->get('ref_123');

    expect($result)->toBeInstanceOf(RefundData::class);
});

it('can list refunds', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Refunds retrieved',
        data: [
            ['id' => 'ref_1', 'charge_id' => 'ch_1', 'amount' => 100],
            ['id' => 'ref_2', 'charge_id' => 'ch_2', 'amount' => 200],
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
    expect($result[0])->toBeInstanceOf(RefundData::class);
});

it('returns empty array when list response has no data', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'No refunds found',
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

