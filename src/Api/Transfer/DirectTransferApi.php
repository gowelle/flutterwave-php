<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;

/**
 * API client for Flutterwave Direct Transfer Orchestrator endpoint.
 *
 * This handles the POST /direct-transfers endpoint which combines recipient
 * creation with transfer initiation in a single request.
 *
 * Note: This endpoint only supports POST. For GET/list/update operations,
 * use the general /transfers endpoint via TransferApi.
 */
class DirectTransferApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/direct-transfers';

    /**
     * Create a direct transfer (orchestrator flow)
     */
    public function create(array $data): ApiResponse
    {
        return parent::create($data);
    }

    /**
     * Not supported for direct transfers - use TransferApi
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * Not supported for direct transfers - use TransferApi
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Not supported for direct transfers - use TransferApi
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }
}
