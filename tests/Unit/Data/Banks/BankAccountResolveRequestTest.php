<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Banks\BankAccountResolveRequest;

describe('BankAccountResolveRequest', function () {
    it('creates with required fields and default currency', function () {
        $request = new BankAccountResolveRequest(
            bankCode: '044',
            accountNumber: '0123456789',
        );

        expect($request)
            ->toBeInstanceOf(BankAccountResolveRequest::class)
            ->bankCode->toBe('044')
            ->accountNumber->toBe('0123456789')
            ->currency->toBe('NGN');
    });

    it('creates with custom currency', function () {
        $request = new BankAccountResolveRequest(
            bankCode: '058',
            accountNumber: '9876543210',
            currency: 'USD',
        );

        expect($request->currency)->toBe('USD');
    });

    it('converts to API payload', function () {
        $request = new BankAccountResolveRequest(
            bankCode: '044',
            accountNumber: '0123456789',
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('bank_code', '044')
            ->toHaveKey('account_number', '0123456789')
            ->toHaveKey('currency', 'NGN');
    });

    it('uppercases currency in API payload', function () {
        $request = new BankAccountResolveRequest(
            bankCode: '044',
            accountNumber: '0123456789',
            currency: 'usd',
        );

        $payload = $request->toApiPayload();

        expect($payload['currency'])->toBe('USD');
    });

    it('handles mixed case currency', function () {
        $request = new BankAccountResolveRequest(
            bankCode: '044',
            accountNumber: '0123456789',
            currency: 'Ngn',
        );

        $payload = $request->toApiPayload();

        expect($payload['currency'])->toBe('NGN');
    });
});
