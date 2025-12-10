<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\AuthorizationData;
use Gowelle\Flutterwave\Data\DirectChargeData;
use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Enums\FlutterwaveEnvironment;
use Gowelle\Flutterwave\Enums\NextActionType;
use Gowelle\Flutterwave\Events\FlutterwaveChargeCreated;
use Gowelle\Flutterwave\Events\FlutterwaveChargeUpdated;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();

    $this->config = new FlutterwaveConfig(
        'test_client_id',
        'test_client_secret',
        'test_secret_hash',
        FlutterwaveEnvironment::STAGING,
    );

    $this->baseService = Mockery::mock(FlutterwaveBaseService::class);
    $this->baseService->shouldReceive('getConfig')->andReturn($this->config);

    $this->service = new FlutterwaveDirectChargeService($this->baseService);
});

describe('DirectChargeFlow', function () {
    it('creates a direct charge successfully', function () {
        $this->baseService
            ->shouldReceive('create')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Charge created',
                data: [
                    'id' => 'dc_123456789',
                    'amount' => 10000,
                    'currency' => 'TZS',
                    'reference' => 'ORDER-REF-001',
                    'status' => 'pending',
                    'customer' => [
                        'id' => 'cust_123',
                        'email' => 'customer@example.com',
                    ],
                    'payment_method_details' => [
                        'type' => 'card',
                        'id' => 'pm_123',
                    ],
                ],
            ));

        $chargeData = $this->service->create([
            'amount' => 10000,
            'currency' => 'TZS',
            'reference' => 'ORDER-REF-001',
            'customer' => ['email' => 'customer@example.com'],
            'payment_method' => ['type' => 'card'],
            'redirect_url' => 'https://example.com/callback',
        ]);

        expect($chargeData)
            ->toBeInstanceOf(DirectChargeData::class)
            ->id->toBe('dc_123456789')
            ->amount->toBe(10000.0)
            ->currency->toBe('TZS')
            ->reference->toBe('ORDER-REF-001')
            ->status->toBe(DirectChargeStatus::PENDING);

        Event::assertDispatched(FlutterwaveChargeCreated::class, function ($event) {
            return $event->chargeData->id === 'dc_123456789';
        });
    });

    it('handles charge requiring PIN authorization', function () {
        $this->baseService
            ->shouldReceive('create')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Authorization required',
                data: [
                    'id' => 'dc_pin_required',
                    'amount' => 5000,
                    'currency' => 'NGN',
                    'reference' => 'ORDER-PIN-001',
                    'status' => 'requires_action',
                    'next_action' => [
                        'type' => 'requires_pin',
                        'requires_pin' => [
                            'message' => 'Please enter your card PIN',
                        ],
                    ],
                ],
            ));

        $chargeData = $this->service->create([
            'amount' => 5000,
            'currency' => 'NGN',
            'reference' => 'ORDER-PIN-001',
            'customer' => ['email' => 'user@example.com'],
            'payment_method' => ['type' => 'card'],
            'redirect_url' => 'https://example.com/callback',
        ]);

        expect($chargeData)
            ->status->toBe(DirectChargeStatus::REQUIRES_ACTION)
            ->requiresAction()->toBeTrue()
            ->nextAction->type->toBe(NextActionType::REQUIRES_PIN)
            ->and($chargeData->nextAction->getMessage())->toBe('Please enter your card PIN');
    });

    it('handles charge requiring OTP authorization', function () {
        $this->baseService
            ->shouldReceive('create')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'OTP required',
                data: [
                    'id' => 'dc_otp_required',
                    'amount' => 5000,
                    'currency' => 'NGN',
                    'reference' => 'ORDER-OTP-001',
                    'status' => 'requires_action',
                    'next_action' => [
                        'type' => 'requires_otp',
                        'requires_otp' => [
                            'message' => 'Enter OTP sent to your phone',
                            'reference' => 'otp_ref_123',
                        ],
                    ],
                ],
            ));

        $chargeData = $this->service->create([
            'amount' => 5000,
            'currency' => 'NGN',
            'reference' => 'ORDER-OTP-001',
            'customer' => ['email' => 'user@example.com'],
            'payment_method' => ['type' => 'card'],
            'redirect_url' => 'https://example.com/callback',
        ]);

        expect($chargeData)
            ->status->toBe(DirectChargeStatus::REQUIRES_ACTION)
            ->nextAction->type->toBe(NextActionType::REQUIRES_OTP);
    });

    it('handles charge requiring 3DS redirect', function () {
        $this->baseService
            ->shouldReceive('create')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: '3DS required',
                data: [
                    'id' => 'dc_3ds_required',
                    'amount' => 15000,
                    'currency' => 'USD',
                    'reference' => 'ORDER-3DS-001',
                    'status' => 'requires_action',
                    'next_action' => [
                        'type' => 'redirect_url',
                        'redirect_url' => [
                            'url' => 'https://bank.example.com/3ds-auth?token=abc123',
                        ],
                    ],
                ],
            ));

        $chargeData = $this->service->create([
            'amount' => 15000,
            'currency' => 'USD',
            'reference' => 'ORDER-3DS-001',
            'customer' => ['email' => 'user@example.com'],
            'payment_method' => ['type' => 'card'],
            'redirect_url' => 'https://example.com/callback',
        ]);

        expect($chargeData)
            ->status->toBe(DirectChargeStatus::REQUIRES_ACTION)
            ->nextAction->type->toBe(NextActionType::REDIRECT_URL)
            ->nextAction->requiresRedirect()->toBeTrue()
            ->and($chargeData->getRedirectUrl())->toBe('https://bank.example.com/3ds-auth?token=abc123');
    });

    it('submits PIN authorization and gets successful charge', function () {
        $this->baseService
            ->shouldReceive('update')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Charge completed',
                data: [
                    'id' => 'dc_pin_charge',
                    'amount' => 5000,
                    'currency' => 'NGN',
                    'reference' => 'ORDER-PIN-SUBMIT',
                    'status' => 'succeeded',
                    'issuer_response' => [
                        'code' => '00',
                        'message' => 'Approved',
                    ],
                ],
            ));

        $authorization = AuthorizationData::createPin('test_nonce', 'encrypted_pin_1234');
        $chargeData = $this->service->updateChargeAuthorization('dc_pin_charge', $authorization);

        expect($chargeData)
            ->status->toBe(DirectChargeStatus::SUCCEEDED)
            ->isSuccessful()->toBeTrue()
            ->isTerminal()->toBeTrue()
            ->and($chargeData->getIssuerResponseCode())->toBe('00');

        Event::assertDispatched(FlutterwaveChargeUpdated::class, function ($event) {
            return $event->chargeData->status === DirectChargeStatus::SUCCEEDED;
        });
    });

    it('submits OTP authorization and gets successful charge', function () {
        $this->baseService
            ->shouldReceive('update')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Charge completed',
                data: [
                    'id' => 'dc_otp_charge',
                    'amount' => 7500,
                    'currency' => 'NGN',
                    'reference' => 'ORDER-OTP-SUBMIT',
                    'status' => 'succeeded',
                ],
            ));

        $authorization = AuthorizationData::createOtp('123456');
        $chargeData = $this->service->updateChargeAuthorization('dc_otp_charge', $authorization);

        expect($chargeData)
            ->status->toBe(DirectChargeStatus::SUCCEEDED)
            ->isSuccessful()->toBeTrue();
    });

    it('handles declined charge', function () {
        $this->baseService
            ->shouldReceive('create')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Charge declined',
                data: [
                    'id' => 'dc_declined',
                    'amount' => 10000,
                    'currency' => 'TZS',
                    'reference' => 'ORDER-DECLINED',
                    'status' => 'failed',
                    'issuer_response' => [
                        'code' => '51',
                        'message' => 'Insufficient funds',
                    ],
                ],
            ));

        $chargeData = $this->service->create([
            'amount' => 10000,
            'currency' => 'TZS',
            'reference' => 'ORDER-DECLINED',
            'customer' => ['email' => 'user@example.com'],
            'payment_method' => ['type' => 'card'],
            'redirect_url' => 'https://example.com/callback',
        ]);

        expect($chargeData)
            ->status->toBe(DirectChargeStatus::FAILED)
            ->isSuccessful()->toBeFalse()
            ->isTerminal()->toBeTrue()
            ->and($chargeData->getIssuerResponseCode())->toBe('51')
            ->and($chargeData->getIssuerResponseMessage())->toBe('Insufficient funds');
    });

    it('retrieves charge status', function () {
        $this->baseService
            ->shouldReceive('retrieve')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Status retrieved',
                data: [
                    'id' => 'dc_status_check',
                    'amount' => 5000,
                    'currency' => 'TZS',
                    'reference' => 'ORDER-STATUS',
                    'status' => 'succeeded',
                ],
            ));

        $status = $this->service->status('dc_status_check');

        expect($status)
            ->toBeInstanceOf(DirectChargeStatus::class)
            ->toBe(DirectChargeStatus::SUCCEEDED);
    });

    it('handles mobile money charge with payment instructions', function () {
        $this->baseService
            ->shouldReceive('create')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Payment instruction',
                data: [
                    'id' => 'dc_momo_001',
                    'amount' => 25000,
                    'currency' => 'TZS',
                    'reference' => 'ORDER-MOMO-001',
                    'status' => 'requires_action',
                    'next_action' => [
                        'type' => 'payment_instruction',
                        'payment_instruction' => [
                            'note' => 'Complete payment on your phone',
                            'instructions' => 'Dial *150*00# to approve payment',
                        ],
                    ],
                ],
            ));

        $chargeData = $this->service->create([
            'amount' => 25000,
            'currency' => 'TZS',
            'reference' => 'ORDER-MOMO-001',
            'customer' => ['email' => 'user@example.com', 'phone_number' => '+255712345678'],
            'payment_method' => ['type' => 'mobile_money', 'mobile_network' => 'M-PESA'],
            'redirect_url' => 'https://example.com/callback',
        ]);

        expect($chargeData)
            ->status->toBe(DirectChargeStatus::REQUIRES_ACTION)
            ->nextAction->type->toBe(NextActionType::PAYMENT_INSTRUCTION)
            ->nextAction->isAsynchronous()->toBeTrue()
            ->and($chargeData->nextAction->getPaymentInstructionNote())->toBe('Complete payment on your phone');
    });
});

