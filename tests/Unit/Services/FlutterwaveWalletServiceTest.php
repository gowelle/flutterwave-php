<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Wallet\WalletAccountResolveData;
use Gowelle\Flutterwave\Data\Wallet\WalletBalanceData;
use Gowelle\Flutterwave\Data\Wallet\WalletStatementData;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApiContract;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveWalletService;
use Gowelle\Flutterwave\Support\HeaderBuilder;

beforeEach(function () {
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->headerBuilder = \Mockery::mock(HeaderBuilder::class);
    $this->service = new FlutterwaveWalletService($this->baseService);
});

it('can resolve wallet account', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Wallet account resolved',
        data: [
            'provider' => 'flutterwave',
            'identifier' => 'wallet_123',
            'name' => 'John Doe',
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('resolve')
        ->once()
        ->with('flutterwave', 'wallet_123')
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
            ->with(FlutterwaveApi::WALLET_ACCOUNT_RESOLVE, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->resolveAccount('flutterwave', 'wallet_123');

    expect($result)->toBeInstanceOf(WalletAccountResolveData::class);
    expect($result->provider)->toBe('flutterwave');
    expect($result->identifier)->toBe('wallet_123');
    expect($result->name)->toBe('John Doe');
});

it('throws exception when resolving wallet account fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to resolve wallet account',
        data: null,
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('resolve')
        ->once()
        ->with('flutterwave', 'wallet_123')
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
            ->with(FlutterwaveApi::WALLET_ACCOUNT_RESOLVE, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    expect(fn () => $this->service->resolveAccount('flutterwave', 'wallet_123'))
        ->toThrow(FlutterwaveApiException::class);
});

it('can get wallet statement', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Wallet statement retrieved',
        data: [
            'cursor' => [
                'next' => 'next_cursor',
                'previous' => 'prev_cursor',
                'limit' => 10,
                'total' => 100,
                'has_more_items' => true,
            ],
            'transactions' => [
                [
                    'transaction_direction' => 'credit',
                    'amount' => ['value' => 1000, 'currency' => 'NGN'],
                ],
            ],
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('getStatement')
        ->once()
        ->with(['currency' => 'NGN'])
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
            ->with(FlutterwaveApi::WALLET_STATEMENT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->getStatement(['currency' => 'NGN']);

    expect($result)->toBeInstanceOf(WalletStatementData::class);
    expect($result->cursor)->toBeInstanceOf(\Gowelle\Flutterwave\Data\Wallet\WalletStatementCursor::class);
    expect($result->transactions)->toBeArray();
});

it('throws exception when getting wallet statement fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to retrieve wallet statement',
        data: null,
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('getStatement')
        ->once()
        ->with(['currency' => 'NGN'])
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
            ->with(FlutterwaveApi::WALLET_STATEMENT, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    expect(fn () => $this->service->getStatement(['currency' => 'NGN']))
        ->toThrow(FlutterwaveApiException::class);
});

it('can get wallet balance for a currency', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Wallet balance retrieved',
        data: [
            'currency' => 'NGN',
            'available_balance' => 1200.09,
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('getBalance')
        ->once()
        ->with('NGN')
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
            ->with(FlutterwaveApi::WALLET_BALANCE, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->getBalance('NGN');

    expect($result)->toBeInstanceOf(WalletBalanceData::class);
    expect($result->currency)->toBe('NGN');
    expect($result->availableBalance)->toBe(1200.09);
});

it('throws exception when getting wallet balance fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to fetch wallet balance',
        data: null,
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('getBalance')
        ->once()
        ->with('NGN')
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
            ->with(FlutterwaveApi::WALLET_BALANCE, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    expect(fn () => $this->service->getBalance('NGN'))
        ->toThrow(FlutterwaveApiException::class);
});

it('can get wallet balances for multiple currencies', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Wallet balances retrieved',
        data: [
            [
                'currency' => 'NGN',
                'available_balance' => 1200.09,
            ],
            [
                'currency' => 'USD',
                'available_balance' => 3.29,
            ],
        ],
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('getBalances')
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
            ->with(FlutterwaveApi::WALLET_BALANCES, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    $result = $this->service->getBalances();

    expect($result)->toBeArray();
    expect(\count($result))->toBe(2);
    expect($result[0])->toBeInstanceOf(WalletBalanceData::class);
    expect($result[0]->currency)->toBe('NGN');
    expect($result[1]->currency)->toBe('USD');
});

it('throws exception when getting wallet balances fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to fetch wallet balances',
        data: null,
    );

    $apiMock = \Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('getBalances')
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
            ->with(FlutterwaveApi::WALLET_BALANCES, 'test_token', ['Content-Type' => 'application/json'])
            ->andReturn($apiMock);
    }));

    expect(fn () => $this->service->getBalances())
        ->toThrow(FlutterwaveApiException::class);
});
