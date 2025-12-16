<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use InvalidArgumentException;

final readonly class HeaderConfig
{
    public function __construct(
        public string $contentType,
        public string $idempotencyKey,
        public string $traceId,
        public ?string $scenarioKey = null,
    ) {
        $this->validate();
    }

    /**
     * Create header config for payment operations
     */
    public static function forPayment(
        string $idempotencyKey,
        string $traceId,
        string $scenarioKey = 'scenario:auth_redirect',
    ): self {
        return new self(
            contentType: 'application/json',
            idempotencyKey: $idempotencyKey,
            traceId: $traceId,
            scenarioKey: $scenarioKey,
        );
    }

    /**
     * Create from array
     */
    public static function fromArray(array $headers): self
    {
        return new self(
            contentType: $headers['Content-Type'] ?? 'application/json',
            idempotencyKey: $headers['X-Idempotency-Key'],
            traceId: $headers['X-Trace-Id'],
            scenarioKey: $headers['X-Scenario-Key'] ?? null,
        );
    }

    /**
     * Convert to array for HTTP headers
     */
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

    /**
     * Validate header values
     */
    private function validate(): void
    {
        if (empty($this->contentType)) {
            throw new InvalidArgumentException('Content-Type cannot be empty');
        }

        if (empty($this->idempotencyKey)) {
            throw new InvalidArgumentException('Idempotency-Key cannot be empty');
        }

        if (empty($this->traceId)) {
            throw new InvalidArgumentException('Trace-Id cannot be empty');
        }
    }
}
