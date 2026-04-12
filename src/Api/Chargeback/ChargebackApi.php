<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Chargeback;

use Exception;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Chargeback\CreateChargebackRequest;
use Gowelle\Flutterwave\Data\Chargeback\UpdateChargebackRequest;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Support\Facades\Validator;

/**
 * API client for the Flutterwave Chargebacks endpoint.
 *
 * Supports listing, creating, retrieving, and updating chargebacks at /chargebacks.
 *
 * @see https://developer.flutterwave.com/reference/chargebacks_list
 */
class ChargebackApi extends FlutterwaveBaseApi
{
    protected string $endpoint = '/chargebacks';

    /**
     * List all chargebacks
     *
     * @see https://developer.flutterwave.com/reference/chargebacks_list
     */
    public function list(): ApiResponse
    {
        return parent::list();
    }

    /**
     * Create a chargeback
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Exception
     *
     * @see https://developer.flutterwave.com/reference/chargebacks_post
     */
    public function create(array $data): ApiResponse
    {
        $validated = $this->validateCreateData($data);

        return parent::create($validated);
    }

    /**
     * Create a chargeback from DTO
     *
     * @see https://developer.flutterwave.com/reference/chargebacks_post
     */
    public function createFromDto(CreateChargebackRequest $request): ApiResponse
    {
        return parent::create($request->toApiPayload());
    }

    /**
     * Retrieve a chargeback by ID
     *
     * @see https://developer.flutterwave.com/reference/chargebacks_get_by_id
     */
    public function retrieve(string $id): ApiResponse
    {
        return parent::retrieve($id);
    }

    /**
     * Update a chargeback by ID
     *
     * @param  array<string, mixed>  $data
     *
     * @throws Exception
     *
     * @see https://developer.flutterwave.com/reference/chargeback_put
     */
    public function update(string $id, array $data): ApiResponse
    {
        $validated = $this->validateUpdateData($data);

        return parent::update($id, $validated);
    }

    /**
     * Update a chargeback from DTO
     *
     * @see https://developer.flutterwave.com/reference/chargeback_put
     */
    public function updateFromDto(string $id, UpdateChargebackRequest $request): ApiResponse
    {
        return parent::update($id, $request->toApiPayload());
    }

    /**
     * Search is not supported for chargebacks
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate create chargeback payload
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    protected function validateCreateData(array $data): array
    {
        $validator = Validator::make($data, [
            'charge_id' => 'required|string',
            'reason'    => 'required|string',
            'meta'      => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new Exception('Invalid chargeback data: '.$validator->errors()->first());
        }

        return $validator->validated();
    }

    /**
     * Validate update chargeback payload
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    protected function validateUpdateData(array $data): array
    {
        $validator = Validator::make($data, [
            'status'  => 'required|string',
            'comment' => 'nullable|string',
            'meta'    => 'nullable|array',
        ]);

        if ($validator->fails()) {
            throw new Exception('Invalid chargeback update data: '.$validator->errors()->first());
        }

        return $validator->validated();
    }
}
