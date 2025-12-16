<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Data\Refund\CreateRefundRequest;
use Gowelle\Flutterwave\Data\Refund\ListRefundsRequest;
use Gowelle\Flutterwave\Data\RefundData;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Enums\RefundReason;
use Gowelle\Flutterwave\Enums\RefundStatus;
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
            'amount_refunded' => 500,
            'status' => 'pending',
            'reason' => 'requested_by_customer',
            'created_datetime' => '2025-02-13T12:12:22Z',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->andReturn($response);

    $request = new CreateRefundRequest(
        amount: 500,
        chargeId: 'ch_123',
        reason: RefundReason::REQUESTED_BY_CUSTOMER,
    );

    $result = $this->service->create($request);

    expect($result)->toBeInstanceOf(RefundData::class);
    expect($result->id)->toBe('ref_123');
    expect($result->amountRefunded)->toBe(500.0);
    expect($result->status)->toBe(RefundStatus::PENDING);
});

it('can get a refund by id', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Refund retrieved',
        data: [
            'id' => 'ref_123',
            'charge_id' => 'ch_123',
            'amount_refunded' => 500,
            'status' => 'succeeded',
            'reason' => 'duplicate',
            'created_datetime' => '2025-02-13T12:12:22Z',
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
    expect($result->isSuccessful())->toBeTrue();
});

it('can list refunds', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Refunds retrieved',
        data: [
            [
                'id' => 'ref_1',
                'charge_id' => 'ch_1',
                'amount_refunded' => 100,
                'status' => 'succeeded',
                'reason' => 'duplicate',
            ],
            [
                'id' => 'ref_2',
                'charge_id' => 'ch_2',
                'amount_refunded' => 200,
                'status' => 'succeeded',
                'reason' => 'requested_by_customer',
            ],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('getHeaderBuilder->build')
        ->andReturn(\Mockery::mock());

    $this->baseService
        ->shouldReceive('getAccessToken')
        ->andReturn('test_token');

    // Mock the API provider
    $mockApi = \Mockery::mock();
    $mockApi->shouldReceive('listWithParams')
        ->andReturn($response);

    $this->baseService
        ->shouldReceive('list')
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
        ->shouldReceive('getHeaderBuilder->build')
        ->andReturn(\Mockery::mock());

    $this->baseService
        ->shouldReceive('getAccessToken')
        ->andReturn('test_token');

    // Mock the API provider
    $mockApi = \Mockery::mock();
    $mockApi->shouldReceive('listWithParams')
        ->andReturn($response);

    $result = $this->service->list();

    expect($result)->toBeArray();
    expect($result)->toBeEmpty();
});

it('can list refunds with filters', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Refunds retrieved',
        data: [
            [
                'id' => 'ref_1',
                'charge_id' => 'ch_1',
                'amount_refunded' => 100,
                'status' => 'succeeded',
            ],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('getHeaderBuilder->build')
        ->andReturn(\Mockery::mock());

    $this->baseService
        ->shouldReceive('getAccessToken')
        ->andReturn('test_token');

    // Mock the API provider
    $mockApi = \Mockery::mock();
    $mockApi->shouldReceive('listWithParams')
        ->andReturn($response);

    $request = new ListRefundsRequest(page: 1, size: 10);

    $result = $this->service->list($request);

    expect($result)->toBeArray();
    expect(count($result))->toBe(1);
});

it('creates refund DTO with correct status enum', function () {
    $data = [
        'id' => 'ref_123',
        'charge_id' => 'ch_123',
        'amount_refunded' => 500,
        'status' => 'pending',
        'reason' => 'duplicate',
        'meta' => ['key' => 'value'],
        'created_datetime' => '2025-02-13T12:12:22Z',
    ];

    $refund = RefundData::fromApi($data);

    expect($refund->status)->toBe(RefundStatus::PENDING);
    expect($refund->isPending())->toBeTrue();
    expect($refund->isSuccessful())->toBeFalse();
});

it('handles refund status conversion', function () {
    $data = [
        'id' => 'ref_123',
        'charge_id' => 'ch_123',
        'amount_refunded' => 500,
        'status' => 'succeeded', // Multiple possible values
        'created_datetime' => '2025-02-13T12:12:22Z',
    ];

    $refund = RefundData::fromApi($data);

    expect($refund->status->isSuccessful())->toBeTrue();
    expect($refund->status->isTerminal())->toBeTrue();
});
