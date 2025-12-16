<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Order;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Order\CreateOrderRequest;
use Gowelle\Flutterwave\Data\Order\UpdateOrderRequest;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Support\Facades\Validator;

class OrderApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the order API
     */
    protected string $endpoint = '/orders';

    /**
     * Create an order with validation
     */
    public function create(array $data): ApiResponse
    {
        $validatedData = $this->validateCreateData($data);

        return parent::create($validatedData);
    }

    /**
     * Create an order from DTO
     */
    public function createFromDto(CreateOrderRequest $request): ApiResponse
    {
        return parent::create($request->toApiPayload());
    }

    /**
     * Update an order with validation
     */
    public function update(string $id, array $data): ApiResponse
    {
        $validatedData = $this->validateUpdateData($data);

        return parent::update($id, $validatedData);
    }

    /**
     * Update an order from DTO
     */
    public function updateFromDto(string $id, UpdateOrderRequest $request): ApiResponse
    {
        return parent::update($id, $request->toApiPayload());
    }

    /**
     * Validate create order data
     */
    protected function validateCreateData(array $data): array
    {
        $validator = Validator::make($data, [
            'order_reference' => 'required|string',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'customer' => 'required|array',
            'customer.name' => 'required|string',
            'customer.email' => 'required|email',
            'customer.phone_number' => 'nullable|string',
            'items' => 'required|array',
            'items.*.name' => 'required|string',
            'items.*.quantity' => 'required|integer',
            'items.*.amount' => 'required|numeric',
        ]);

        return $validator->validate();
    }

    /**
     * Validate update order data
     */
    protected function validateUpdateData(array $data): array
    {
        $validator = Validator::make($data, [
            'order_reference' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'status' => 'nullable|string',
        ]);

        return $validator->validate();
    }
}
