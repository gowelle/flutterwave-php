<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Chargeback\ChargebackData;
use Gowelle\Flutterwave\Data\Chargeback\CreateChargebackRequest;
use Gowelle\Flutterwave\Data\Chargeback\UpdateChargebackRequest;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApiContract;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveChargebackService;
use Gowelle\Flutterwave\Support\HeaderBuilder;

beforeEach(function () {
    $this->baseService = Mockery::mock(FlutterwaveBaseService::class);
    $this->headerBuilder = Mockery::mock(HeaderBuilder::class);
    $this->service = new FlutterwaveChargebackService($this->baseService);
});

it('lists chargebacks with trace-only headers', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Chargebacks retrieved',
        data: [
            [
                'id' => 'cbk_123',
                'charge_id' => 'chg_123',
                'amount' => 12.34,
                'status' => 'pending',
                'type' => 'local',
            ],
        ],
    );

    $apiMock = Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('listWithParams')
        ->once()
        ->with([
            'page' => 1,
            'size' => 10,
        ])
        ->andReturn($response);

    $this->baseService->shouldReceive('getAccessToken')->once()->andReturn('test_token');
    $this->baseService->shouldReceive('getHeaderBuilder')->once()->andReturn($this->headerBuilder);

    $this->headerBuilder->shouldReceive('fromArray')
        ->once()
        ->with(Mockery::on(fn (array $headers) => isset($headers['X-Trace-Id']) && ! isset($headers['X-Idempotency-Key']) && ! isset($headers['X-Scenario-Key'])))
        ->andReturn([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => 'trace-chargeback-123456',
        ]);

    app()->instance(FlutterwaveApiProvider::class, Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(
                FlutterwaveApi::CHARGEBACK,
                'test_token',
                [
                    'Content-Type' => 'application/json',
                    'X-Trace-Id' => 'trace-chargeback-123456',
                ]
            )
            ->andReturn($apiMock);
    }));

    $result = $this->service->list([
        'page' => 1,
        'size' => 10,
    ]);

    expect($result)->toBeArray();
    expect($result[0])->toBeInstanceOf(ChargebackData::class);
});

it('creates chargebacks with trace and idempotency headers', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Chargeback created',
        data: [
            'id' => 'cbk_123',
            'charge_id' => 'chg_123',
            'amount' => 12.34,
            'status' => 'pending',
            'type' => 'local',
        ],
    );

    $request = new CreateChargebackRequest(
        chargeId: 'chg_123',
        amount: 12.34,
        type: 'local',
        expiry: 72,
        stage: 'new',
        status: 'pending',
    );

    $apiMock = Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('createFromDto')
        ->once()
        ->with($request)
        ->andReturn($response);

    $this->baseService->shouldReceive('getAccessToken')->once()->andReturn('test_token');
    $this->baseService->shouldReceive('getHeaderBuilder')->once()->andReturn($this->headerBuilder);

    $this->headerBuilder->shouldReceive('fromArray')
        ->once()
        ->with(Mockery::on(fn (array $headers) => isset($headers['X-Trace-Id']) && isset($headers['X-Idempotency-Key']) && ! isset($headers['X-Scenario-Key'])))
        ->andReturn([
            'Content-Type' => 'application/json',
            'X-Idempotency-Key' => 'idem-chargeback-123456',
            'X-Trace-Id' => 'trace-chargeback-123456',
        ]);

    app()->instance(FlutterwaveApiProvider::class, Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(
                FlutterwaveApi::CHARGEBACK,
                'test_token',
                [
                    'Content-Type' => 'application/json',
                    'X-Idempotency-Key' => 'idem-chargeback-123456',
                    'X-Trace-Id' => 'trace-chargeback-123456',
                ]
            )
            ->andReturn($apiMock);
    }));

    $result = $this->service->create($request);

    expect($result)->toBeInstanceOf(ChargebackData::class);
    expect($result->id)->toBe('cbk_123');
});

