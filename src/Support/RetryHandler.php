<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Support;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

final class RetryHandler
{
    public function __construct(
        private int $maxRetries = 3,
        private int $retryDelay = 1000, // milliseconds
    ) {}

    /**
     * Execute a callback with retry logic
     */
    public function execute(callable $callback): mixed
    {
        $attempt = 0;

        while (true) {
            try {
                return $callback();
            } catch (RequestException $e) {
                $attempt++;

                if (! $this->shouldRetry($e, $attempt)) {
                    throw $e;
                }

                $this->logRetry($attempt, $e);
                $this->sleep($attempt);
            }
        }
    }

    /**
     * Determine if request should be retried
     */
    private function shouldRetry(RequestException $e, int $attempt): bool
    {
        // Don't retry if max attempts reached
        if ($attempt >= $this->maxRetries) {
            return false;
        }

        $status = $e->response?->status();

        // Retry on 5xx errors and specific 4xx errors
        return $status >= 500
            || $status === 429  // Rate limit
            || $status === 408  // Timeout
            || $status === 503; // Service unavailable
    }

    /**
     * Log retry attempt
     */
    private function logRetry(int $attempt, RequestException $e): void
    {
        if (config('flutterwave.logging.enabled', true)) {
            Log::warning('Flutterwave API retry attempt', [
                'attempt' => $attempt,
                'max_retries' => $this->maxRetries,
                'status' => $e->response?->status(),
                'url' => $e->response?->effectiveUri()?->__toString(),
            ]);
        }
    }

    /**
     * Sleep with exponential backoff
     */
    private function sleep(int $attempt): void
    {
        $delay = $this->retryDelay * (2 ** ($attempt - 1));
        usleep($delay * 1000);
    }
}
