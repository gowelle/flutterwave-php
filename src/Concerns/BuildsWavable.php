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
     * @param  FlutterwaveApi|null  $apiType  Optional API type to determine scenario key
     * @param  bool  $isProductEnvironment  Whether this is a production environment
     */
    private function buildWavable(array $data, ?FlutterwaveApi $apiType = null, bool $isProductEnvironment = false): Wavable
    {
        // Prefer an explicit idempotency key. For charge requests, fall back to
        // order_id when the business flow guarantees one order maps to one charge.
        $isChargeRequest = $apiType === FlutterwaveApi::CHARGE;
        $idempotencyKey = $data['idempotency_key']
            ?? (($isChargeRequest && isset($data['order_id'])) ? $data['order_id'] : Str::uuid()->toString());

        $traceId = $data['trace_id'] ?? Str::uuid()->toString();

        // Determine scenario key based on API type and environment
        $scenarioKey = null;
        if (! $isProductEnvironment) {
            // Check if explicit scenario key is provided
            if (isset($data['scenario_key'])) {
                $scenarioKey = $data['scenario_key'];
            }
        }

        return new Wavable($idempotencyKey, $traceId, $scenarioKey);
    }
}