it('retrieves chargebacks with trace-only headers', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Chargeback retrieved',
        data: [
            'id' => 'cbk_123',
            'charge_id' => 'chg_123',
            'amount' => 12.34,
            'status' => 'pending',
            'type' => 'local',
        ],
    );

    $apiMock = Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('retrieve')
        ->once()
        ->with('cbk_123')
        ->andReturn($response);

    $this->baseService->shouldReceive('getAccessToken')->once()->andReturn('test_token');
    $this->baseService->shouldReceive('getHeaderBuilder')->once()->andReturn($this->headerBuilder);

    $this->headerBuilder->shouldReceive('fromArray')
        ->once()
        ->andReturn([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => 'trace-chargeback-123456',
        ]);

    app()->instance(FlutterwaveApiProvider::class, Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->with(
                FlutterwaveApi::CHARGEBACK,
                'test_token',
                [
                    'Content-Type' => 'application/json',
                    'X-Trace-Id' => 'trace-chargeback-123456',
                ]
            )
            ->andReturn($apiMock);
    }));

    $result = $this->service->retrieve('cbk_123');

    expect($result)->toBeInstanceOf(ChargebackData::class);
});

it('accepts a chargeback via update', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Chargeback updated',
        data: [
            'id' => 'cbk_123',
            'charge_id' => 'chg_123',
            'status' => 'accepted',
        ],
    );

    $apiMock = Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('updateFromDto')
        ->once()
        ->with('cbk_123', Mockery::on(function (UpdateChargebackRequest $request) {
            return $request->status === 'accepted' && $request->comment === 'Accepted';
        }))
        ->andReturn($response);

    $this->baseService->shouldReceive('getAccessToken')->once()->andReturn('test_token');
    $this->baseService->shouldReceive('getHeaderBuilder')->once()->andReturn($this->headerBuilder);

    $this->headerBuilder->shouldReceive('fromArray')
        ->once()
        ->andReturn([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => 'trace-chargeback-123456',
        ]);

    app()->instance(FlutterwaveApiProvider::class, Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->andReturn($apiMock);
    }));

    $result = $this->service->accept('cbk_123', 'Accepted');

    expect($result->isAccepted())->toBeTrue();
});

it('declines a chargeback with a declined request only', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Chargeback updated',
        data: [
            'id' => 'cbk_123',
            'charge_id' => 'chg_123',
            'status' => 'declined',
            'uploaded_proof' => 'https://example.com/proof.jpg',
        ],
    );

    $request = UpdateChargebackRequest::decline(
        message: 'Service was securely delivered',
        evidenceUrl: 'https://example.com/proof.jpg',
    );

    $apiMock = Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('updateFromDto')
        ->once()
        ->with('cbk_123', $request)
        ->andReturn($response);

    $this->baseService->shouldReceive('getAccessToken')->once()->andReturn('test_token');
    $this->baseService->shouldReceive('getHeaderBuilder')->once()->andReturn($this->headerBuilder);

    $this->headerBuilder->shouldReceive('fromArray')
        ->once()
        ->andReturn([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => 'trace-chargeback-123456',
        ]);

    app()->instance(FlutterwaveApiProvider::class, Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')
            ->once()
            ->andReturn($apiMock);
    }));

    $result = $this->service->decline('cbk_123', $request);

    expect($result->isDeclined())->toBeTrue();
});

it('throws when decline receives a non-declined request', function () {
    expect(fn () => $this->service->decline('cbk_123', UpdateChargebackRequest::accept()))
        ->toThrow(InvalidArgumentException::class);
});

it('throws exception when create fails', function () {
    $response = new ApiResponse(
        status: 'error',
        message: 'Failed to create chargeback',
        data: null,
    );

    $request = new CreateChargebackRequest(
        chargeId: 'chg_123',
        amount: 12.34,
        type: 'local',
        expiry: 72,
    );

    $apiMock = Mockery::mock(FlutterwaveApiContract::class);
    $apiMock->shouldReceive('createFromDto')->once()->with($request)->andReturn($response);

    $this->baseService->shouldReceive('getAccessToken')->once()->andReturn('test_token');
    $this->baseService->shouldReceive('getHeaderBuilder')->once()->andReturn($this->headerBuilder);
    $this->headerBuilder->shouldReceive('fromArray')->once()->andReturn([
        'Content-Type' => 'application/json',
        'X-Idempotency-Key' => 'idem-chargeback-123456',
        'X-Trace-Id' => 'trace-chargeback-123456',
    ]);

    app()->instance(FlutterwaveApiProvider::class, Mockery::mock(FlutterwaveApiProvider::class, function ($mock) use ($apiMock) {
        $mock->shouldReceive('useApi')->once()->andReturn($apiMock);
    }));

    expect(fn () => $this->service->create($request))
        ->toThrow(FlutterwaveApiException::class);
});
