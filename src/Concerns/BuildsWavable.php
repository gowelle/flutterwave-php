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
        // Generate UUID for idempotency key and trace id
        // If request is for charge, use order_id as idempotency key for idempotency
        $isChargeRequest = $apiType === FlutterwaveApi::CHARGE;
        $idempotencyKey = ($isChargeRequest && isset($data['order_id']))
            ? $data['order_id']
            : ($data['idempotency_key'] ?? $data['user_id'] ?? Str::uuid()->toString());

        $traceId = $data['trace_id'] ?? Str::uuid()->toString();

        // Determine scenario key based on API type and environment
        $scenarioKey = null;
        if (! $isProductEnvironment) {
            // Check if explicit scenario key is provided
            if (isset($data['scenario_key'])) {
                $scenarioKey = $data['scenario_key'];
            } else {
                // Use context-appropriate defaults based on API type
                match ($apiType) {
                    FlutterwaveApi::TRANSFER, FlutterwaveApi::DIRECT_TRANSFER => $scenarioKey = 'scenario:successful',
                    FlutterwaveApi::CHARGE => $scenarioKey = 'scenario:auth_redirect',
                    // Recipient/Sender endpoints don't support scenario keys
                    FlutterwaveApi::TRANSFER_RECIPIENTS, FlutterwaveApi::TRANSFER_SENDERS => $scenarioKey = null,
                    default => $scenarioKey = null,
                };
            }
        }

        return new Wavable($idempotencyKey, $traceId, $scenarioKey);
    }
}
