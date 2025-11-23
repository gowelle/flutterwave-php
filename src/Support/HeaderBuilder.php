<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Support;

use Gowelle\Flutterwave\Data\FlutterwaveConfig;
use Gowelle\Flutterwave\Infrastructure\Wavable;
use Illuminate\Support\Str;

class HeaderBuilder
{
    public function __construct(private readonly FlutterwaveConfig $config) {}

    /**
     * Build HTTP headers for Flutterwave API requests
     */
    public function build(?Wavable $wavable = null): array
    {
        $headers = ['Content-Type' => 'application/json'];

        if ($wavable) {
            $headers['X-Idempotency-Key'] = $wavable->getIdempotencyKey();
            $headers['X-Trace-Id'] = $wavable->getTraceId();

            // Only add scenario key in non-production environments
            if (! $this->config->isProduction() && $wavable->getScenarioKey()) {
                $headers['X-Scenario-Key'] = $wavable->getScenarioKey();
            }
        } else {
            // Generate defaults for operations that don't need Wavable
            $headers['X-Idempotency-Key'] = Str::uuid()->toString();
            $headers['X-Trace-Id'] = Str::uuid()->toString();

            if (! $this->config->isProduction()) {
                $headers['X-Scenario-Key'] = 'scenario:auth_redirect';
            }
        }

        return $headers;
    }

    /**
     * Build headers from array of values
     */
    public function fromArray(array $values): array
    {
        $headers = ['Content-Type' => $values['Content-Type'] ?? 'application/json'];

        if (isset($values['X-Idempotency-Key'])) {
            $headers['X-Idempotency-Key'] = $values['X-Idempotency-Key'];
        }

        if (isset($values['X-Trace-Id'])) {
            $headers['X-Trace-Id'] = $values['X-Trace-Id'];
        }

        if (isset($values['X-Scenario-Key']) && ! $this->config->isProduction()) {
            $headers['X-Scenario-Key'] = $values['X-Scenario-Key'];
        }

        return $headers;
    }
}
