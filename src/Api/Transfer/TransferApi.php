<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Transfer\RetryTransferRequest;
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
                    ->post($this->getBaseApiUrl().'/direct-transfers', $data)
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
     * Retry or duplicate a transfer
     *
     * @param  string  $action      'retry' or 'duplicate'
     * @param  string|null  $reference   Unique transaction reference for this retry
     * @param  string|null  $callbackUrl  Optional webhook URL for transfer status updates
     *
     * @see https://developer.flutterwave.com/reference/transfer_post_retry
     */
    public function retry(
        string $id,
        string $action = 'retry',
        ?string $reference = null,
        ?string $callbackUrl = null,
    ): ApiResponse {
        $body = array_filter([
            'action'       => $action,
            'reference'    => $reference,
            'callback_url' => $callbackUrl,
        ], fn ($value) => $value !== null);

        return $this->executeWithRetry(function () use ($id, $body) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl()."/transfers/{$id}/retries", $body)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Retry or duplicate a transfer from DTO
     *
     * @see https://developer.flutterwave.com/reference/transfer_post_retry
     */
    public function retryFromDto(RetryTransferRequest $request): ApiResponse
    {
        return $this->retry(
            id: $request->transferId,
            action: $request->action,
            reference: $request->reference,
            callbackUrl: $request->callbackUrl,
        );
    }

    // Recipients

    /**
     * Create a recipient
     *
     * @deprecated Use RecipientApi::create() via FlutterwaveApi::TRANSFER_RECIPIENTS instead.
     */
    public function createRecipient(array $data): ApiResponse
    {
        return $this->executeWithRetry(function () use ($data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl().'/transfers/recipients', $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Get a recipient
     *
     * @deprecated Use RecipientApi::retrieve() via FlutterwaveApi::TRANSFER_RECIPIENTS instead.
     */
    public function getRecipient(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl()."/transfers/recipients/{$id}")
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * List recipients
     *
     * @deprecated Use RecipientApi::list() via FlutterwaveApi::TRANSFER_RECIPIENTS instead.
     */
    public function listRecipients(): ApiResponse
    {
        return $this->executeWithRetry(function () {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl().'/transfers/recipients')
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Delete a recipient
     *
     * @deprecated Use RecipientApi::delete() via FlutterwaveApi::TRANSFER_RECIPIENTS instead.
     */
    public function deleteRecipient(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->delete($this->getBaseApiUrl()."/transfers/recipients/{$id}")
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
     *
     * @deprecated Use SenderApi::create() via FlutterwaveApi::TRANSFER_SENDERS instead.
     */
    public function createSender(array $data): ApiResponse
    {
        return $this->executeWithRetry(function () use ($data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl().'/transfers/senders', $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Get a sender
     *
     * @deprecated Use SenderApi::retrieve() via FlutterwaveApi::TRANSFER_SENDERS instead.
     */
    public function getSender(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl()."/transfers/senders/{$id}")
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * List senders
     *
     * @deprecated Use SenderApi::list() via FlutterwaveApi::TRANSFER_SENDERS instead.
     */
    public function listSenders(): ApiResponse
    {
        return $this->executeWithRetry(function () {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl().'/transfers/senders')
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
     *
     * @deprecated Use RateApi::create() via FlutterwaveApi::TRANSFER_RATES instead.
     */
    public function getRate(array $data): ApiResponse
    {
        return $this->executeWithRetry(function () use ($data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->getBaseApiUrl().'/transfers/rates', $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * List rates
     *
     * @deprecated Use RateApi::retrieve() via FlutterwaveApi::TRANSFER_RATES instead.
     */
    public function listRates(): ApiResponse
    {
        return $this->executeWithRetry(function () {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->getBaseApiUrl().'/transfers/rates')
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }
}
