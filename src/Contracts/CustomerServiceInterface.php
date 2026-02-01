<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Contracts;

use Gowelle\Flutterwave\Data\Customer\CreateCustomerRequest;
use Gowelle\Flutterwave\Data\Customer\SearchCustomerRequest;
use Gowelle\Flutterwave\Data\Customer\UpdateCustomerRequest;
use Gowelle\Flutterwave\Data\CustomerData;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;

/**
 * Customer Service Interface
 *
 * Contract for managing customer operations in Flutterwave.
 */
interface CustomerServiceInterface
{
    /**
     * Create a customer
     *
     * @param  array<string, mixed>  $data
     *
     * @throws FlutterwaveApiException
     */
    public function create(array $data): CustomerData;

    /**
     * Create a customer from DTO
     *
     * @throws FlutterwaveApiException
     */
    public function createFromDto(CreateCustomerRequest $request): CustomerData;

    /**
     * Update a customer
     *
     * @param  array<string, mixed>  $data
     *
     * @throws FlutterwaveApiException
     */
    public function update(string $id, array $data): CustomerData;

    /**
     * Update a customer from DTO
     *
     * @throws FlutterwaveApiException
     */
    public function updateFromDto(string $id, UpdateCustomerRequest $request): CustomerData;

    /**
     * Get a customer
     *
     * @param  string  $id  Customer ID
     */
    public function get(string $id): CustomerData;

    /**
     * List customers
     *
     * @param  array<string, mixed>  $data
     * @return CustomerData[]
     *
     * @throws FlutterwaveApiException
     */
    public function list(array $data): array;

    /**
     * Search for customers
     *
     * @param  string  $email  Email to search for
     *
     * @throws FlutterwaveApiException
     */
    public function search(string $email): CustomerData;

    /**
     * Search for a customer from DTO
     *
     * @throws FlutterwaveApiException
     */
    public function searchFromDto(SearchCustomerRequest $request): CustomerData;
}
