<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

use Exception;
use Gowelle\Flutterwave\Services\FlutterwaveErrorMapper;
use Throwable;

final class FlutterwaveApiException extends FlutterwaveException
{
    public function __construct(
        string $message,
        ?int $statusCode = null,
        private readonly ?string $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);

        if ($statusCode !== null) {
            $this->setStatusCode($statusCode);
        }

        // Parse and set response data and error data from JSON response body
        $this->parseAndSetResponseData();
    }

    /**
     * Create exception from response body using error mapper.
     */
    public static function fromResponseBody(?string $responseBody, int $statusCode, ?Throwable $previous = null): static
    {
        // Parse error using error mapper
        $errorData = FlutterwaveErrorMapper::mapFromResponse($responseBody, $statusCode);

        // Use user-friendly message
        $message = $errorData->getUserFriendlyMessage();

        $exception = new self(
            message: $message,
            statusCode: $statusCode,
            responseBody: $responseBody,
            previous: $previous,
        );

        // Set error data, code, and type
        $exception->setErrorData($errorData);
        $exception->setErrorCode($errorData->code);
        $exception->setErrorType($errorData->type);

        return $exception;
    }

    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * Parse response body JSON and set response data and error data
     */
    private function parseAndSetResponseData(): void
    {
        if (empty($this->responseBody)) {
            return;
        }

        try {
            $decoded = json_decode($this->responseBody, true);

            if (\is_array($decoded)) {
                $this->setResponseData($decoded);

                // Parse error data using error mapper
                $errorData = FlutterwaveErrorMapper::parseErrorFromArray($decoded, $this->statusCode ?? 500);
                $this->setErrorData($errorData);
                $this->setErrorCode($errorData->code);
                $this->setErrorType($errorData->type);
            }
        } catch (Exception) {
            // If JSON decoding fails, try to create error from HTTP status
            if ($this->statusCode !== null) {
                $errorData = FlutterwaveErrorMapper::mapFromResponse(null, $this->statusCode);
                $this->setErrorData($errorData);
                $this->setErrorCode($errorData->code);
                $this->setErrorType($errorData->type);
            }
        }
    }
}
