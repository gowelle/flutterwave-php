<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Transfer;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Support\Facades\Validator;

class TransferApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the transfer API
     */
    protected string $endpoint = '/transfers';

    /**
     * Create a transfer with validation
     */
    public function create(array $data): ApiResponse
    {
        $validatedData = $this->validateTransferData($data);

        return parent::create($validatedData);
    }

    /**
     * Retrieve a transfer
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * List transfers
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Update a transfer is not implemented
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Search for a transfer is not implemented
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate transfer data
     */
    protected function validateTransferData(array $data): array
    {
        $validator = Validator::make($data, [
            'account_bank' => 'required|string',
            'account_number' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'reference' => 'required|string',
            'narration' => 'nullable|string|max:500',
            'beneficiary_name' => 'required|string',
        ]);

        return $validator->validate();
    }
}
