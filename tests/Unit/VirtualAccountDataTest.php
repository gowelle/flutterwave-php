<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\VirtualAccount\CreateVirtualAccountRequestDTO;
use Gowelle\Flutterwave\Data\VirtualAccount\UpdateVirtualAccountRequestDTO;
use Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData;
use Gowelle\Flutterwave\Enums\VirtualAccountCurrency;
use Gowelle\Flutterwave\Enums\VirtualAccountStatus;
use Gowelle\Flutterwave\Enums\VirtualAccountType;
use Gowelle\Flutterwave\Enums\VirtualAccountUpdateAction;

describe('VirtualAccountData', function () {
    it('can be created from API response', function () {
        $apiResponse = [
            'id' => 'va_123',
            'amount' => 0,
            'account_number' => '7824822527',
            'reference' => 'test_ref_123',
            'account_bank_name' => 'WEMA BANK',
            'account_type' => 'static',
            'status' => 'active',
            'account_expiration_datetime' => '2025-12-31T23:59:59Z',
            'customer_id' => 'cus_123',
            'currency' => 'NGN',
            'customer_reference' => 'AEGP2345',
            'note' => 'Test account',
            'meta' => ['test' => 'data'],
            'created_datetime' => '2024-12-16T10:00:00Z',
        ];

        $data = VirtualAccountData::fromApi($apiResponse);

        expect($data->id)->toBe('va_123');
        expect($data->amount)->toBe(0.0);
        expect($data->accountNumber)->toBe('7824822527');
        expect($data->reference)->toBe('test_ref_123');
        expect($data->accountBankName)->toBe('WEMA BANK');
        expect($data->accountType)->toEqual(VirtualAccountType::STATIC);
        expect($data->status)->toEqual(VirtualAccountStatus::ACTIVE);
        expect($data->currency)->toEqual(VirtualAccountCurrency::NGN);
        expect($data->note)->toBe('Test account');
        expect($data->customerId)->toBe('cus_123');
    });

    it('can create collection from API response', function () {
        $apiResponses = [
            [
                'id' => 'va_123',
                'amount' => 0,
                'account_number' => '7824822527',
                'reference' => 'test_ref_123',
                'account_bank_name' => 'WEMA BANK',
                'account_type' => 'static',
                'status' => 'active',
                'account_expiration_datetime' => '2025-12-31T23:59:59Z',
                'customer_id' => 'cus_123',
                'currency' => 'NGN',
                'created_datetime' => '2024-12-16T10:00:00Z',
            ],
            [
                'id' => 'va_456',
                'amount' => 5000,
                'account_number' => '7824822528',
                'reference' => 'test_ref_456',
                'account_bank_name' => 'WEMA BANK',
                'account_type' => 'dynamic',
                'status' => 'inactive',
                'account_expiration_datetime' => '2025-12-25T23:59:59Z',
                'customer_id' => 'cus_456',
                'currency' => 'GHS',
                'created_datetime' => '2024-12-15T10:00:00Z',
            ],
        ];

        $collection = VirtualAccountData::collection($apiResponses);

        expect($collection)->toHaveCount(2);
        expect($collection[0]->id)->toBe('va_123');
        expect($collection[1]->id)->toBe('va_456');
    });

    it('converts to array correctly', function () {
        $data = new VirtualAccountData(
            id: 'va_123',
            amount: 0.0,
            accountNumber: '7824822527',
            reference: 'test_ref_123',
            accountBankName: 'WEMA BANK',
            accountType: VirtualAccountType::STATIC,
            status: VirtualAccountStatus::ACTIVE,
            accountExpirationDatetime: '2025-12-31T23:59:59Z',
            note: 'Test note',
            customerId: 'cus_123',
            currency: VirtualAccountCurrency::NGN,
            customerReference: 'AEGP2345',
            meta: ['key' => 'value'],
            createdDatetime: '2024-12-16T10:00:00Z',
        );

        $array = $data->toArray();

        expect($array['id'])->toBe('va_123');
        expect($array['account_number'])->toBe('7824822527');
        expect($array['account_type'])->toBe('static');
        expect($array['status'])->toBe('active');
        expect($array['currency'])->toBe('NGN');
    });

    it('can check if account is active', function () {
        $active = new VirtualAccountData(
            id: 'va_123',
            amount: 0,
            accountNumber: '7824822527',
            reference: 'test_ref_123',
            accountBankName: 'WEMA BANK',
            accountType: VirtualAccountType::STATIC,
            status: VirtualAccountStatus::ACTIVE,
            accountExpirationDatetime: '2025-12-31T23:59:59Z',
        );

        $inactive = new VirtualAccountData(
            id: 'va_456',
            amount: 0,
            accountNumber: '7824822528',
            reference: 'test_ref_456',
            accountBankName: 'WEMA BANK',
            accountType: VirtualAccountType::STATIC,
            status: VirtualAccountStatus::INACTIVE,
            accountExpirationDatetime: '2025-12-31T23:59:59Z',
        );

        expect($active->isActive())->toBeTrue();
        expect($inactive->isActive())->toBeFalse();
    });

    it('can check if account is static', function () {
        $static = new VirtualAccountData(
            id: 'va_123',
            amount: 0,
            accountNumber: '7824822527',
            reference: 'test_ref_123',
            accountBankName: 'WEMA BANK',
            accountType: VirtualAccountType::STATIC,
            status: VirtualAccountStatus::ACTIVE,
            accountExpirationDatetime: '2025-12-31T23:59:59Z',
        );

        $dynamic = new VirtualAccountData(
            id: 'va_456',
            amount: 100,
            accountNumber: '7824822528',
            reference: 'test_ref_456',
            accountBankName: 'WEMA BANK',
            accountType: VirtualAccountType::DYNAMIC,
            status: VirtualAccountStatus::ACTIVE,
            accountExpirationDatetime: '2025-12-31T23:59:59Z',
        );

        expect($static->isStatic())->toBeTrue();
        expect($dynamic->isStatic())->toBeFalse();
    });

    it('can check if account is expired', function () {
        $expired = new VirtualAccountData(
            id: 'va_123',
            amount: 0,
            accountNumber: '7824822527',
            reference: 'test_ref_123',
            accountBankName: 'WEMA BANK',
            accountType: VirtualAccountType::DYNAMIC,
            status: VirtualAccountStatus::ACTIVE,
            accountExpirationDatetime: '2020-01-01T00:00:00Z',
        );

        $notExpired = new VirtualAccountData(
            id: 'va_456',
            amount: 100,
            accountNumber: '7824822528',
            reference: 'test_ref_456',
            accountBankName: 'WEMA BANK',
            accountType: VirtualAccountType::DYNAMIC,
            status: VirtualAccountStatus::ACTIVE,
            accountExpirationDatetime: '2099-12-31T23:59:59Z',
        );

        expect($expired->isExpired())->toBeTrue();
        expect($notExpired->isExpired())->toBeFalse();
    });

    it('handles empty expiration datetime', function () {
        $data = new VirtualAccountData(
            id: 'va_123',
            amount: 0,
            accountNumber: '7824822527',
            reference: 'test_ref_123',
            accountBankName: 'WEMA BANK',
            accountType: VirtualAccountType::STATIC,
            status: VirtualAccountStatus::ACTIVE,
            accountExpirationDatetime: '',
        );

        expect($data->isExpired())->toBeFalse();
    });
});

