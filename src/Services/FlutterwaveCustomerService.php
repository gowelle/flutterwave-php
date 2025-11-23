<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\CustomerData;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveCustomerService
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Create a customer
     */
    public function create(array $data): CustomerData
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::CUSTOMER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return CustomerData::fromApi($this->flutterwaveBaseService->create(FlutterwaveApi::CUSTOMER, $wavable, $data)->data);
    }

    /**
     * Get customers
     *
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
     * @param  array  $data  customer data
     * @return CustomerData
     *
     * @throws FlutterwaveApiException
     */
    public function update(string $id, array $data): CustomerData
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::CUSTOMER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return CustomerData::fromApi($this->flutterwaveBaseService->update(FlutterwaveApi::CUSTOMER, $wavable, $id, $data)->data);
    }

    /**
     * Search for customers
     *
     * @return CustomerData[]
     *
     * @throws FlutterwaveApiException
     */
    public function search(string $email): CustomerData
    {
        $wavable = $this->buildWavable(['email' => $email], FlutterwaveApi::CUSTOMER, $this->flutterwaveBaseService->getConfig()->isProduction());
        return CustomerData::fromApi($this->flutterwaveBaseService->search(FlutterwaveApi::CUSTOMER, $wavable, ['email' => $email])->data);
    }
}
