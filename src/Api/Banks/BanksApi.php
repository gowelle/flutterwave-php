<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Banks;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BanksApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the banks API
     */
    protected string $endpoint = '/banks';

    /**
     * Retrieve banks by country
     *
     * @throws Exception
     */
    public function retrieveByCountry(string $country): ApiResponse
    {
        $country = mb_strtoupper($country);

        try {
            $url = $this->getBaseApiUrl().$this->endpoint.'?country='.$country;

            $response = Http::timeout(30)
                ->withToken($this->getAccessToken())
                ->withHeaders($this->getHeaders()->toArray())
                ->get($url)
                ->throw();

            $data = $response->json();

            return new ApiResponse(
                status: $data['status'] ?? 'unknown',
                message: $data['message'] ?? null,
                data: $data['data'] ?? [],
            );
        } catch (RequestException $e) {
            Log::error('Flutterwave Banks API Error', [
                'endpoint' => $this->endpoint,
                'country' => $country,
                'status_code' => $e->response?->status(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception('Failed to retrieve banks: '.$e->getMessage());
        }
    }

    /**
     * Create is not supported for banks
     */
    public function create(array $data): ApiResponse
    {
        $this->notImplemented('create');
    }

    /**
     * Update is not supported for banks
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Retrieve is not supported for banks — use retrieveByCountry()
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * List is not supported for banks — use retrieveByCountry()
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Search is not supported for banks
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }
}
