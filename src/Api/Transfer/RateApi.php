<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;

/**
 * API client for Flutterwave Transfer Rates endpoint.
 *
 * Handles rate calculations at /transfers/rates.
 *
 * @see https://developer.flutterwave.com/reference/transfer_rates_post
 */
class RateApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/transfers/rates';

    /**
     * Create a rate conversion (POST /transfers/rates)
     *
     * @see https://developer.flutterwave.com/reference/transfer_rates_post
     */
    public function create(array $data): ApiResponse
    {
        return parent::create($data);
    }

    /**
     * Fetch a specific converted rate by ID (GET /transfers/rates/{id})
     *
     * @see https://developer.flutterwave.com/reference/transfer_rates_get
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * List is not supported — use retrieve(string $id) for a specific rate.
     * There is no list-all-rates endpoint in the Flutterwave API.
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Update is not supported for rates
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Search is not supported for rates
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }
}

