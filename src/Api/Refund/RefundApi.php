<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Refund;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Support\Facades\Validator;

class RefundApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the refund API
     */
    protected string $endpoint = '/refunds';

    /**
     * Create a refund with validation
     */
    public function create(array $data): ApiResponse
    {
        $validatedData = $this->validateRefundData($data);

        return parent::create($validatedData);
    }

    /**
     * Retrieve a refund
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * List refunds
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Update a refund is not implemented
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Search for a refund is not implemented
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate refund data
     */
    protected function validateRefundData(array $data): array
    {
        $validator = Validator::make($data, [
            'charge_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        return $validator->validate();
    }
}
