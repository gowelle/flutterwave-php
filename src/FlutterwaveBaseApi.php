<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave;

use Exception;
use Gowelle\Flutterwave\Concerns\InitializesCredentials;
use Gowelle\Flutterwave\Concerns\ProvidesBaseApiUrl;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Exceptions\ApiMethodNotImplementedException;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApiContract;
use Gowelle\Flutterwave\Services\FlutterwaveErrorMapper;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class FlutterwaveBaseApi implements FlutterwaveApiContract
{
    use InitializesCredentials;
    use ProvidesBaseApiUrl;

    public function __construct(
        private readonly AbstractHeadersConfig $headers,
        private readonly string $accessToken,
        private readonly RetryHandler $retryHandler,
        private readonly RateLimiter $rateLimiter,
    ) {
        $this->initializeCredentials();
    }

    /**
     * Get the access token
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Get the headers
     */
    public function getHeaders(): AbstractHeadersConfig
    {
        return $this->headers;
    }

    /**
     * Execute a callback with retry and rate limiting logic
     */
    protected function executeWithRetry(callable $callback): mixed
    {
        // Apply rate limiting
        $this->rateLimiter->attempt();

        // Execute with retry logic
        return $this->retryHandler->execute($callback);
    }

    /**
     * Throw an exception for methods that are not implemented
     */
    protected function notImplemented(string $method): never
    {
        throw new ApiMethodNotImplementedException(
            \sprintf('%s does not support %s operation', static::class, $method)
        );
    }

    /**
     * List the items
     */
    public function list(): ApiResponse
    {
        return $this->executeWithRetry(function () {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->buildApiSpecificBaseUrl())
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                $this->logApiError('LIST', $this->buildApiSpecificBaseUrl(), $e);

                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Create an item
     */
    public function create(array $data): ApiResponse
    {
        return $this->executeWithRetry(function () use ($data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->post($this->buildApiSpecificBaseUrl(), $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                $this->logApiError('POST', $this->buildApiSpecificBaseUrl(), $e);

                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Retrieve an item
     *
     * @throws Exception
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->validateId($id);

        return $this->executeWithRetry(function () use ($id) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->get($this->buildApiSpecificBaseUrl().'/'.$id)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                $this->logApiError('GET', $this->buildApiSpecificBaseUrl().'/'.$id, $e);

                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Update an item
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->validateId($id);

        return $this->executeWithRetry(function () use ($id, $data) {
            try {
                $response = Http::timeout(config('flutterwave.timeout', 30))
                    ->withToken($this->getAccessToken())
                    ->withHeaders($this->getHeaders()->toArray())
                    ->put($this->buildApiSpecificBaseUrl().'/'.$id, $data)
                    ->throw();

                return ApiResponse::fromArray($response->json());
            } catch (RequestException $e) {
                $this->logApiError('PUT', $this->buildApiSpecificBaseUrl().'/'.$id, $e);

                throw $this->createApiException($e);
            }
        });
    }

    /**
     * Search for an item
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate entity ID
     */
    protected function validateId(string $id): void
    {
        if (empty($id)) {
            throw new Exception('Related entity ID is required');
        }

        // Prevent path traversal
        if (str_contains($id, '..') || str_contains($id, '/')) {
            throw new Exception('Invalid ID format: path traversal not allowed');
        }

        // Validate ID format (alphanumeric, dashes, underscores only)
        if (! preg_match('/^[a-zA-Z0-9_-]+$/', $id)) {
            throw new Exception('Invalid ID format: only alphanumeric characters, dashes, and underscores allowed');
        }
    }

    /**
     * Log API error
     */
    protected function logApiError(string $method, string $url, RequestException $e): void
    {
        $statusCode = $e->response?->status() ?? 500;
        $errorData = FlutterwaveErrorMapper::mapFromResponse($e->response?->body(), $statusCode);

        Log::error('Flutterwave API error', [
            'method' => $method,
            'url' => $url,
            'status' => $statusCode,
            'error_code' => $errorData->code->value,
            'error_type' => $errorData->type->value,
            'error_message' => $errorData->message,
            'user_friendly_message' => $errorData->getUserFriendlyMessage(),
            'technical_description' => $errorData->getTechnicalDescription(),
            'validation_errors' => $errorData->validationErrors,
            'is_retriable' => $errorData->isRetriable(),
            'is_client_error' => $errorData->isClientError(),
            'is_system_error' => $errorData->isSystemError(),
        ]);
    }

    /**
     * Create API exception from request exception
     */
    protected function createApiException(RequestException $e): FlutterwaveApiException
    {
        return FlutterwaveApiException::fromResponseBody(
            responseBody: $e->response?->body(),
            statusCode: $e->response?->status() ?? 500,
            previous: $e,
        );
    }
}
