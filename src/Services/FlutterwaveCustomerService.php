<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Contracts\CustomerServiceInterface;
use Gowelle\Flutterwave\Data\CustomerData;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveCustomerService implements CustomerServiceInterface
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Create a customer
     *
     * @param  array<string, mixed>  $data  Customer data
     *
     * @example
     * $customer = $customerService->create([
     *     'email' => 'customer@example.com',
     *     'name' => 'John Doe',
     *     'phone_number' => '+255712345678',
     * ]);
     */
    public function create(array $data): CustomerData
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::CUSTOMER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return CustomerData::fromApi($this->flutterwaveBaseService->create(FlutterwaveApi::CUSTOMER, $wavable, $data)->data);
    }

    /**
     * Get customers
     *
     * @param  array<string, mixed>  $data  Query parameters
     * @return CustomerData[]
     */
    public function list(array $data): array
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::CUSTOMER, $this->flutterwaveBaseService->getConfig()->isProduction());
        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::CUSTOMER, $wavable, $data);

        // Return empty array if no data is returned from API
        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return CustomerData::collection($response->data);
    }

    /**
     * Get a customer
     *
     * @param  string  $id  customer ID
     * @return CustomerData
     *
     */
    public function get(string $id): CustomerData
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::CUSTOMER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return CustomerData::fromApi($this->flutterwaveBaseService->retrieve(FlutterwaveApi::CUSTOMER, $wavable, $id)->data);
    }

    /**
     * Update a customer
     *
     * @param  string  $id  customer ID
     * @param  array<string, mixed>  $data  customer data
     *
     * @throws FlutterwaveApiException
     */
    public function update(string $id, array $data): CustomerData
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::CUSTOMER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return CustomerData::fromApi($this->flutterwaveBaseService->update(FlutterwaveApi::CUSTOMER, $wavable, $id, $data)->data);
    }

    /**
     * Search for customers by email
     *
     * @return CustomerData First matching customer
     *
     * @throws FlutterwaveApiException
     * @throws \RuntimeException If no customer found
     */
    public function search(string $email): CustomerData
    {
        $wavable = $this->buildWavable(['email' => $email], FlutterwaveApi::CUSTOMER, $this->flutterwaveBaseService->getConfig()->isProduction());
        $response = $this->flutterwaveBaseService->search(FlutterwaveApi::CUSTOMER, $wavable, ['email' => $email]);

        // API returns data as array of customers
        if ($response->data === null || ! \is_array($response->data) || empty($response->data)) {
            throw new \RuntimeException('No customer found with email: ' . $email);
        }

        // Return first matching customer
        return CustomerData::fromApi($response->data[0]);
    }
}
