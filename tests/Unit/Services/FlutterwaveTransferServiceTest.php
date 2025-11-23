<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Data\TransferData;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveTransferService;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

beforeEach(function () {
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->service = new FlutterwaveTransferService($this->baseService);
});

it('can create a transfer', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Transfer created',
        data: [
            'id' => 'trf_123',
            'account_bank' => '044',
            'account_number' => '0123456789',
            'amount' => 5000,
            'currency' => 'NGN',
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
        'account_bank' => '044',
        'account_number' => '0123456789',
        'amount' => 5000,
        'currency' => 'NGN',
        'reference' => 'PAYOUT-123',
        'beneficiary_name' => 'John Doe',
        'narration' => 'Monthly payout',
    ]);

    expect($result)->toBeInstanceOf(TransferData::class);
});

it('can get a transfer by id', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Transfer retrieved',
        data: [
            'id' => 'trf_123',
            'account_bank' => '044',
            'account_number' => '0123456789',
            'amount' => 5000,
            'status' => 'completed',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('retrieve')
        ->once()
        ->with(FlutterwaveApi::TRANSFER, \Mockery::any(), 'trf_123')
        ->andReturn($response);

    $result = $this->service->get('trf_123');

    expect($result)->toBeInstanceOf(TransferData::class);
});

it('can list transfers', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Transfers retrieved',
        data: [
            ['id' => 'trf_1', 'amount' => 1000],
            ['id' => 'trf_2', 'amount' => 2000],
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
    expect($result[0])->toBeInstanceOf(TransferData::class);
});

it('returns empty array when list response has no data', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'No transfers found',
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

