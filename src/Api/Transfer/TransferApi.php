<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * API client for Flutterwave Transfer endpoints.
 *
 * Handles both general flow (/transfers) and orchestrator flow (/direct-transfers).
 */
class TransferApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/transfers';

    /**
     * Create a transfer (general flow)
     */
    public function create(array $data): ApiResponse
    {
        return parent::create($data);
    }

    /**
     * Create a direct transfer (orchestrator flow)
     */
    public function createDirect(array $data): ApiResponse
    {
        return $this->executeWithRetry(function () use ($data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl() . '/direct-transfers', $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Retrieve a transfer
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * List transfers
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Update a transfer (for deferred/scheduled)
     */
    public function update(string $id, array $data): ApiResponse
    {
        return parent::update($id, $data);
    }

    /**
     * Retry a transfer
     */
    public function retry(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl() . "/transfers/{$id}/retries", [])
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    // Recipients

    /**
     * Create a recipient
     */
    public function createRecipient(array $data): ApiResponse
    {
        return $this->executeWithRetry(function () use ($data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl() . '/transfers/recipients', $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Get a recipient
     */
    public function getRecipient(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl() . "/transfers/recipients/{$id}")
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * List recipients
     */
    public function listRecipients(): ApiResponse
    {
        return $this->executeWithRetry(function () {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl() . '/transfers/recipients')
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Delete a recipient
     */
    public function deleteRecipient(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->delete($this->getBaseApiUrl() . "/transfers/recipients/{$id}")
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    // Senders

    /**
     * Create a sender
     */
    public function createSender(array $data): ApiResponse
    {
        return $this->executeWithRetry(function () use ($data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl() . '/transfers/senders', $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Get a sender
     */
    public function getSender(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl() . "/transfers/senders/{$id}")
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * List senders
     */
    public function listSenders(): ApiResponse
    {
        return $this->executeWithRetry(function () {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl() . '/transfers/senders')
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    // Rates

    /**
     * Get transfer rate
     */
    public function getRate(array $data): ApiResponse
    {
        return $this->executeWithRetry(function () use ($data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl() . '/transfers/rates', $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * List rates
     */
    public function listRates(): ApiResponse
    {
        return $this->executeWithRetry(function () {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl() . '/transfers/rates')
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }
}
