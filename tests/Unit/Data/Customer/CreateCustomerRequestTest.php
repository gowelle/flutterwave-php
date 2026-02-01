<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\Customer\CreateCustomerRequest;

describe('CreateCustomerRequest', function () {
    it('creates with required fields', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phone: ['country_code' => 'TZA', 'number' => '712345678'],
        );

        expect($request)
            ->toBeInstanceOf(CreateCustomerRequest::class)
            ->email->toBe('john@example.com')
            ->firstName->toBe('John')
            ->lastName->toBe('Doe')
            ->phone->toBe(['country_code' => 'TZA', 'number' => '712345678'])
            ->middleName->toBeNull();
    });

    it('creates with optional middle name', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phone: ['country_code' => 'TZA', 'number' => '712345678'],
            middleName: 'Michael',
        );

        expect($request->middleName)->toBe('Michael');
    });

    it('converts to API payload', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            firstName: 'John',
            lastName: 'Doe',
            phone: ['country_code' => 'TZA', 'number' => '712345678'],
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('email', 'john@example.com')
            ->toHaveKey('phone')
            ->toHaveKey('name');

        expect($payload['phone'])
            ->toHaveKey('country_code', 'TZA')
            ->toHaveKey('number', '712345678');

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
            phone: ['country_code' => 'TZA', 'number' => '712345678'],
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
            phone: ['country_code' => 'TZA', 'number' => '712345678'],
            middleName: '',
        );

        $payload = $request->toApiPayload();

        expect($payload['name'])->not->toHaveKey('middle');
    });

    it('creates with email only per v4 API', function () {
        $request = new CreateCustomerRequest(email: 'minimal@example.com');

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('email', 'minimal@example.com')
            ->toHaveCount(1);
    });

    it('includes address in payload when provided', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            address: [
                'line1' => '221B Baker Street',
                'line2' => 'Flat 2',
                'city' => 'London',
                'state' => 'England',
                'postal_code' => 'NW1 6XE',
                'country' => 'GB',
            ],
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('email', 'john@example.com')
            ->toHaveKey('address');

        expect($payload['address'])
            ->toHaveKey('line1', '221B Baker Street')
            ->toHaveKey('city', 'London')
            ->toHaveKey('country', 'GB');
    });

    it('includes phone object in payload when provided', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            phone: ['country_code' => 'USA', 'number' => '2025551234'],
        );

        $payload = $request->toApiPayload();

        expect($payload)
            ->toHaveKey('phone')
            ->and($payload['phone'])->toBe([
                'country_code' => 'USA',
                'number' => '2025551234',
            ]);
    });

    it('omits phone from payload when country_code or number is empty', function () {
        $request = new CreateCustomerRequest(
            email: 'john@example.com',
            phone: ['country_code' => 'TZA', 'number' => ''],
        );

        $payload = $request->toApiPayload();

        expect($payload)->not->toHaveKey('phone');
    });
});
