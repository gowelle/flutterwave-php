<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/**
 * API client for Flutterwave Transfer Senders endpoint.
 *
 * Handles operations for transfer senders at /transfers/senders.
 *
 * @see https://developer.flutterwave.com/reference/transfers_senders_list
 */
class SenderApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/transfers/senders';

    /**
     * Create a sender
     *
     * @see https://developer.flutterwave.com/reference/transfers_senders_create
     */
    public function create(array $data): ApiResponse
    {
        return parent::create($data);
    }

    /**
     * Retrieve a sender by ID
     *
     * @see https://developer.flutterwave.com/reference/transfers_senders_get
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * List senders
     *
     * @see https://developer.flutterwave.com/reference/transfers_senders_list
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Delete a sender by ID
     *
     * @see https://developer.flutterwave.com/reference/transfers_senders_delete
     */
    public function delete(string $id): ApiResponse
    {
        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->delete($this->buildApiSpecificBaseUrl().'/'.$id)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Update is not supported for senders
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Search is not supported for senders
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }
}

