<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;

/**
 * API client for Flutterwave Transfer Senders endpoint.
 *
 * Handles operations for transfer senders at /transfers/senders.
 */
class SenderApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/transfers/senders';

    /**
     * Create a sender
     */
    public function create(array $data): ApiResponse
    {
        return parent::create($data);
    }

    /**
     * Retrieve a sender
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * List senders
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Update is not supported for senders
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }
}
