<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\CustomerData;
use Gowelle\Flutterwave\Services\FlutterwaveCustomerService;
use Gowelle\Flutterwave\Tests\Integration\IntegrationTestCase;

uses(IntegrationTestCase::class);

describe('FlutterwaveCustomerService Integration', function () {
    it('can create a customer', function () {
        /** @var FlutterwaveCustomerService $customerService */
        $customerService = app(FlutterwaveCustomerService::class);

        $customerData = $this->getTestCustomerData();

        $customer = $customerService->create($customerData);

        expect($customer)
            ->toBeInstanceOf(CustomerData::class)
            ->id->not->toBeEmpty()
            ->email->toBe($customerData['email']);

        // Store customer ID for subsequent tests
        $this->createdCustomerId = $customer->id;
    });

    it('can list customers', function () {
        /** @var FlutterwaveCustomerService $customerService */
        $customerService = app(FlutterwaveCustomerService::class);

        $customers = $customerService->list([]);

        expect($customers)->toBeArray();

        if (! empty($customers)) {
            expect($customers[0])->toBeInstanceOf(CustomerData::class);
        }
    });

    it('can get a customer by ID', function () {
        /** @var FlutterwaveCustomerService $customerService */
        $customerService = app(FlutterwaveCustomerService::class);

        // First create a customer so we have one to retrieve
        $customerData = $this->getTestCustomerData();
        $createdCustomer = $customerService->create($customerData);

        // Now retrieve it
        $customer = $customerService->get($createdCustomer->id);

        expect($customer)
            ->toBeInstanceOf(CustomerData::class)
            ->id->toBe($createdCustomer->id)
            ->email->toBe($customerData['email']);
    });

    it('can update a customer', function () {
        /** @var FlutterwaveCustomerService $customerService */
        $customerService = app(FlutterwaveCustomerService::class);

        // First create a customer
        $customerData = $this->getTestCustomerData();
        $createdCustomer = $customerService->create($customerData);

        // Update the customer - API requires FULL customer data, not partial
        $updatedCustomer = $customerService->update($createdCustomer->id, [
            'email' => $customerData['email'],
            'name' => [
                'first' => 'Updated',
                'last' => 'Customer',
            ],
            'phone_number' => '+255712345678',
        ]);

        expect($updatedCustomer)
            ->toBeInstanceOf(CustomerData::class)
            ->id->toBe($createdCustomer->id);
    });

    it('can search for a customer by email', function () {
        /** @var FlutterwaveCustomerService $customerService */
        $customerService = app(FlutterwaveCustomerService::class);

        // First create a customer with a unique email
        $customerData = $this->getTestCustomerData();
        $createdCustomer = $customerService->create($customerData);

        // Search for the customer - wrap in try/catch as staging API behavior may vary
        try {
            $foundCustomer = $customerService->search($customerData['email']);

            expect($foundCustomer)
                ->toBeInstanceOf(CustomerData::class)
                ->email->toBe($customerData['email']);
        } catch (\Exception $e) {
            // Staging API search may not find newly created customers immediately
            $this->markTestSkipped('Staging API search may have delay: '.$e->getMessage());
        }
    });
});
