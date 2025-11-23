<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\PaymentMethods;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Gowelle\Flutterwave\Infrastructure\PaymentMethodType;

class PaymentMethodsApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the payment methods API
     */
    protected string $endpoint = '/payment-methods';

    /**
     * Create a payment method with validation
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        // Ensure customer_id and type are present before validation
        if (! isset($data['customer_id'])) {
            throw new Exception('customer_id is required for creating payment methods');
        }

        if (! isset($data['type'])) {
            throw new Exception('type is required for creating payment methods');
        }

        $validatedData = $this->validateCreateData($data);

        return parent::create($validatedData);
    }

    /**
     * Update a payment method
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        throw new ApiMethodNotImplementedException('Payment methods update method not implemented');
    }

    /**
     * Search for a payment method
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        throw new ApiMethodNotImplementedException('Payment methods search method not implemented');
    }

    protected function validateCreateData(array $data): array
    {
        return PaymentMethodType::from($data['type'])->validateCreateData($data);
    }
}
