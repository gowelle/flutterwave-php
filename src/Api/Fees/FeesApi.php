<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Fees;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

/**
 * API client for the Flutterwave Fees endpoint.
 *
 * Retrieves transaction fees for a given amount, currency, and payment type.
 *
 * @see https://developer.flutterwave.com/reference/fees_get
 */
class FeesApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/fees';

    /**
     * Retrieve transaction fees
     *
     * @param  array<string, mixed>  $params  {amount: float, currency: string, payment_type?: string}
     *
     * @throws Exception
     *
     * @see https://developer.flutterwave.com/reference/fees_get
     */
    public function getFees(array $params): ApiResponse
    {
        $validated = $this->validateParams($params);

        return $this->executeWithRetry(function () use ($validated) {
            try {
                $url = $this->getBaseApiUrl().$this->endpoint;
                if (! empty($validated)) {
                    $url .= '?'.http_build_query($validated);
                }

                $response = Http::timeout((int) config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($url)
                    ->throw();

                return ApiResponse::fromArray($response->json() ?? []);
            } catch (RequestException $e) {
                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Create is not supported for fees
     */
    public function create(array $data): ApiResponse
    {
        $this->notImplemented('create');
    }

    /**
     * Retrieve is not supported directly — use getFees() with query params
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * List is not supported for fees — use getFees() with query params
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Update is not supported for fees
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Search is not supported for fees
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate query parameters
     *
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    protected function validateParams(array $params): array
    {
        $validator = Validator::make($params, [
            'amount'       => 'required|numeric|min:0.01',
            'currency'     => 'required|string|size:3',
            'payment_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new Exception('Invalid fees query parameters: '.$validator->errors()->first());
        }

        return $validator->validated();
    }
}