describe('CreateVirtualAccountRequestDTO', function () {
    it('can be created from array', function () {
        $data = CreateVirtualAccountRequestDTO::fromArray([
            'reference' => 'test_ref_123',
            'customer_id' => 'cus_123',
            'amount' => 0,
            'currency' => 'NGN',
            'account_type' => 'static',
            'narration' => 'Test account',
            'bvn' => '12345678901',
        ]);

        expect($data->reference)->toBe('test_ref_123');
        expect($data->customerId)->toBe('cus_123');
        expect($data->amount)->toBe(0.0);
        expect($data->currency)->toEqual(VirtualAccountCurrency::NGN);
        expect($data->accountType)->toEqual(VirtualAccountType::STATIC);
    });

    it('converts to API request format', function () {
        $dto = new CreateVirtualAccountRequestDTO(
            reference: 'test_ref_123',
            customerId: 'cus_123',
            amount: 0,
            currency: VirtualAccountCurrency::NGN,
            accountType: VirtualAccountType::STATIC,
            narration: 'Test account',
            bvn: '12345678901',
            meta: ['key' => 'value'],
        );

        $array = $dto->toArray();

        expect($array['reference'])->toBe('test_ref_123');
        expect($array['customer_id'])->toBe('cus_123');
        expect($array['currency'])->toBe('NGN');
        expect($array['account_type'])->toBe('static');
        expect($array['narration'])->toBe('Test account');
        expect($array['bvn'])->toBe('12345678901');
    });

    it('excludes null values from request array', function () {
        $dto = new CreateVirtualAccountRequestDTO(
            reference: 'test_ref_123',
            customerId: 'cus_123',
            amount: 0,
            currency: VirtualAccountCurrency::NGN,
            accountType: VirtualAccountType::STATIC,
            expiry: null,
            narration: null,
        );

        $array = $dto->toArray();

        expect(isset($array['expiry']))->toBeFalse();
        expect(isset($array['narration']))->toBeFalse();
        expect(isset($array['reference']))->toBeTrue();
    });

    it('handles enum objects in fromArray', function () {
        $data = CreateVirtualAccountRequestDTO::fromArray([
            'reference' => 'test_ref_123',
            'customer_id' => 'cus_123',
            'amount' => 100,
            'currency' => VirtualAccountCurrency::GHS,
            'account_type' => VirtualAccountType::DYNAMIC,
        ]);

        expect($data->currency)->toEqual(VirtualAccountCurrency::GHS);
        expect($data->accountType)->toEqual(VirtualAccountType::DYNAMIC);
    });
});

