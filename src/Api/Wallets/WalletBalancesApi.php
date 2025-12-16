<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Wallets;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WalletBalancesApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the wallet balances API
     */
    protected string $endpoint = '/wallets/balances';

    /**
     * Fetch wallet balances for multiple currencies
     *
     * @throws Exception
     */
    public function getBalances(): ApiResponse
    {
        try {
            $url = $this->getBaseApiUrl().$this->endpoint;

            $response = Http::timeout((int) config('flutterwave.timeout', 30))
                ->withToken($this->getAccessToken())
                ->withHeaders($this->getHeaders()->toArray())
                ->get($url)
                ->throw();

            return ApiResponse::fromArray($response->json() ?? []);
        } catch (RequestException $e) {
            Log::error('Flutterwave Wallet Balances API Error', [
                'endpoint' => $this->endpoint,
                'status_code' => $e->response?->status(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception('Failed to fetch wallet balances: '.$e->getMessage());
        }
    }

    /**
     * Not implemented for wallet balances API
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        $this->notImplemented('create');
    }

    /**
     * Not implemented for wallet balances API
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Not implemented for wallet balances API
     *
     * @throws Exception
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * Not implemented for wallet balances API
     *
     * @throws Exception
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Not implemented for wallet balances API
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }
}
