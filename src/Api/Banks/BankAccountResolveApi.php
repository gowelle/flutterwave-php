<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Banks;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BankAccountResolveApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the bank account resolve API
     */
    protected string $endpoint = '/banks/account-resolve';

    /**
     * Resolve bank account details
     *
     * @throws Exception
     */
    public function resolve(string $bankCode, string $accountNumber, string $currency = 'NGN'): ApiResponse
    {
        $validatedData = $this->validateResolveData([
            'bank_code' => $bankCode,
            'account_number' => $accountNumber,
            'currency' => mb_strtoupper($currency),
        ]);

        try {
            $url = $this->getBaseApiUrl().$this->endpoint;

            $response = Http::timeout(30)
                ->withToken($this->getAccessToken())
                ->withHeaders($this->getHeaders()->toArray())
                ->post($url, $validatedData)
                ->throw();

            $data = $response->json();

            return new ApiResponse(
                status: $data['status'] ?? 'unknown',
                message: $data['message'] ?? null,
                data: $data['data'] ?? [],
            );
        } catch (RequestException $e) {
            Log::error('Flutterwave Bank Account Resolve API Error', [
                'endpoint' => $this->endpoint,
                'bank_code' => $bankCode,
                'account_number' => $accountNumber,
                'status_code' => $e->response?->status(),
                'response' => $e->response?->body(),
            ]);

            throw new Exception('Failed to resolve bank account: '.$e->getMessage());
        }
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        throw new Exception('Create method not implemented for Bank Account Resolve API');
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        throw new Exception('Update method not implemented for Bank Account Resolve API');
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function retrieve(string $id): ApiResponse
    {
        throw new Exception('Retrieve method not implemented for Bank Account Resolve API');
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function list(): ApiResponse
    {
        throw new Exception('List method not implemented for Bank Account Resolve API');
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        throw new Exception('Search method not implemented for Bank Account Resolve API');
    }

    /**
     * Validate bank account resolve data
     */
    protected function validateResolveData(array $data): array
    {
        $validator = Validator::make($data, [
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'currency' => 'required|string|size:3',
        ]);

        return $validator->validate();
    }
}
