<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\ChargeData;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Data\PaymentMethodData;
use Gowelle\Flutterwave\Data\PaymentMethods\CardPaymentMethod;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwavePaymentsService;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

beforeEach(function () {
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->service = new FlutterwavePaymentsService($this->baseService);
});

it('can get payment methods', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Payment methods retrieved',
        data: [
            ['id' => '1', 'type' => 'card', 'customer_id' => 'cust_1', 'last4' => '2950', 'brand' => 'mastercard'],
            ['id' => '2', 'type' => 'card', 'customer_id' => 'cust_1', 'last4' => '1234', 'brand' => 'visa'],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('list')
        ->once()
        ->andReturn($response);

    $result = $this->service->methods([]);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(2);
    expect($result[0])->toBeInstanceOf(PaymentMethodData::class);
    expect($result[0]->id)->toBe('1');
    expect($result[0]->type)->toBe('card');
});

it('can create a payment method', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Payment method created',
        data: ['id' => 'pm_123', 'type' => 'card', 'customer_id' => 'cust_123', 'last4' => '2950', 'brand' => 'mastercard'],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->andReturn($response);

    $result = $this->service->createMethod([
        'customer_id' => 'cust_123',
        'type' => 'card',
    ]);

    expect($result)->toBeInstanceOf(CardPaymentMethod::class);
    expect($result->id)->toBe('pm_123');
    expect($result->type)->toBe('card');
    expect($result->customerId)->toBe('cust_123');
});

it('can get a payment method by id', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Payment method retrieved',
        data: ['id' => 'pm_123', 'type' => 'card', 'customer_id' => 'cust_123', 'last4' => '2950', 'brand' => 'mastercard'],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('retrieve')
        ->once()
        ->with(FlutterwaveApi::PAYMENT_METHODS, \Mockery::any(), 'pm_123')
        ->andReturn($response);

    $result = $this->service->getMethod('pm_123');

    expect($result)->toBeInstanceOf(CardPaymentMethod::class);
    expect($result->id)->toBe('pm_123');
    expect($result->type)->toBe('card');
});

it('can process a charge', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Charge created',
        data: [
            'id' => 'ch_123',
            'reference' => 'ref_123',
            'amount' => 1000,
            'currency' => 'TZS',
            'status' => 'pending',
        ],
    );

    $callbackCalled = false;
    $callbackTraceId = null;

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->andReturn($response);

    $result = $this->service->process(
        [
            'payment_method_type' => 'card',
            'customer_id' => 'cust_123',
            'payment_method_id' => 'pm_123',
            'amount' => 1000,
            'currency' => 'TZS',
            'reference' => 'ref_123',
            'redirect_url' => 'https://example.com/callback',
        ],
        function ($traceId) use (&$callbackCalled, &$callbackTraceId) {
            $callbackCalled = true;
            $callbackTraceId = $traceId;
        }
    );

    expect($result)->toBeInstanceOf(ChargeData::class);
    expect($result->id)->toBe('ch_123');
    expect($result->status)->toBe('pending');
    expect($callbackCalled)->toBeTrue();
    expect($callbackTraceId)->not->toBeNull();
});

it('can get charge status', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Charge retrieved',
        data: [
            'id' => 'ch_123',
            'reference' => 'ref_123',
            'amount' => 1000,
            'currency' => 'TZS',
            'status' => 'successful',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('retrieve')
        ->once()
        ->with(FlutterwaveApi::CHARGE, \Mockery::any(), 'ch_123')
        ->andReturn($response);

    $result = $this->service->status('ch_123');

    expect($result)->toBeInstanceOf(DirectChargeStatus::class);
    expect($result)->toBe(DirectChargeStatus::SUCCEEDED);
});
