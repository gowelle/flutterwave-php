<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Customer\CreateCustomerRequest;

describe('CreateCustomerRequest', function () {
    it('creates with required fields', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phoneNumber: '+255123456789',
        );

        expect($request)
            ->toBeInstanceOf(CreateCustomerRequest::class)
            ->email->toBe('john@example.com')
            ->firstName->toBe('John')
            ->lastName->toBe('Doe')
            ->phoneNumber->toBe('+255123456789')
            ->middleName->toBeNull();
    });

    it('creates with optional middle name', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phoneNumber: '+255123456789',
            middleName: 'Michael',
        );

        expect($request->middleName)->toBe('Michael');
    });

    it('converts to API payload', function () {
        $request = new CreateCustomerRequest(
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
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phoneNumber: '+255123456789',
            middleName: 'Michael',
        );

        $payload = $request->toApiPayload();

        expect($payload['name'])
            ->toHaveKey('first', 'John')
            ->toHaveKey('middle', 'Michael')
            ->toHaveKey('last', 'Doe');
    });

    it('excludes empty middle name from payload', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phoneNumber: '+255123456789',
            middleName: '',
        );

        $payload = $request->toApiPayload();

        expect($payload['name'])->not->toHaveKey('middle');
    });
});
