<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\CustomerData;

describe('CustomerData', function () {
    it('creates from minimal API response', function () {
        $data = [
            'id' => 'cust_123',
            'email' => 'customer@example.com',
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData)
            ->toBeInstanceOf(CustomerData::class)
            ->id->toBe('cust_123')
            ->email->toBe('customer@example.com')
            ->name->toBeNull()
            ->phoneNumber->toBeNull()
            ->address->toBeNull()
            ->createdAt->toBeNull();
    });

    it('creates from full API response', function () {
        $data = [
            'id' => 'cust_456',
            'email' => 'john.doe@example.com',
            'name' => [
                'first' => 'John',
                'middle' => 'Michael',
                'last' => 'Doe',
            ],
            'phone_number' => '+255123456789',
            'address' => [
                'line1' => '123 Main St',
                'line2' => 'Suite 100',
                'city' => 'Dar es Salaam',
                'state' => 'Dar es Salaam',
                'postal_code' => '11101',
                'country' => 'TZ',
            ],
            'created_at' => '2025-01-01T12:00:00Z',
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData)
            ->id->toBe('cust_456')
            ->email->toBe('john.doe@example.com')
            ->name->toBe(['first' => 'John', 'middle' => 'Michael', 'last' => 'Doe'])
            ->phoneNumber->toBe('+255123456789')
            ->createdAt->toBe('2025-01-01T12:00:00Z');

        expect($customerData->address)
            ->toHaveKey('line1', '123 Main St')
            ->toHaveKey('line2', 'Suite 100')
            ->toHaveKey('city', 'Dar es Salaam')
            ->toHaveKey('country', 'TZ');
    });

    it('handles legacy string name format', function () {
        $data = [
            'id' => 'cust_legacy',
            'email' => 'legacy@example.com',
            'name' => 'John Doe Legacy',
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData->name)->toBe([
            'first' => 'John Doe Legacy',
            'middle' => null,
            'last' => '',
        ]);
    });

    it('handles phonenumber field (alternative snake_case)', function () {
        $data = [
            'id' => 'cust_phone',
            'email' => 'phone@example.com',
            'phonenumber' => '+1234567890',
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData->phoneNumber)->toBe('+1234567890');
    });

    it('handles phone object format', function () {
        $data = [
            'id' => 'cust_phone_obj',
            'email' => 'phone@example.com',
            'phone' => [
                'country_code' => '+255',
                'number' => '123456789',
            ],
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData->phoneNumber)->toBe('+255123456789');
    });

    it('handles phone object without country code', function () {
        $data = [
            'id' => 'cust_phone_no_cc',
            'email' => 'phone@example.com',
            'phone' => [
                'number' => '123456789',
            ],
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData->phoneNumber)->toBe('123456789');
    });

    it('handles created_datetime fallback', function () {
        $data = [
            'id' => 'cust_datetime',
            'email' => 'datetime@example.com',
            'created_datetime' => '2025-02-01T10:00:00Z',
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData->createdAt)->toBe('2025-02-01T10:00:00Z');
    });

    it('handles missing address fields gracefully', function () {
        $data = [
            'id' => 'cust_partial_addr',
            'email' => 'partial@example.com',
            'address' => [
                'line1' => '123 Main St',
                'city' => 'Nairobi',
            ],
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData->address)
            ->toHaveKey('line1', '123 Main St')
            ->toHaveKey('line2', null)
            ->toHaveKey('city', 'Nairobi')
            ->toHaveKey('state', '')
            ->toHaveKey('postal_code', '')
            ->toHaveKey('country', '');
    });

    it('handles missing name parts gracefully', function () {
        $data = [
            'id' => 'cust_partial_name',
            'email' => 'partial@example.com',
            'name' => [
                'first' => 'John',
            ],
        ];

        $customerData = CustomerData::fromApi($data);

        expect($customerData->name)->toBe([
            'first' => 'John',
            'middle' => null,
            'last' => '',
        ]);
    });

    it('creates collection from array', function () {
        $items = [
            ['id' => 'cust_1', 'email' => 'one@example.com'],
            ['id' => 'cust_2', 'email' => 'two@example.com'],
            ['id' => 'cust_3', 'email' => 'three@example.com'],
        ];

        $collection = CustomerData::collection($items);

        expect($collection)
            ->toHaveCount(3)
            ->each->toBeInstanceOf(CustomerData::class);

        expect($collection[0]->email)->toBe('one@example.com');
        expect($collection[1]->email)->toBe('two@example.com');
        expect($collection[2]->email)->toBe('three@example.com');
    });

    it('returns full name as string', function () {
        $fullName = CustomerData::fromApi([
            'id' => 'cust_full',
            'email' => 'full@example.com',
            'name' => ['first' => 'John', 'middle' => 'Michael', 'last' => 'Doe'],
        ]);

        expect($fullName->getFullName())->toBe('John Michael Doe');
    });

    it('returns full name without middle name', function () {
        $noMiddle = CustomerData::fromApi([
            'id' => 'cust_no_mid',
            'email' => 'nomid@example.com',
            'name' => ['first' => 'Jane', 'last' => 'Smith'],
        ]);

        expect($noMiddle->getFullName())->toBe('Jane Smith');
    });

    it('returns null for full name when no name provided', function () {
        $noName = CustomerData::fromApi([
            'id' => 'cust_no_name',
            'email' => 'noname@example.com',
        ]);

        expect($noName->getFullName())->toBeNull();
    });

    it('converts to array', function () {
        $customerData = CustomerData::fromApi([
            'id' => 'cust_array',
            'email' => 'array@example.com',
            'name' => ['first' => 'Test', 'last' => 'User'],
            'phone_number' => '+123456789',
            'address' => [
                'line1' => '123 St',
                'city' => 'City',
                'state' => 'State',
                'postal_code' => '12345',
                'country' => 'US',
            ],
            'created_at' => '2025-01-01T00:00:00Z',
        ]);

        $array = $customerData->toArray();

        expect($array)
            ->toHaveKey('id', 'cust_array')
            ->toHaveKey('email', 'array@example.com')
            ->toHaveKey('name')
            ->toHaveKey('phone_number', '+123456789')
            ->toHaveKey('address')
            ->toHaveKey('created_at', '2025-01-01T00:00:00Z');
    });

    it('converts to request array format', function () {
        $customerData = CustomerData::fromApi([
            'id' => 'cust_req',
            'email' => 'request@example.com',
            'name' => ['first' => 'Test', 'middle' => '', 'last' => 'User'],
            'phone_number' => '+987654321',
            'address' => [
                'line1' => '456 Avenue',
                'line2' => '',
                'city' => 'Town',
                'state' => '',
                'postal_code' => '54321',
                'country' => 'UK',
            ],
        ]);

        $requestArray = $customerData->toRequestArray();

        expect($requestArray)
            ->toHaveKey('email', 'request@example.com')
            ->toHaveKey('name')
            ->toHaveKey('phone_number', '+987654321')
            ->toHaveKey('address');

        // Empty values should be filtered out
        expect($requestArray['name'])->not->toHaveKey('middle');
        expect($requestArray['address'])->not->toHaveKey('line2');
        expect($requestArray['address'])->not->toHaveKey('state');
    });

    it('excludes null values from request array', function () {
        $minimal = CustomerData::fromApi([
            'id' => 'cust_minimal',
            'email' => 'minimal@example.com',
        ]);

        $requestArray = $minimal->toRequestArray();

        expect($requestArray)->toHaveKey('email', 'minimal@example.com');
        expect($requestArray)->not->toHaveKey('name');
        expect($requestArray)->not->toHaveKey('phone_number');
        expect($requestArray)->not->toHaveKey('address');
    });
});
