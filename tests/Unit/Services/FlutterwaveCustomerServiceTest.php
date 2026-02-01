<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Customer\CreateCustomerRequest;
use Gowelle\Flutterwave\Data\Customer\SearchCustomerRequest;
use Gowelle\Flutterwave\Data\Customer\UpdateCustomerRequest;
use Gowelle\Flutterwave\Data\CustomerData;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveCustomerService;

beforeEach(function () {
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->service = new FlutterwaveCustomerService($this->baseService);
});

it('can create a customer', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Customer created',
        data: [
            'id' => 'cust_123',
            'email' => 'test@example.com',
            'name' => 'Test Customer',
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
        'email' => 'test@example.com',
        'name' => 'Test Customer',
        'phone' => ['country_code' => 'TZA', 'number' => '712345678'],
    ]);

    expect($result)->toBeInstanceOf(CustomerData::class);
});

it('can create a customer from DTO', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Customer created',
        data: [
            'id' => 'cust_123',
            'email' => 'test@example.com',
            'name' => 'Test Customer',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->andReturn($response);

    $request = new CreateCustomerRequest(
        email: 'test@example.com',
        firstName: 'Test',
        lastName: 'Customer',
        phone: ['country_code' => 'TZA', 'number' => '712345678'],
    );

    $result = $this->service->createFromDto($request);

    expect($result)->toBeInstanceOf(CustomerData::class);
});

it('can list customers', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Customers retrieved',
        data: [
            ['id' => 'cust_1', 'email' => 'test1@example.com'],
            ['id' => 'cust_2', 'email' => 'test2@example.com'],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('list')
        ->once()
        ->andReturn($response);

    $result = $this->service->list([]);

    expect($result)->toBeArray();
    expect(\count($result))->toBe(2);
    expect($result[0])->toBeInstanceOf(CustomerData::class);
});

it('can get a customer by id', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Customer retrieved',
        data: [
            'id' => 'cust_123',
            'email' => 'test@example.com',
            'name' => 'Test Customer',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('retrieve')
        ->once()
        ->with(FlutterwaveApi::CUSTOMER, \Mockery::any(), 'cust_123')
        ->andReturn($response);

    $result = $this->service->get('cust_123');

    expect($result)->toBeInstanceOf(CustomerData::class);
    expect($result->id)->toBe('cust_123');
});

it('can update a customer', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Customer updated',
        data: [
            'id' => 'cust_123',
            'email' => 'updated@example.com',
            'name' => 'Updated Customer',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('update')
        ->once()
        ->with(FlutterwaveApi::CUSTOMER, \Mockery::any(), 'cust_123', \Mockery::any())
        ->andReturn($response);

    $result = $this->service->update('cust_123', [
        'name' => 'Updated Customer',
    ]);

    expect($result)->toBeInstanceOf(CustomerData::class);
});

it('can update a customer from DTO', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Customer updated',
        data: [
            'id' => 'cust_123',
            'email' => 'updated@example.com',
            'name' => 'Updated Customer',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('update')
        ->once()
        ->with(FlutterwaveApi::CUSTOMER, \Mockery::any(), 'cust_123', \Mockery::any())
        ->andReturn($response);

    $request = new UpdateCustomerRequest(
        email: 'updated@example.com',
        firstName: 'Updated',
        lastName: 'Customer',
        phone: ['country_code' => 'TZA', 'number' => '712345678'],
    );

    $result = $this->service->updateFromDto('cust_123', $request);

    expect($result)->toBeInstanceOf(CustomerData::class);
});

it('can search for a customer by email', function () {
    // API returns data as array of customers
    $response = new ApiResponse(
        status: 'success',
        message: 'Customer found',
        data: [
            [
                'id' => 'cust_123',
                'email' => 'test@example.com',
            ],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('search')
        ->once()
        ->with(FlutterwaveApi::CUSTOMER, \Mockery::any(), ['email' => 'test@example.com'])
        ->andReturn($response);

    $result = $this->service->search('test@example.com');

    expect($result)->toBeInstanceOf(CustomerData::class);
});

it('can search for a customer from DTO', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Customer found',
        data: [
            [
                'id' => 'cust_123',
                'email' => 'test@example.com',
            ],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('search')
        ->once()
        ->with(FlutterwaveApi::CUSTOMER, \Mockery::any(), ['email' => 'test@example.com'])
        ->andReturn($response);

    $request = new SearchCustomerRequest(email: 'test@example.com');

    $result = $this->service->searchFromDto($request);

    expect($result)->toBeInstanceOf(CustomerData::class);
});

it('throws when searchFromDto finds no customer', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'No customer found',
        data: [],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('search')
        ->once()
        ->andReturn($response);

    $request = new SearchCustomerRequest(email: 'missing@example.com');

    $this->service->searchFromDto($request);
})->throws(\RuntimeException::class, 'No customer found with email: missing@example.com');

it('returns empty array when list response has no data', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'No customers found',
        data: null,
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('list')
        ->once()
        ->andReturn($response);

    $result = $this->service->list([]);

    expect($result)->toBeArray();
    expect($result)->toBeEmpty();
});
