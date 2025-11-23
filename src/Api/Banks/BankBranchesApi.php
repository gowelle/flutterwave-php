<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Banks;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BankBranchesApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the bank branches API
     */
    protected string $endpoint = '/banks';

    /**
     * Retrieve bank branches by bank ID
     *
     * @throws Exception
     */
    public function retrieveByBankId(string $bankId): ApiResponse
    {
        try {
            $url = $this->getBaseApiUrl().$this->endpoint.'/'.$bankId.'/branches';

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
            Log::error('Flutterwave Bank Branches API Error', [
                'endpoint' => $this->endpoint,
                'bank_id' => $bankId,
                'status_code' => $e->response?->status(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception('Failed to retrieve bank branches: '.$e->getMessage());
        }
    }

    /**
     * Not implemented for bank branches API
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        throw new Exception('Create method not implemented for Bank Branches API');
    }

    /**
     * Not implemented for bank branches API
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        throw new Exception('Update method not implemented for Bank Branches API');
    }

    /**
     * Not implemented for bank branches API
     *
     * @throws Exception
     */
    public function retrieve(string $id): ApiResponse
    {
        throw new Exception('Retrieve method not implemented for Bank Branches API');
    }

    /**
     * Not implemented for bank branches API
     *
     * @throws Exception
     */
    public function list(): ApiResponse
    {
        throw new Exception('List method not implemented for Bank Branches API');
    }

    /**
     * Not implemented for bank branches API
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        throw new Exception('Search method not implemented for Bank Branches API');
    }
}
