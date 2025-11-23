<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

final class RateLimitException extends FlutterwaveException
{
    public static function exceeded(int $maxRequests, int $perSeconds): self
    {
        return new self(
            "Rate limit exceeded: {$maxRequests} requests per {$perSeconds} seconds. Please try again later."
        );
    }
}
