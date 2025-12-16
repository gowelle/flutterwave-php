<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Refund;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
     * List refunds with query parameters (pagination and filtering)
     *
     * @param array<string, mixed> $params Query parameters (page, size, from, to)
     */
    public function listWithParams(array $params): ApiResponse
    {
        return $this->executeWithRetry(function () use ($params) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->buildApiSpecificBaseUrl(), $params)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                $this->logApiError('GET', $this->buildApiSpecificBaseUrl(), $e);

                throw $this->createApiException($e);
            }
        });
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
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
            'meta' => 'nullable|array',
        ]);

        return $validator->validate();
    }

    /**
     * Log API error
     */
    protected function logApiError(string $method, string $url, RequestException $e): void
    {
        Log::error('Flutterwave Refund API error', [
            'method' => $method,
            'url' => $url,
            'status' => $e->response?->status() ?? 500,
            'response' => $e->response?->body(),
        ]);
    }

    /**
     * Create API exception from request exception
     */
    protected function createApiException(RequestException $e)
    {
        return \Gowelle\Flutterwave\Exceptions\FlutterwaveApiException::fromResponseBody(
            responseBody: $e->response?->body(),
            statusCode: $e->response?->status() ?? 500,
            previous: $e,
        );
    }
}
