<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Objects;

use Gowelle\Flutterwave\Infrastructure\Wavable as InfrastructureWavable;

final readonly class Wavable implements InfrastructureWavable
{
    public function __construct(
        public string $idempotencyKey,
        public string $traceId,
        public ?string $scenarioKey = null,
    ) {}

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function getTraceId(): string
    {
        return $this->traceId;
    }

    public function getScenarioKey(): ?string
    {
        return $this->scenarioKey;
    }
}
