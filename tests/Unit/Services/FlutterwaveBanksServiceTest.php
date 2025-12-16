<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\BankAccountResolveData;
use Gowelle\Flutterwave\Data\BankBranchData;
use Gowelle\Flutterwave\Data\BankData;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApiContract;
use Gowelle\Flutterwave\Services\FlutterwaveBanksService;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Support\HeaderBuilder;

beforeEach(function () {
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->headerBuilder = \Mockery::mock(HeaderBuilder::class);
    $this->service = new FlutterwaveBanksService($this->baseService);
});

it('can get banks by country', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Banks retrieved',
        data: [
            ['id' => '1', 'code' => '044', 'name' => 'Access Bank'],
            ['id' => '2', 'code' => '050', 'name' => 'Ecobank'],
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('retrieveByCountry')
        ->once()
        ->with('NG')
        ->andReturn($response);

    $this->baseService
        ->shouldReceive('getAccessToken')
        ->once()
        ->andReturn('test_token');

    $this->baseService
        ->shouldReceive('getHeaderBuilder')
        ->once()
        ->andReturn($this->headerBuilder);

    $this->headerBuilder
        ->shouldReceive('build')
        ->once()
        ->andReturn(['Content-Type' => 'application/json']);

    app()->instance(FlutterwaveApiProvider::class, \Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(FlutterwaveApi::BANKS, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->get('NG');

    expect($result)->toBeArray();
    expect(\count($result))->toBe(2);
    expect($result[0])->toBeInstanceOf(BankData::class);
});

it('throws exception when getting banks fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to retrieve banks',
        data: null,
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('retrieveByCountry')
        ->once()
        ->with('NG')
        ->andReturn($response);

    $this->baseService
        ->shouldReceive('getAccessToken')
        ->once()
        ->andReturn('test_token');

    $this->baseService
        ->shouldReceive('getHeaderBuilder')
        ->once()
        ->andReturn($this->headerBuilder);

    $this->headerBuilder
        ->shouldReceive('build')
        ->once()
        ->andReturn(['Content-Type' => 'application/json']);

    app()->instance(FlutterwaveApiProvider::class, \Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(FlutterwaveApi::BANKS, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    expect(fn () => $this->service->get('NG'))
        ->toThrow(FlutterwaveApiException::class);
});

it('can get bank branches by bank id', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Branches retrieved',
        data: [
            ['id' => '1', 'name' => 'Main Branch'],
            ['id' => '2', 'name' => 'Lagos Branch'],
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('retrieveByBankId')
        ->once()
        ->with('bank_123')
        ->andReturn($response);

    $this->baseService
        ->shouldReceive('getAccessToken')
        ->once()
        ->andReturn('test_token');

    $this->baseService
        ->shouldReceive('getHeaderBuilder')
        ->once()
        ->andReturn($this->headerBuilder);

    $this->headerBuilder
        ->shouldReceive('build')
        ->once()
        ->andReturn(['Content-Type' => 'application/json']);

    app()->instance(FlutterwaveApiProvider::class, \Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(FlutterwaveApi::BANK_BRANCHES, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->branches('bank_123');

    expect($result)->toBeArray();
    expect(\count($result))->toBe(2);
    expect($result[0])->toBeInstanceOf(BankBranchData::class);
});

it('can resolve bank account', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Account resolved',
        data: [
            'account_number' => '0123456789',
            'account_name' => 'John Doe',
            'bank_code' => '044',
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('resolve')
        ->once()
        ->with('044', '0123456789', 'NGN')
        ->andReturn($response);

    $this->baseService
        ->shouldReceive('getAccessToken')
        ->once()
        ->andReturn('test_token');

    $this->baseService
        ->shouldReceive('getHeaderBuilder')
        ->once()
        ->andReturn($this->headerBuilder);

    $this->headerBuilder
        ->shouldReceive('build')
        ->once()
        ->andReturn(['Content-Type' => 'application/json']);

    app()->instance(FlutterwaveApiProvider::class, \Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(FlutterwaveApi::BANK_ACCOUNT_RESOLVE, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->resolveAccount('044', '0123456789', 'NGN');

    expect($result)->toBeInstanceOf(BankAccountResolveData::class);
});
