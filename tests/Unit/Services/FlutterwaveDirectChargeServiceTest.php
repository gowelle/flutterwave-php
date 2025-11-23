<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\AuthorizationData;
use Gowelle\Flutterwave\Data\DirectChargeData;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Events\DirectChargeCreated;
use Gowelle\Flutterwave\Events\DirectChargeUpdated;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->service = new FlutterwaveDirectChargeService($this->baseService);
});

it('can create a direct charge', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Direct charge created',
        data: [
            'id' => 'dc_123',
            'amount' => 1000,
            'currency' => 'TZS',
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
        'amount' => 1000,
        'currency' => 'TZS',
        'reference' => 'ORDER-123',
        'customer' => [
            'email' => 'test@example.com',
            'name' => 'Test Customer',
        ],
        'payment_method' => [
            'type' => 'card',
        ],
        'redirect_url' => 'https://example.com/callback',
    ]);

    expect($result)->toBeInstanceOf(DirectChargeData::class);
    Event::assertDispatched(DirectChargeCreated::class);
});

it('can update charge authorization', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Charge authorization updated',
        data: [
            'id' => 'dc_123',
            'status' => 'successful',
        ],
    );

    $authorizationData = AuthorizationData::createPin('nonce_123', 'encrypted_pin_123');

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('update')
        ->once()
        ->andReturn($response);

    $result = $this->service->updateChargeAuthorization('dc_123', $authorizationData);

    expect($result)->toBeInstanceOf(DirectChargeData::class);
    Event::assertDispatched(DirectChargeUpdated::class);
});

it('can get charge status', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Charge retrieved',
        data: [
            'id' => 'dc_123',
            'status' => 'successful',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('retrieve')
        ->once()
        ->with(FlutterwaveApi::DIRECT_CHARGE, \Mockery::any(), 'dc_123')
        ->andReturn($response);

    $result = $this->service->status('dc_123');

    expect($result)->toBeInstanceOf(DirectChargeStatus::class);
});

