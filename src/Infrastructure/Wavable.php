<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Infrastructure;

interface Wavable
{
    public function getIdempotencyKey(): ?string;

    public function getTraceId(): ?string;

    public function getScenarioKey(): ?string;
}
