<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Charge;

use Gowelle\Flutterwave\Data\ApiResponse;
use Illuminate\Support\Facades\Validator;

class DirectChargeApi extends ChargeApi
{
    /**
     * Use the canonical charge resource endpoint for list/retrieve/update.
     */
    protected string $endpoint = '/charges';

    /**
     * Orchestrator-only endpoint used when initiating a direct charge.
     */
    protected string $orchestrationEndpoint = '/orchestration/direct-charges';

    /**
     * Create a direct charge via the orchestrator endpoint.
     */
    public function create(array $data): ApiResponse
    {
        $validatedData = $this->validateCreateData($data);

        return $this->postToUrl($this->getBaseApiUrl().$this->orchestrationEndpoint, $validatedData);
    }

    /**
     * Validate direct charge creation data.
     */
    protected function validateCreateData(array $data): array
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'reference' => 'required|string|between:6,42',
            'customer' => 'required|array',
            'payment_method' => 'required|array',
            'redirect_url' => 'nullable|url',
            'meta' => 'nullable|array',
            'authorization' => 'nullable|array',
            'recurring' => 'nullable|boolean',
            'order_id' => 'nullable|string',
            'merchant_vat_amount' => 'nullable|numeric|min:0',
        ]);

        return $validator->validate();
    }
}