describe('DirectCharge Edge Cases', function () {
    it('handles successful charge without next action', function () {
        $this->baseService
            ->shouldReceive('create')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Instant success',
                data: [
                    'id' => 'dc_instant_success',
                    'amount' => 1000,
                    'currency' => 'TZS',
                    'reference' => 'ORDER-INSTANT',
                    'status' => 'succeeded',
                ],
            ));

        $chargeData = $this->service->create([
            'amount' => 1000,
            'currency' => 'TZS',
            'reference' => 'ORDER-INSTANT',
            'customer' => ['email' => 'user@example.com'],
            'payment_method' => ['type' => 'saved_card', 'id' => 'pm_saved_123'],
            'redirect_url' => 'https://example.com/callback',
        ]);

        expect($chargeData)
            ->status->toBe(DirectChargeStatus::SUCCEEDED)
            ->isSuccessful()->toBeTrue()
            ->nextAction->type->toBe(NextActionType::NONE);
    });

    it('handles charge with metadata', function () {
        $this->baseService
            ->shouldReceive('create')
            ->once()
            ->andReturn(new ApiResponse(
                status: 'success',
                message: 'Charge with metadata',
                data: [
                    'id' => 'dc_with_meta',
                    'amount' => 5000,
                    'currency' => 'TZS',
                    'reference' => 'ORDER-META',
                    'status' => 'pending',
                    'meta' => [
                        'order_id' => '12345',
                        'product_name' => 'Test Product',
                        'custom_data' => ['key' => 'value'],
                    ],
                ],
            ));

        $chargeData = $this->service->create([
            'amount' => 5000,
            'currency' => 'TZS',
            'reference' => 'ORDER-META',
            'customer' => ['email' => 'user@example.com'],
            'payment_method' => ['type' => 'card'],
            'redirect_url' => 'https://example.com/callback',
            'meta' => ['order_id' => '12345', 'product_name' => 'Test Product'],
        ]);

        expect($chargeData)
            ->and($chargeData->getMeta('order_id'))->toBe('12345')
            ->and($chargeData->getMeta('product_name'))->toBe('Test Product')
            ->and($chargeData->getMeta('missing_key', 'default'))->toBe('default');
    });
});
