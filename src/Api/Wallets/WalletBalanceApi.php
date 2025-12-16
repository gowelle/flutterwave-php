<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Wallets;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WalletBalanceApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the wallet balance API
     */
    protected string $endpoint = '/wallets/balances';

    /**
     * Fetch a currency's wallet balance
     *
     * @throws Exception
     */
    public function getBalance(string $currency): ApiResponse
    {
        $this->validateCurrency($currency);

        try {
            $url = $this->getBaseApiUrl().$this->endpoint.'/'.$currency;

            $response = Http::timeout((int) config('flutterwave.timeout', 30))
                ->withToken($this->getAccessToken())
                ->withHeaders($this->getHeaders()->toArray())
                ->get($url)
                ->throw();

            return ApiResponse::fromArray($response->json() ?? []);
        } catch (RequestException $e) {
            Log::error('Flutterwave Wallet Balance API Error', [
                'endpoint' => $this->endpoint,
                'currency' => $currency,
                'status_code' => $e->response?->status(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception('Failed to fetch wallet balance: '.$e->getMessage());
        }
    }

    /**
     * Not implemented for wallet balance API
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        $this->notImplemented('create');
    }

    /**
     * Not implemented for wallet balance API
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Not implemented for wallet balance API
     *
     * @throws Exception
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * Not implemented for wallet balance API
     *
     * @throws Exception
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Not implemented for wallet balance API
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate currency code
     *
     * @throws Exception
     */
    protected function validateCurrency(string $currency): void
    {
        $validator = Validator::make(['currency' => $currency], [
            'currency' => 'required|string|size:3',
        ]);

        if ($validator->fails()) {
            throw new Exception('Invalid currency code: '.$validator->errors()->first());
        }
    }
}
