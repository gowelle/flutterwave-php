<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

use Exception;
use Gowelle\Flutterwave\Data\ErrorData;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorCode;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorType;
use Gowelle\Flutterwave\Services\FlutterwaveErrorMapper;
use JsonException;

class FlutterwaveException extends Exception
{
    /**
     * The response data from Flutterwave API (if available).
     */
    protected ?array $responseData = null;

    /**
     * The HTTP status code (if applicable).
     */
    protected ?int $statusCode = null;

    /**
     * The structured error data (if available).
     */
    protected ?ErrorData $errorData = null;

    /**
     * The Flutterwave error code (if available).
     */
    protected ?FlutterwaveErrorCode $errorCode = null;

    /**
     * The Flutterwave error type (if available).
     */
    protected ?FlutterwaveErrorType $errorType = null;

    /**
     * Create a new Flutterwave exception.
     *
     * @param  Throwable|null  $previous
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create exception from API response.
     */
    public static function fromResponse(array $response, int $statusCode): static
    {
        // Parse error using error mapper
        $errorData = FlutterwaveErrorMapper::parseErrorFromArray($response, $statusCode);

        // Use user-friendly message
        $message = $errorData->getUserFriendlyMessage();

        $exception = new static($message, $statusCode);
        $exception->setResponseData($response);
        $exception->setStatusCode($statusCode);
        $exception->setErrorData($errorData);
        $exception->setErrorCode($errorData->code);
        $exception->setErrorType($errorData->type);

        return $exception;
    }

    /**
     * Create exception from response body string.
     */
    public static function fromResponseBody(?string $responseBody, int $statusCode): static
    {
        // Parse error using error mapper
        $errorData = FlutterwaveErrorMapper::mapFromResponse($responseBody, $statusCode);

        // Use user-friendly message
        $message = $errorData->getUserFriendlyMessage();

        $exception = new static($message, $statusCode);
        $exception->setStatusCode($statusCode);
        $exception->setErrorData($errorData);
        $exception->setErrorCode($errorData->code);
        $exception->setErrorType($errorData->type);

        // Try to parse response body for response data
        if (! empty($responseBody)) {
            try {
                $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

                if (\is_array($decoded)) {
                    $exception->setResponseData($decoded);
                }
            } catch (JsonException) {
                // Ignore JSON parsing errors
            }
        }

        return $exception;
    }

    /**
     * Create exception for authentication failure.
     */
    public static function authenticationFailed(string $message = 'Authentication failed'): static
    {
        return new static($message, 401);
    }

    /**
     * Create exception for invalid configuration.
     */
    public static function invalidConfiguration(string $message): static
    {
        return new static("Invalid configuration: {$message}", 500);
    }

    /**
     * Create exception for webhook verification failure.
     */
    public static function invalidWebhookSignature(): static
    {
        return new static('Invalid webhook signature', 403);
    }

    /**
     * Set response data.
     *
     * @return $this
     */
    public function setResponseData(array $data): static
    {
        $this->responseData = $data;

        return $this;
    }

    /**
     * Get response data.
     */
    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    /**
     * Set HTTP status code.
     *
     * @return $this
     */
    public function setStatusCode(int $code): static
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * Get HTTP status code.
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Check if this is a validation error.
     */
    public function isValidationError(): bool
    {
        return $this->statusCode === 422;
    }

    /**
     * Check if this is an authentication error.
     */
    public function isAuthenticationError(): bool
    {
        return $this->statusCode === 401;
    }

    /**
     * Check if this is a not found error.
     */
    public function isNotFoundError(): bool
    {
        return $this->statusCode === 404;
    }

    /**
     * Get detailed error message with validation errors if applicable
     */
    public function getDetailedErrorMessage(): string
    {
        // If we have error data, use it
        if ($this->errorData !== null) {
            return $this->errorData->getTechnicalDescription();
        }

        $message = $this->getMessage();

        if ($this->responseData && \is_array($this->responseData)) {
            $errors = [];

            // Check for validation errors in error.validation_errors (Flutterwave structure)
            if (isset($this->responseData['error']['validation_errors']) && \is_array($this->responseData['error']['validation_errors'])) {
                foreach ($this->responseData['error']['validation_errors'] as $validationError) {
                    if (\is_array($validationError) && isset($validationError['message'])) {
                        $field = $validationError['field_name'] ?? 'field';
                        $errors[] = "{$field}: {$validationError['message']}";
                    }
                }
            }

            // Fallback: check for validation errors in data.errors
            if (empty($errors) && isset($this->responseData['data']['errors']) && \is_array($this->responseData['data']['errors'])) {
                foreach ($this->responseData['data']['errors'] as $error) {
                    if (\is_array($error) && isset($error['message'])) {
                        $errors[] = $error['message'];
                    } elseif (\is_string($error)) {
                        $errors[] = $error;
                    }
                }
            }

            if (! empty($errors)) {
                $message .= ' ['.implode(', ', array_unique($errors)).']';
            }
        }

        return $message;
    }

    /**
     * Set error data.
     *
     * @return $this
     */
    public function setErrorData(ErrorData $errorData): static
    {
        $this->errorData = $errorData;

        return $this;
    }

    /**
     * Get error data.
     */
    public function getErrorData(): ?ErrorData
    {
        return $this->errorData;
    }

    /**
     * Set error code.
     *
     * @return $this
     */
    public function setErrorCode(FlutterwaveErrorCode $code): static
    {
        $this->errorCode = $code;

        return $this;
    }

    /**
     * Get error code.
     */
    public function getErrorCode(): ?FlutterwaveErrorCode
    {
        return $this->errorCode;
    }

    /**
     * Set error type.
     *
     * @return $this
     */
    public function setErrorType(FlutterwaveErrorType $type): static
    {
        $this->errorType = $type;

        return $this;
    }

    /**
     * Get error type.
     */
    public function getErrorType(): ?FlutterwaveErrorType
    {
        return $this->errorType;
    }

    /**
     * Get user-friendly error message.
     */
    public function getUserFriendlyMessage(): string
    {
        return $this->errorData?->getUserFriendlyMessage() ?? $this->getMessage();
    }

    /**
     * Check if this error is retriable.
     */
    public function isRetriable(): bool
    {
        return $this->errorData?->isRetriable() ?? false;
    }

    /**
     * Check if this is a client error.
     */
    public function isClientError(): bool
    {
        return $this->errorData?->isClientError() ?? false;
    }

    /**
     * Check if this is a system error.
     */
    public function isSystemError(): bool
    {
        return $this->errorData?->isSystemError() ?? false;
    }
}
