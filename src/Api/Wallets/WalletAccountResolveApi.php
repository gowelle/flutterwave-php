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

class WalletAccountResolveApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the wallet account resolve API
     */
    protected string $endpoint = '/wallets/account-resolve';

    /**
     * Resolve wallet account details
     *
     * @throws Exception
     */
    public function resolve(string $provider, string $identifier): ApiResponse
    {
        $validatedData = $this->validateResolveData([
            'provider' => $provider,
            'identifier' => $identifier,
        ]);

        try {
            $url = $this->getBaseApiUrl().$this->endpoint;

            $response = Http::timeout((int) config('flutterwave.timeout', 30))
                ->withToken($this->getAccessToken())
                ->withHeaders($this->getHeaders()->toArray())
                ->post($url, $validatedData)
                ->throw();

            return ApiResponse::fromArray($response->json() ?? []);
        } catch (RequestException $e) {
            Log::error('Flutterwave Wallet Account Resolve API Error', [
                'endpoint' => $this->endpoint,
                'provider' => $provider,
                'identifier' => $identifier,
                'status_code' => $e->response?->status(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception('Failed to resolve wallet account: '.$e->getMessage());
        }
    }

    /**
     * Not implemented for wallet account resolve API
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        $this->notImplemented('create');
    }

    /**
     * Not implemented for wallet account resolve API
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Not implemented for wallet account resolve API
     *
     * @throws Exception
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * Not implemented for wallet account resolve API
     *
     * @throws Exception
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Not implemented for wallet account resolve API
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate wallet account resolve data
     */
    protected function validateResolveData(array $data): array
    {
        $validator = Validator::make($data, [
            'provider' => 'required|string|in:flutterwave',
            'identifier' => 'required|string',
        ]);

        return $validator->validate();
    }
}
