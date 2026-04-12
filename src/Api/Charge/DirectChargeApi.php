<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Charge;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;

class DirectChargeApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the direct charge API
     */
    protected string $endpoint = '/orchestration/direct-charges';

    /**
     * Create a direct charge
     */
    public function create(array $data): ApiResponse
    {
        return parent::create($data);
    }

    /**
     * Retrieve is not supported for direct charges
     * (GET /orchestration/direct-charges/{id} does not exist in the API)
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * Update is not supported for direct charges
     * (PUT /orchestration/direct-charges/{id} does not exist in the API)
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * List direct charges is not implemented
     *
     * @throws Exception
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Search for a direct charge is not implemented
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }
}
