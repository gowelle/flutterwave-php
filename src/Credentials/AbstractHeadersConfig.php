<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Credentials;

use Gowelle\Flutterwave\Data\HeaderConfig;

final class AbstractHeadersConfig
{
    public string $contentType = 'application/json';

    public string $idempotencyKey;

    public string $traceId;

    public ?string $scenarioKey;

    private function __construct(string $contentType, string $idempotencyKey, string $traceId, ?string $scenarioKey = null)
    {
        $this->contentType = $contentType;
        $this->idempotencyKey = $idempotencyKey;
        $this->traceId = $traceId;
        $this->scenarioKey = $scenarioKey;
    }

    public static function fromArray(array $headers): static
    {
        return new self(
            contentType: $headers['Content-Type'],
            idempotencyKey: $headers['X-Idempotency-Key'],
            traceId: $headers['X-Trace-Id'],
            scenarioKey: $headers['X-Scenario-Key'] ?? null,
        );
    }

    public static function fromHeaderConfig(HeaderConfig $config): static
    {
        return new static(
            contentType: $config->contentType,
            idempotencyKey: $config->idempotencyKey,
            traceId: $config->traceId,
            scenarioKey: $config->scenarioKey,
        );
    }

    public function toArray(): array
    {
        $headers = [
            'Content-Type' => $this->contentType,
            'X-Idempotency-Key' => $this->idempotencyKey,
            'X-Trace-Id' => $this->traceId,
        ];

        if ($this->scenarioKey !== null) {
            $headers['X-Scenario-Key'] = $this->scenarioKey;
        }

        return $headers;
    }
}
