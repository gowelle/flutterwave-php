<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Customer\UpdateCustomerRequest;

describe('UpdateCustomerRequest', function () {
    it('creates with required fields', function () {
        $request = new UpdateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phoneNumber: '+255123456789',
        );

        expect($request)
            ->toBeInstanceOf(UpdateCustomerRequest::class)
            ->email->toBe('john@example.com')
            ->firstName->toBe('John')
            ->lastName->toBe('Doe')
            ->phoneNumber->toBe('+255123456789')
            ->middleName->toBeNull();
    });

    it('creates with optional middle name', function () {
        $request = new UpdateCustomerRequest(
            email: 'updated@example.com',
            firstName: 'Jane',
            lastName: 'Smith',
            phoneNumber: '+255987654321',
            middleName: 'Elizabeth',
        );

        expect($request->middleName)->toBe('Elizabeth');
    });

    it('converts to API payload', function () {
        $request = new UpdateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phoneNumber: '+255123456789',
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('email', 'john@example.com')
            ->toHaveKey('phone_number', '+255123456789')
            ->toHaveKey('name');

        expect($payload['name'])
            ->toHaveKey('first', 'John')
            ->toHaveKey('last', 'Doe')
            ->not->toHaveKey('middle');
    });

    it('includes middle name in payload when provided', function () {
        $request = new UpdateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phoneNumber: '+255123456789',
            middleName: 'Michael',
        );

        $payload = $request->toApiPayload();

        expect($payload['name'])
            ->toHaveKey('middle', 'Michael');
    });
});
