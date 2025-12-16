<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Data\Transfer\BankTransferRequest;
use Gowelle\Flutterwave\Data\Transfer\CreateRecipientRequest;
use Gowelle\Flutterwave\Data\Transfer\CreateSenderRequest;
use Gowelle\Flutterwave\Data\Transfer\GetRateRequest;
use Gowelle\Flutterwave\Data\Transfer\MobileMoneyTransferRequest;
use Gowelle\Flutterwave\Data\Transfer\RateData;
use Gowelle\Flutterwave\Data\Transfer\RecipientData;
use Gowelle\Flutterwave\Data\Transfer\SenderData;
use Gowelle\Flutterwave\Data\Transfer\TransferData;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Enums\TransferStatus;
use Gowelle\Flutterwave\Enums\TransferType;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveTransferService;

beforeEach(function () {
    $this->baseService = \Mockery::mock(FlutterwaveBaseService::class);
    $this->service = new FlutterwaveTransferService($this->baseService);
});

it('can create a bank transfer via orchestrator', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Transfer created',
        data: [
            'id' => 'trf_123',
            'type' => 'bank',
            'action' => 'instant',
            'reference' => 'PAYOUT-123',
            'status' => 'NEW',
            'source_currency' => 'NGN',
            'destination_currency' => 'NGN',
            'amount' => ['value' => 5000, 'applies_to' => 'destination_currency'],
            'recipient' => ['bank' => ['account_number' => '0123456789', 'code' => '044']],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->with(FlutterwaveApi::DIRECT_TRANSFER, \Mockery::any(), \Mockery::any())
        ->andReturn($response);

    $request = new BankTransferRequest(
        amount: 5000,
        sourceCurrency: 'NGN',
        destinationCurrency: 'NGN',
        accountNumber: '0123456789',
        bankCode: '044',
        reference: 'PAYOUT-123',
    );

    $result = $this->service->bankTransfer($request);

    expect($result)->toBeInstanceOf(TransferData::class);
    expect($result->id)->toBe('trf_123');
    expect($result->type)->toBe(TransferType::BANK);
    expect($result->status)->toBe(TransferStatus::NEW);
});

it('can create a mobile money transfer via orchestrator', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Transfer created',
        data: [
            'id' => 'trf_456',
            'type' => 'mobile_money',
            'action' => 'instant',
            'reference' => 'MOMO-123',
            'status' => 'PENDING',
            'source_currency' => 'NGN',
            'destination_currency' => 'GHS',
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->andReturn($response);

    $request = new MobileMoneyTransferRequest(
        amount: 1000,
        sourceCurrency: 'NGN',
        destinationCurrency: 'GHS',
        network: 'MTN',
        phoneNumber: '2339012345678',
        firstName: 'John',
        lastName: 'Doe',
        reference: 'MOMO-123',
    );

    $result = $this->service->mobileMoneyTransfer($request);

    expect($result)->toBeInstanceOf(TransferData::class);
    expect($result->type)->toBe(TransferType::MOBILE_MONEY);
});

it('can get a transfer by id', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Transfer retrieved',
        data: [
            'id' => 'trf_123',
            'type' => 'bank',
            'action' => 'instant',
            'reference' => 'PAYOUT-123',
            'status' => 'SUCCEEDED',
            'source_currency' => 'NGN',
            'destination_currency' => 'NGN',
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
    expect($result->status->isSuccessful())->toBeTrue();
});

it('can list transfers', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Transfers retrieved',
        data: [
            ['id' => 'trf_1', 'type' => 'bank', 'action' => 'instant', 'status' => 'NEW', 'reference' => 'REF-1', 'source_currency' => 'NGN', 'destination_currency' => 'NGN'],
            ['id' => 'trf_2', 'type' => 'wallet', 'action' => 'instant', 'status' => 'SUCCEEDED', 'reference' => 'REF-2', 'source_currency' => 'NGN', 'destination_currency' => 'NGN'],
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
    expect(\count($result))->toBe(2);
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

it('can create a recipient', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Recipient created',
        data: [
            'id' => 'rcp_123',
            'type' => 'bank',
            'currency' => 'NGN',
            'bank' => ['account_number' => '0123456789', 'code' => '044'],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->with(FlutterwaveApi::TRANSFER_RECIPIENTS, \Mockery::any(), \Mockery::any())
        ->andReturn($response);

    $request = CreateRecipientRequest::bankNgn(
        accountNumber: '0123456789',
        bankCode: '044',
    );

    $result = $this->service->createRecipient($request);

    expect($result)->toBeInstanceOf(RecipientData::class);
    expect($result->id)->toBe('rcp_123');
});

it('can create a sender', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Sender created',
        data: [
            'id' => 'snd_123',
            'type' => 'generic_sender',
            'name' => ['first' => 'John', 'last' => 'Doe'],
            'email' => 'john@example.com',
            'phone' => ['country_code' => '234', 'number' => '1234567890'],
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->with(FlutterwaveApi::TRANSFER_SENDERS, \Mockery::any(), \Mockery::any())
        ->andReturn($response);

    $request = CreateSenderRequest::generic(
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        phone: ['country_code' => '234', 'number' => '1234567890'],
    );

    $result = $this->service->createSender($request);

    expect($result)->toBeInstanceOf(SenderData::class);
    expect($result->getFullName())->toBe('John Doe');
});

it('can get transfer rate', function () {
    $response = new ApiResponse(
        status: 'success',
        message: 'Rate retrieved',
        data: [
            'source_currency' => 'NGN',
            'destination_currency' => 'GHS',
            'rate' => 0.013,
            'source_amount' => 10000,
            'destination_amount' => 130,
        ],
    );

    $this->baseService
        ->shouldReceive('getConfig')
        ->andReturn(new FlutterwaveConfig('test_client_id', 'test_client_secret', 'test_secret_hash', FlutterwaveEnvironment::STAGING));

    $this->baseService
        ->shouldReceive('create')
        ->once()
        ->with(FlutterwaveApi::TRANSFER_RATES, \Mockery::any(), \Mockery::any())
        ->andReturn($response);

    $request = new GetRateRequest(
        sourceCurrency: 'NGN',
        destinationCurrency: 'GHS',
        amount: 10000,
    );

    $result = $this->service->getRate($request);

    expect($result)->toBeInstanceOf(RateData::class);
    expect($result->rate)->toBe(0.013);
});
