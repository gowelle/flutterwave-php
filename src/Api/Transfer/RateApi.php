<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;

/**
 * API client for Flutterwave Transfer Rates endpoint.
 *
 * Handles rate calculations at /transfers/rates.
 */
class RateApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/transfers/rates';

    /**
     * Get transfer rate (POST)
     */
    public function create(array $data): ApiResponse
    {
        return parent::create($data);
    }

    /**
     * List available rates
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Retrieve is not supported for rates
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * Update is not supported for rates
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }
}
