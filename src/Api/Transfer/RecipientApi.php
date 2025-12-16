<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * API client for Flutterwave Transfer Recipients endpoint.
 *
 * Handles CRUD operations for transfer recipients at /transfers/recipients.
 */
class RecipientApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/transfers/recipients';

    /**
     * Create a recipient
     */
    public function create(array $data): ApiResponse
    {
        return parent::create($data);
    }

    /**
     * Retrieve a recipient
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * List recipients
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Delete a recipient
     */
    public function delete(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->delete($this->buildApiSpecificBaseUrl() . '/' . $id)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Update is not supported for recipients
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }
}
