<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Support;

use Gowelle\Flutterwave\Exceptions\RateLimitException;
use Illuminate\Support\Facades\Cache;

final class RateLimiter
{
    public function __construct(
        private int $maxRequests = 100,
        private int $perSeconds = 60,
    ) {}

    /**
     * Attempt to make a request within rate limits
     *
     * @throws RateLimitException
     */
    public function attempt(string $key = 'global'): void
    {
        if (! config('flutterwave.rate_limit.enabled', true)) {
            return;
        }

        $cacheKey = "flutterwave:ratelimit:{$key}";

        $requests = Cache::get($cacheKey, 0);

        if ($requests >= $this->maxRequests) {
            throw new RateLimitException(
                "Rate limit exceeded: {$this->maxRequests} requests per {$this->perSeconds} seconds"
            );
        }

        Cache::put($cacheKey, $requests + 1, $this->perSeconds);
    }

    /**
     * Get remaining requests for a key
     */
    public function remaining(string $key = 'global'): int
    {
        $cacheKey = "flutterwave:ratelimit:{$key}";
        $requests = Cache::get($cacheKey, 0);

        return max(0, $this->maxRequests - $requests);
    }

    /**
     * Clear rate limit for a key
     */
    public function clear(string $key = 'global'): void
    {
        $cacheKey = "flutterwave:ratelimit:{$key}";
        Cache::forget($cacheKey);
    }
}
