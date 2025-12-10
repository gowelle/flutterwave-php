<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Concerns;

use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Objects\Wavable;
use Illuminate\Support\Str;

trait BuildsWavable
{
    /**
     * Build a wavable object
     *
     * @param  array<string, mixed>  $data  Request data
     * @param  FlutterwaveApi|null  $apiType  Optional API type to determine if this is a charge request
     * @param  bool  $isProductEnvironment  Whether this is a production environment
     */
    private function buildWavable(array $data, ?FlutterwaveApi $apiType = null, bool $isProductEnvironment = false): Wavable
    {
        // Generate UUID for idempotency key and trace id
        // If request is for charge, use order_id as idempotency key for idempotency
        $isChargeRequest = $apiType === FlutterwaveApi::CHARGE;
        $idempotencyKey = ($isChargeRequest && isset($data['order_id']))
            ? $data['order_id']
            : ($data['idempotency_key'] ?? $data['user_id'] ?? Str::uuid()->toString());

        $traceId = $data['trace_id'] ?? Str::uuid()->toString();

        // Omit scenario key if production environment (scenario keys are for testing)
        $scenarioKey = $isProductEnvironment
            ? null
            : ($data['scenario_key'] ?? 'scenario:auth_redirect');

        return new Wavable($idempotencyKey, $traceId, $scenarioKey);
    }
}
