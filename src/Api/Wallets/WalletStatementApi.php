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

class WalletStatementApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the wallet statement API
     */
    protected string $endpoint = '/wallets/statement';

    /**
     * Retrieve wallet statement
     *
     * @param  array<string, mixed>  $params
     *
     * @throws Exception
     */
    public function getStatement(array $params = []): ApiResponse
    {
        $validatedParams = $this->validateStatementParams($params);

        try {
            $url = $this->getBaseApiUrl().$this->endpoint;
            if (! empty($validatedParams)) {
                $url .= '?'.http_build_query($validatedParams);
            }

            $response = Http::timeout((int) config('flutterwave.timeout', 30))
                ->withToken($this->getAccessToken())
                ->withHeaders($this->getHeaders()->toArray())
                ->get($url)
                ->throw();

            return ApiResponse::fromArray($response->json() ?? []);
        } catch (RequestException $e) {
            Log::error('Flutterwave Wallet Statement API Error', [
                'endpoint' => $this->endpoint,
                'params' => $validatedParams,
                'status_code' => $e->response?->status(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception('Failed to retrieve wallet statement: '.$e->getMessage());
        }
    }

    /**
     * Not implemented for wallet statement API
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        $this->notImplemented('create');
    }

    /**
     * Not implemented for wallet statement API
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Not implemented for wallet statement API
     *
     * @throws Exception
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * Not implemented for wallet statement API
     *
     * @throws Exception
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Not implemented for wallet statement API
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate wallet statement parameters
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function validateStatementParams(array $params): array
    {
        $validator = Validator::make($params, [
            'currency' => 'required|string|size:3',
            'size' => 'nullable|integer|min:10|max:50',
            'from' => 'nullable|string|date',
            'to' => 'nullable|string|date',
            'next' => 'nullable|string',
            'previous' => 'nullable|string',
        ]);

        return $validator->validated();
    }
}
