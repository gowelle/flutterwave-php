<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Customer;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class CustomerApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the customer API
     */
    protected string $endpoint = '/customers';

    /**
     * Create a customer with validation
     */
    public function create(array $data): ApiResponse
    {
        $validatedData = $this->validateCreateData($data);

        return parent::create($validatedData);
    }

    /**
     * Update a customer with validation
     */
    public function update(string $id, array $data): ApiResponse
    {
        $validatedData = $this->validateUpdateData($data);

        return parent::update($id, $validatedData);
    }

    /**
     * Search for a customer
     *
     * @return ?object
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        try {
            $normalizedData = $this->validateSearchData($data);

            $response = Http::withToken($this->getAccessToken())
                ->withHeaders($this->getHeaders()->toArray())
                ->post($this->buildApiSpecificBaseUrl(), $normalizedData)
                ->json();

            return ApiResponse::fromArray($response);
        } catch (Exception $exception) {
            $this->logApiError('SEARCH', $this->buildApiSpecificBaseUrl(), $exception);

            throw $this->createApiException($exception);
        }
    }

    public function validateUpdateData(array $data): array
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'name.first' => 'required|string',
            'name.middle' => 'nullable|string',
            'name.last' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        return $validator->validate();
    }

    protected function validateSearchData(array $data): array
    {
        $validator = Validator::make($data, [
            'email' => 'string|email',
        ]);

        return $validator->validate();
    }

    protected function validateCreateData(array $data): array
    {
        $validator = Validator::make($data, [
            'email' => 'required|email',
            'name.first' => 'required|string',
            'name.middle' => 'nullable|string',
            'name.last' => 'required|string',
            'phone_number' => 'required|string',
        ]);

        return $validator->validate();
    }
}
