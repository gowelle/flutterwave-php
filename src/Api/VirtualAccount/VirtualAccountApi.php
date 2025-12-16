<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\VirtualAccount;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Enums\VirtualAccountCurrency;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

/**
 * Virtual Account API
 *
 * Manages virtual bank accounts for receiving payments.
 * Supports creating, listing, retrieving, and updating virtual accounts.
 */
class VirtualAccountApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the virtual account API
     */
    protected string $endpoint = '/virtual-accounts';

    /**
     * Create a virtual account with validation
     */
    public function create(array $data): ApiResponse
    {
        $validatedData = $this->validateCreateData($data);

        return parent::create($validatedData);
    }

    /**
     * Retrieve a virtual account
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * List all virtual accounts
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * List virtual accounts with query parameters (pagination and filtering)
     *
     * @param  array<string, mixed>  $params  Query parameters (page, size, from, to, reference)
     */
    public function listWithParams(array $params): ApiResponse
    {
        return $this->executeWithRetry(function () use ($params) {
            try {
                $filteredParams = array_filter($params);

                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->buildApiSpecificBaseUrl(), $filteredParams)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                $this->logApiError('GET', $this->buildApiSpecificBaseUrl(), $e);

                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Update a virtual account with validation
     */
    public function update(string $id, array $data): ApiResponse
    {
        $validatedData = $this->validateUpdateData($data);

        return parent::update($id, $validatedData);
    }

    /**
     * Search is not implemented for virtual accounts
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate create request data
     */
    protected function validateCreateData(array $data): array
    {
        $validator = Validator::make($data, [
            'reference' => 'required|string|min:6|max:42',
            'customer_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|in:NGN,GHS,EGP,KES',
            'account_type' => 'required|string|in:static,dynamic',
            'expiry' => 'nullable|integer|min:60|max:31536000',
            'meta' => 'nullable|array',
            'narration' => 'nullable|string',
            'bvn' => 'nullable|string',
            'nin' => 'nullable|string',
            'customer_account_number' => 'nullable|string',
        ]);

        $validated = $validator->validate();

        // Additional validation: customer_account_number is required for EGP and KES
        $currency = VirtualAccountCurrency::fromApiResponse($validated['currency']);
        if ($currency->requiresAccountNumber() && empty($validated['customer_account_number'] ?? null)) {
            throw new \Illuminate\Validation\ValidationException(
                Validator::make($data, [
                    'customer_account_number' => 'required_if:currency,EGP,KES',
                ])
            );
        }

        return $validated;
    }

    /**
     * Validate update request data
     */
    protected function validateUpdateData(array $data): array
    {
        $validator = Validator::make($data, [
            'action_type' => 'required|string|in:update_bvn,update_status',
            'status' => 'nullable|string|in:inactive',
            'bvn' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $validated = $validator->validate();

        // Conditional validation based on action_type
        $actionType = $validated['action_type'] ?? null;

        if ($actionType === 'update_bvn' && empty($validated['bvn'] ?? null)) {
            throw new \Illuminate\Validation\ValidationException(
                Validator::make($data, [
                    'bvn' => 'required_if:action_type,update_bvn',
                ])
            );
        }

        if ($actionType === 'update_status' && empty($validated['status'] ?? null)) {
            throw new \Illuminate\Validation\ValidationException(
                Validator::make($data, [
                    'status' => 'required_if:action_type,update_status',
                ])
            );
        }

        return $validated;
    }

    /**
     * Validate list query parameters
     */
    protected function validateListParams(array $params): array
    {
        $validator = Validator::make($params, [
            'from' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'to' => 'nullable|date_format:Y-m-d\TH:i:s\Z',
            'page' => 'nullable|integer|min:1',
            'size' => 'nullable|integer|min:10|max:50',
            'reference' => 'nullable|string',
        ]);

        return $validator->validate();
    }
}
