<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\DirectChargeData;
use Gowelle\Flutterwave\Data\NextActionData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Enums\NextActionType;

describe('DirectChargeData', function () {
    it('creates from minimal API response', function () {
        $data = [
            'id' => 'dc_123',
            'amount' => 1000,
            'currency' => 'TZS',
            'reference' => 'ORDER-123',
            'status' => 'pending',
        ];

        $chargeData = DirectChargeData::fromApi($data);

        expect($chargeData)
            ->toBeInstanceOf(DirectChargeData::class)
            ->id->toBe('dc_123')
            ->amount->toBe(1000.0)
            ->currency->toBe('TZS')
            ->reference->toBe('ORDER-123')
            ->status->toBe(DirectChargeStatus::PENDING);
    });

    it('creates from full API response', function () {
        $data = [
            'id' => 'dc_456',
            'amount' => 5000,
            'currency' => 'NGN',
            'reference' => 'ORDER-456',
            'status' => 'succeeded',
            'customer' => [
                'id' => 'cust_123',
                'email' => 'customer@example.com',
                'name' => 'John Doe',
            ],
            'payment_method_details' => [
                'type' => 'card',
                'id' => 'pm_123',
            ],
            'issuer_response' => [
                'code' => '00',
                'message' => 'Approved',
            ],
            'meta' => [
                'order_id' => '12345',
                'user_id' => '67890',
            ],
            'created_at' => '2025-01-01T12:00:00Z',
            'redirect_url' => 'https://example.com/callback',
        ];

        $chargeData = DirectChargeData::fromApi($data);

        expect($chargeData)
            ->id->toBe('dc_456')
            ->amount->toBe(5000.0)
            ->currency->toBe('NGN')
            ->status->toBe(DirectChargeStatus::SUCCEEDED)
            ->customer->toBe($data['customer'])
            ->paymentMethodDetails->toBe($data['payment_method_details'])
            ->issuerResponse->toBe($data['issuer_response'])
            ->meta->toBe($data['meta'])
            ->createdAt->toBe('2025-01-01T12:00:00Z')
            ->redirectUrl->toBe('https://example.com/callback');
    });

    it('handles different status string variants', function (string $apiStatus, DirectChargeStatus $expectedStatus) {
        $data = [
            'id' => 'dc_789',
            'status' => $apiStatus,
        ];

        $chargeData = DirectChargeData::fromApi($data);

        expect($chargeData->status)->toBe($expectedStatus);
    })->with([
        'succeeded' => ['succeeded', DirectChargeStatus::SUCCEEDED],
        'successful' => ['successful', DirectChargeStatus::SUCCEEDED],
        'success' => ['success', DirectChargeStatus::SUCCEEDED],
        'pending' => ['pending', DirectChargeStatus::PENDING],
        'requires_action' => ['requires_action', DirectChargeStatus::REQUIRES_ACTION],
        'failed' => ['failed', DirectChargeStatus::FAILED],
        'declined' => ['declined', DirectChargeStatus::FAILED],
        'cancelled' => ['cancelled', DirectChargeStatus::CANCELLED],
        'canceled (US spelling)' => ['canceled', DirectChargeStatus::CANCELLED],
        'timeout' => ['timeout', DirectChargeStatus::TIMEOUT],
        'expired' => ['expired', DirectChargeStatus::TIMEOUT],
        'unknown status defaults to failed' => ['unknown_status', DirectChargeStatus::FAILED],
    ]);

    it('handles next_action with redirect_url', function () {
        $data = [
            'id' => 'dc_redirect',
            'status' => 'requires_action',
            'next_action' => [
                'type' => 'redirect_url',
                'redirect_url' => [
                    'url' => 'https://bank.example.com/3ds',
                ],
            ],
        ];

        $chargeData = DirectChargeData::fromApi($data);

        expect($chargeData)
            ->nextAction->toBeInstanceOf(NextActionData::class)
            ->nextAction->type->toBe(NextActionType::REDIRECT_URL)
            ->and($chargeData->getRedirectUrl())->toBe('https://bank.example.com/3ds');
    });

    it('handles next_action with requires_pin', function () {
        $data = [
            'id' => 'dc_pin',
            'status' => 'requires_action',
            'next_action' => [
                'type' => 'requires_pin',
                'requires_pin' => [
                    'message' => 'Please enter your card PIN',
                ],
            ],
        ];

        $chargeData = DirectChargeData::fromApi($data);

        expect($chargeData)
            ->nextAction->type->toBe(NextActionType::REQUIRES_PIN)
            ->nextAction->data->toBe(['message' => 'Please enter your card PIN']);
    });

    it('handles next_action with requires_otp', function () {
        $data = [
            'id' => 'dc_otp',
            'status' => 'requires_action',
            'next_action' => [
                'type' => 'requires_otp',
                'requires_otp' => [
                    'message' => 'Enter OTP sent to your phone',
                    'reference' => 'otp_ref_123',
                ],
            ],
        ];

        $chargeData = DirectChargeData::fromApi($data);

        expect($chargeData)
            ->nextAction->type->toBe(NextActionType::REQUIRES_OTP)
            ->and($chargeData->nextAction->getMessage())->toBe('Enter OTP sent to your phone');
    });

    it('handles empty next_action', function () {
        $data = [
            'id' => 'dc_no_action',
            'status' => 'succeeded',
            'next_action' => null,
        ];

        $chargeData = DirectChargeData::fromApi($data);

        expect($chargeData)
            ->nextAction->type->toBe(NextActionType::NONE)
            ->nextAction->data->toBeNull();
    });

    it('checks if charge is successful', function () {
        $successData = DirectChargeData::fromApi(['id' => 'dc_1', 'status' => 'succeeded']);
        $failedData = DirectChargeData::fromApi(['id' => 'dc_2', 'status' => 'failed']);
        $pendingData = DirectChargeData::fromApi(['id' => 'dc_3', 'status' => 'pending']);

        expect($successData->isSuccessful())->toBeTrue();
        expect($failedData->isSuccessful())->toBeFalse();
        expect($pendingData->isSuccessful())->toBeFalse();
    });

    it('checks if charge requires action', function () {
        $requiresActionData = DirectChargeData::fromApi([
            'id' => 'dc_action',
            'status' => 'requires_action',
            'next_action' => [
                'type' => 'requires_pin',
                'requires_pin' => ['message' => 'Enter PIN'],
            ],
        ]);
        $pendingData = DirectChargeData::fromApi(['id' => 'dc_pending', 'status' => 'pending']);

        expect($requiresActionData->requiresAction())->toBeTrue();
        expect($pendingData->requiresAction())->toBeFalse();
    });

    it('checks if charge is in terminal state', function () {
        $terminalStatuses = ['succeeded', 'failed', 'cancelled', 'timeout'];
        $nonTerminalStatuses = ['pending', 'requires_action'];

        foreach ($terminalStatuses as $status) {
            $chargeData = DirectChargeData::fromApi(['id' => 'dc_test', 'status' => $status]);
            expect($chargeData->isTerminal())->toBeTrue("Status '$status' should be terminal");
        }

        foreach ($nonTerminalStatuses as $status) {
            $chargeData = DirectChargeData::fromApi(['id' => 'dc_test', 'status' => $status]);
            expect($chargeData->isTerminal())->toBeFalse("Status '$status' should not be terminal");
        }
    });

    it('extracts customer email', function () {
        $withEmail = DirectChargeData::fromApi([
            'id' => 'dc_email',
            'customer' => ['email' => 'test@example.com'],
        ]);
        $withoutEmail = DirectChargeData::fromApi([
            'id' => 'dc_no_email',
            'customer' => ['name' => 'John'],
        ]);
        $noCustomer = DirectChargeData::fromApi(['id' => 'dc_no_customer']);

        expect($withEmail->getCustomerEmail())->toBe('test@example.com');
        expect($withoutEmail->getCustomerEmail())->toBeNull();
        expect($noCustomer->getCustomerEmail())->toBeNull();
    });

    it('extracts payment method type and id', function () {
        $withPaymentMethod = DirectChargeData::fromApi([
            'id' => 'dc_pm',
            'payment_method_details' => [
                'type' => 'card',
                'id' => 'pm_123',
            ],
        ]);
        $noPaymentMethod = DirectChargeData::fromApi(['id' => 'dc_no_pm']);

        expect($withPaymentMethod->getPaymentMethodType())->toBe('card');
        expect($withPaymentMethod->getPaymentMethodId())->toBe('pm_123');
        expect($noPaymentMethod->getPaymentMethodType())->toBeNull();
        expect($noPaymentMethod->getPaymentMethodId())->toBeNull();
    });

    it('extracts issuer response code and message', function () {
        $withIssuer = DirectChargeData::fromApi([
            'id' => 'dc_issuer',
            'issuer_response' => [
                'code' => '00',
                'message' => 'Approved',
            ],
        ]);
        $noIssuer = DirectChargeData::fromApi(['id' => 'dc_no_issuer']);

        expect($withIssuer->getIssuerResponseCode())->toBe('00');
        expect($withIssuer->getIssuerResponseMessage())->toBe('Approved');
        expect($noIssuer->getIssuerResponseCode())->toBeNull();
        expect($noIssuer->getIssuerResponseMessage())->toBeNull();
    });

    it('retrieves metadata by key with default', function () {
        $chargeData = DirectChargeData::fromApi([
            'id' => 'dc_meta',
            'meta' => [
                'order_id' => '12345',
                'user_id' => '67890',
            ],
        ]);

        expect($chargeData->getMeta('order_id'))->toBe('12345');
        expect($chargeData->getMeta('missing_key'))->toBeNull();
        expect($chargeData->getMeta('missing_key', 'default_value'))->toBe('default_value');
    });

    it('converts to array', function () {
        $chargeData = DirectChargeData::fromApi([
            'id' => 'dc_array',
            'amount' => 1000,
            'currency' => 'TZS',
            'reference' => 'ORDER-ARRAY',
            'status' => 'pending',
        ]);

        $array = $chargeData->toArray();

        expect($array)
            ->toHaveKey('id', 'dc_array')
            ->toHaveKey('amount', 1000.0)
            ->toHaveKey('currency', 'TZS')
            ->toHaveKey('reference', 'ORDER-ARRAY')
            ->toHaveKey('status', 'pending')
            ->toHaveKey('next_action')
            ->toHaveKey('customer')
            ->toHaveKey('payment_method_details')
            ->toHaveKey('meta')
            ->toHaveKey('created_at');
    });

    it('handles customer as string (customer_id)', function () {
        $chargeData = DirectChargeData::fromApi([
            'id' => 'dc_cust_id',
            'customer' => 'cust_string_123',
        ]);

        expect($chargeData->customerId)->toBe('cust_string_123');
        expect($chargeData->customer)->toBeNull();
    });

    it('handles created_datetime fallback', function () {
        $withCreatedDatetime = DirectChargeData::fromApi([
            'id' => 'dc_datetime',
            'created_datetime' => '2025-01-02T10:00:00Z',
        ]);

        expect($withCreatedDatetime->createdAt)->toBe('2025-01-02T10:00:00Z');
    });

    it('uses default values for missing fields', function () {
        $minimal = DirectChargeData::fromApi(['id' => 'dc_minimal']);

        expect($minimal)
            ->amount->toBe(0.0)
            ->currency->toBe('USD')
            ->reference->toBe('')
            ->status->toBe(DirectChargeStatus::FAILED);
    });
});
