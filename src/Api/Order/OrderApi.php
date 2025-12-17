<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Order;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Order\CreateOrchestratorOrderRequest;
use Gowelle\Flutterwave\Data\Order\CreateOrderRequest;
use Gowelle\Flutterwave\Data\Order\ListOrdersRequest;
use Gowelle\Flutterwave\Data\Order\UpdateOrderRequest;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class OrderApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the order API
     */
    protected string $endpoint = '/orders';

    /**
     * The orchestrator endpoint for creating orders with full customer/payment method details.
     */
    protected string $orchestratorEndpoint = '/orchestration/direct-orders';

    /**
     * List orders with filter parameters.
     */
    public function listWithFilters(ListOrdersRequest $request): ApiResponse
    {
        return parent::listWithParams($request->toQueryParams());
    }

    /**
     * Create an order with validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ApiResponse
    {
        $validatedData = $this->validateCreateData($data);

        return parent::create($validatedData);
    }

    /**
     * Create an order from DTO.
     */
    public function createFromDto(CreateOrderRequest $request): ApiResponse
    {
        return parent::create($request->toApiPayload());
    }

    /**
     * Create an order using the orchestrator endpoint with full customer/payment method objects.
     */
    public function createWithOrchestrator(CreateOrchestratorOrderRequest $request): ApiResponse
    {
        return $this->executeWithRetry(function () use ($request) {
            try {
                $url = $this->getBaseApiUrl().$this->orchestratorEndpoint;

                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($url, $request->toApiPayload())
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                $this->logApiError('POST', $this->getBaseApiUrl().$this->orchestratorEndpoint, $e);

                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Update an order with validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(string $id, array $data): ApiResponse
    {
        $validatedData = $this->validateUpdateData($data);

        return parent::update($id, $validatedData);
    }

    /**
     * Update an order from DTO.
     */
    public function updateFromDto(string $id, UpdateOrderRequest $request): ApiResponse
    {
        return parent::update($id, $request->toApiPayload());
    }

    /**
     * Void an order.
     *
     * @param  array<string, mixed>|null  $meta  Optional metadata to include
     */
    public function void(string $id, ?array $meta = null): ApiResponse
    {
        return $this->updateFromDto($id, UpdateOrderRequest::void($meta));
    }

    /**
     * Capture an authorized order.
     *
     * @param  array<string, mixed>|null  $meta  Optional metadata to include
     */
    public function capture(string $id, ?array $meta = null): ApiResponse
    {
        return $this->updateFromDto($id, UpdateOrderRequest::capture($meta));
    }

    /**
     * Validate create order data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function validateCreateData(array $data): array
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'reference' => 'required|string|min:6|max:42',
            'customer_id' => 'required|string',
            'payment_method_id' => 'required|string',
            'meta' => 'nullable|array',
            'redirect_url' => 'nullable|url',
            'authorization' => 'nullable|array',
        ]);

        return $validator->validate();
    }

    /**
     * Validate update order data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function validateUpdateData(array $data): array
    {
        $validator = Validator::make($data, [
            'meta' => 'nullable|array',
            'action' => 'nullable|string|in:void,capture',
        ]);

        return $validator->validate();
    }
}
