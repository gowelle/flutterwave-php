<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Charge;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Support\Facades\Validator;

class ChargeApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the charge API
     */
    protected string $endpoint = '/charges';

    /**
     * Create a charge with validation
     */
    public function create(array $data): ApiResponse
    {
        $validatedData = $this->validateCreateData($data);

        return parent::create($validatedData);
    }

    /**
     * Search for a charge
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        throw new Exception('Charge search method not implemented');
    }

    /**
     * Validate charge creation data
     */
    protected function validateCreateData(array $data): array
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'reference' => 'required|string',
            'customer_id' => 'required|string',
            'redirect_url' => 'required|url',
            'payment_method_id' => 'required|string',
            'order_id' => 'required|string',
        ]);

        return $validator->validate();
    }
}
