<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Fees\FeesData;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApiContract;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveFeesService;
use Gowelle\Flutterwave\Support\HeaderBuilder;

beforeEach(function () {
    $this->baseService = Mockery::mock(FlutterwaveBaseService::class);
    $this->headerBuilder = Mockery::mock(HeaderBuilder::class);
    $this->service = new FlutterwaveFeesService($this->baseService);
});

it('can calculate fees', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Fees retrieved',
        data: [
            'charge_amount' => 5000,
            'fee' => 150,
            'merchant_fee' => 100,
            'flutterwave_fee' => 50,
            'currency' => 'TZS',
        ],
    );

    $apiMock = Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('getFees')
        ->once()
        ->with([
            'amount' => 5000,
            'currency' => 'TZS',
            'payment_method' => 'mobile_money',
            'network' => 'M-PESA',
        ])
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
        ->shouldReceive('fromArray')
        ->once()
        ->with(Mockery::on(fn (array $headers) => isset($headers['X-Trace-Id']) && ! isset($headers['X-Idempotency-Key']) && ! isset($headers['X-Scenario-Key'])))
        ->andReturn([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => 'trace-fees-123456',
        ]);

    app()->instance(FlutterwaveApiProvider::class, Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(
                FlutterwaveApi::FEES,
                'test_token',
                [
                    'Content-Type' => 'application/json',
                    'X-Trace-Id' => 'trace-fees-123456',
                ]
            )
            ->andReturn($apiMock);
    }));

    $result = $this->service->calculate([
        'amount' => 5000,
        'currency' => 'TZS',
        'payment_method' => 'mobile_money',
        'network' => 'M-PESA',
    ]);

    expect($result)->toBeInstanceOf(FeesData::class);
    expect($result->fee)->toBe(150.0);
});

it('throws exception when fees calculation fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to retrieve fees',
        data: null,
    );

    $apiMock = Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('getFees')
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
        ->shouldReceive('fromArray')
        ->once()
        ->andReturn([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => 'trace-fees-123456',
        ]);

    app()->instance(FlutterwaveApiProvider::class, Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(
                FlutterwaveApi::FEES,
                'test_token',
                [
                    'Content-Type' => 'application/json',
                    'X-Trace-Id' => 'trace-fees-123456',
                ]
            )
            ->andReturn($apiMock);
    }));

    expect(fn () => $this->service->calculate([
        'amount' => 5000,
        'currency' => 'TZS',
        'payment_method' => 'mobile_money',
    ]))->toThrow(FlutterwaveApiException::class);
});
