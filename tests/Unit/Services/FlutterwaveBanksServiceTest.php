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

it('can create virtual account', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Virtual account created',
        data: [
            'id' => 'va_123',
            'amount' => 0,
            'account_number' => '7824822527',
            'reference' => 'test_ref_123',
            'account_bank_name' => 'WEMA BANK',
            'account_type' => 'static',
            'status' => 'active',
            'account_expiration_datetime' => '2025-12-31T23:59:59Z',
            'customer_id' => 'cus_123',
            'currency' => 'NGN',
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('create')
        ->once()
        ->with(\Mockery::type('array'))
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
            ->with(FlutterwaveApi::VIRTUAL_ACCOUNT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $request = new \Gowelle\Flutterwave\Data\VirtualAccount\CreateVirtualAccountRequestDTO(
        reference: 'test_ref_123',
        customerId: 'cus_123',
        amount: 0,
        currency: \Gowelle\Flutterwave\Enums\VirtualAccountCurrency::NGN,
        accountType: \Gowelle\Flutterwave\Enums\VirtualAccountType::STATIC,
    );

    $result = $this->service->createVirtualAccount($request);

    expect($result)->toBeInstanceOf(\Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::class);
    expect($result->id)->toBe('va_123');
});

it('throws exception when creating virtual account fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to create virtual account',
        data: null,
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('create')
        ->once()
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
            ->with(FlutterwaveApi::VIRTUAL_ACCOUNT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $request = new \Gowelle\Flutterwave\Data\VirtualAccount\CreateVirtualAccountRequestDTO(
        reference: 'test_ref_123',
        customerId: 'cus_123',
        amount: 0,
        currency: \Gowelle\Flutterwave\Enums\VirtualAccountCurrency::NGN,
        accountType: \Gowelle\Flutterwave\Enums\VirtualAccountType::STATIC,
    );

    expect(fn () => $this->service->createVirtualAccount($request))
        ->toThrow(FlutterwaveApiException::class);
});

it('can retrieve virtual account', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Virtual account retrieved',
        data: [
            'id' => 'va_123',
            'amount' => 0,
            'account_number' => '7824822527',
            'reference' => 'test_ref_123',
            'account_bank_name' => 'WEMA BANK',
            'account_type' => 'static',
            'status' => 'active',
            'account_expiration_datetime' => '2025-12-31T23:59:59Z',
            'customer_id' => 'cus_123',
            'currency' => 'NGN',
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('retrieve')
        ->once()
        ->with('va_123')
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
            ->with(FlutterwaveApi::VIRTUAL_ACCOUNT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->retrieveVirtualAccount('va_123');

    expect($result)->toBeInstanceOf(\Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::class);
    expect($result->id)->toBe('va_123');
});

it('can list virtual accounts', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Virtual accounts retrieved',
        data: [
            [
                'id' => 'va_123',
                'amount' => 0,
                'account_number' => '7824822527',
                'reference' => 'test_ref_123',
                'account_bank_name' => 'WEMA BANK',
                'account_type' => 'static',
                'status' => 'active',
                'account_expiration_datetime' => '2025-12-31T23:59:59Z',
                'customer_id' => 'cus_123',
                'currency' => 'NGN',
            ],
            [
                'id' => 'va_456',
                'amount' => 100,
                'account_number' => '7824822528',
                'reference' => 'test_ref_456',
                'account_bank_name' => 'WEMA BANK',
                'account_type' => 'dynamic',
                'status' => 'active',
                'account_expiration_datetime' => '2025-12-31T23:59:59Z',
                'customer_id' => 'cus_456',
                'currency' => 'NGN',
            ],
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('list')
        ->once()
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
            ->with(FlutterwaveApi::VIRTUAL_ACCOUNT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->listVirtualAccounts();

    expect($result)->toBeArray();
    expect(\count($result))->toBe(2);
    expect($result[0])->toBeInstanceOf(\Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::class);
});

it('can list virtual accounts with params', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Virtual accounts retrieved',
        data: [
            [
                'id' => 'va_123',
                'amount' => 0,
                'account_number' => '7824822527',
                'reference' => 'test_ref_123',
                'account_bank_name' => 'WEMA BANK',
                'account_type' => 'static',
                'status' => 'active',
                'account_expiration_datetime' => '2025-12-31T23:59:59Z',
                'customer_id' => 'cus_123',
                'currency' => 'NGN',
            ],
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('listWithParams')
        ->once()
        ->with(\Mockery::type('array'))
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
            ->with(FlutterwaveApi::VIRTUAL_ACCOUNT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $params = new \Gowelle\Flutterwave\Data\VirtualAccount\ListVirtualAccountsParamsDTO(
        page: 1,
        size: 10,
        reference: 'test_ref_123',
    );

    $result = $this->service->listVirtualAccountsWithParams($params);

    expect($result)->toBeArray();
    expect(\count($result))->toBe(1);
    expect($result[0])->toBeInstanceOf(\Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::class);
});

it('can update virtual account', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Virtual account updated',
        data: [
            'id' => 'va_123',
            'amount' => 0,
            'account_number' => '7824822527',
            'reference' => 'test_ref_123',
            'account_bank_name' => 'WEMA BANK',
            'account_type' => 'static',
            'status' => 'inactive',
            'account_expiration_datetime' => '2025-12-31T23:59:59Z',
            'customer_id' => 'cus_123',
            'currency' => 'NGN',
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('update')
        ->once()
        ->with('va_123', \Mockery::type('array'))
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
            ->with(FlutterwaveApi::VIRTUAL_ACCOUNT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $request = \Gowelle\Flutterwave\Data\VirtualAccount\UpdateVirtualAccountRequestDTO::forStatusUpdate(
        \Gowelle\Flutterwave\Enums\VirtualAccountStatus::INACTIVE
    );

    $result = $this->service->updateVirtualAccount('va_123', $request);

    expect($result)->toBeInstanceOf(\Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::class);
    expect($result->status->value)->toBe('inactive');
});

it('throws exception when updating virtual account fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to update virtual account',
        data: null,
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('update')
        ->once()
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
            ->with(FlutterwaveApi::VIRTUAL_ACCOUNT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $request = \Gowelle\Flutterwave\Data\VirtualAccount\UpdateVirtualAccountRequestDTO::forStatusUpdate(
        \Gowelle\Flutterwave\Enums\VirtualAccountStatus::INACTIVE
    );

    expect(fn () => $this->service->updateVirtualAccount('va_123', $request))
        ->toThrow(FlutterwaveApiException::class);
});