describe('UpdateVirtualAccountRequestDTO', function () {
    it('can create BVN update request', function () {
        $dto = UpdateVirtualAccountRequestDTO::forBvnUpdate('12345678901');

        expect($dto->actionType)->toEqual(VirtualAccountUpdateAction::UPDATE_BVN);
        expect($dto->bvn)->toBe('12345678901');
        expect($dto->status)->toBeNull();
    });

    it('can create status update request', function () {
        $dto = UpdateVirtualAccountRequestDTO::forStatusUpdate(VirtualAccountStatus::INACTIVE);

        expect($dto->actionType)->toEqual(VirtualAccountUpdateAction::UPDATE_STATUS);
        expect($dto->status)->toEqual(VirtualAccountStatus::INACTIVE);
        expect($dto->bvn)->toBeNull();
    });

    it('can be created from array', function () {
        $data = UpdateVirtualAccountRequestDTO::fromArray([
            'action_type' => 'update_bvn',
            'bvn' => '12345678901',
            'meta' => ['key' => 'value'],
        ]);

        expect($data->actionType)->toEqual(VirtualAccountUpdateAction::UPDATE_BVN);
        expect($data->bvn)->toBe('12345678901');
        expect($data->meta)->toEqual(['key' => 'value']);
    });

    it('converts to API request format', function () {
        $dto = new UpdateVirtualAccountRequestDTO(
            actionType: VirtualAccountUpdateAction::UPDATE_STATUS,
            status: VirtualAccountStatus::INACTIVE,
            meta: ['reason' => 'deactivation'],
        );

        $array = $dto->toArray();

        expect($array['action_type'])->toBe('update_status');
        expect($array['status'])->toBe('inactive');
        expect($array['meta'])->toEqual(['reason' => 'deactivation']);
    });

    it('excludes null values from request array', function () {
        $dto = new UpdateVirtualAccountRequestDTO(
            actionType: VirtualAccountUpdateAction::UPDATE_BVN,
            bvn: '12345678901',
        );

        $array = $dto->toArray();

        expect(isset($array['status']))->toBeFalse();
        expect($array['action_type'])->toBe('update_bvn');
    });
});
