<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Settlement;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;

class SettlementApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the settlement API
     */
    protected string $endpoint = '/settlements';

    /**
     * List settlements
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Retrieve a settlement
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * Create a settlement is not implemented
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        $this->notImplemented('create');
    }

    /**
     * Update a settlement is not implemented
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Search for a settlement is not implemented
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }
}
