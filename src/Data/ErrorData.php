<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use Gowelle\Flutterwave\Enums\FlutterwaveErrorCode;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorType;

/**
 * Structured error data from Flutterwave API responses.
 */
final readonly class ErrorData
{
    /**
     * @param  FlutterwaveErrorCode  $code  The error code
     * @param  FlutterwaveErrorType  $type  The error type
     * @param  string  $message  The error message from the API
     * @param  array<int, array{field: string, message: string}>  $validationErrors  Validation errors if any
     * @param  int  $httpStatusCode  The HTTP status code
     */
    public function __construct(
        public FlutterwaveErrorCode $code,
        public FlutterwaveErrorType $type,
        public string $message,
        public array $validationErrors = [],
        public int $httpStatusCode = 500,
    ) {}

    /**
     * Get user-friendly error message.
     */
    public function getUserFriendlyMessage(): string
    {
        // If there are validation errors, include them
        if (! empty($this->validationErrors)) {
            $validationMessages = array_map(
                fn (array $error): string => "{$error['field']}: {$error['message']}",
                $this->validationErrors,
            );

            return $this->code->getMessage().' ('.implode(', ', $validationMessages).')';
        }

        // Otherwise, return the mapped user-friendly message
        return $this->code->getMessage();
    }

    /**
     * Get the original API error message.
     */
    public function getApiMessage(): string
    {
        return $this->message;
    }

    /**
     * Get technical description including all error details.
     */
    public function getTechnicalDescription(): string
    {
        $description = \sprintf(
            '[%s] %s: %s',
            $this->code->value,
            $this->type->value,
            $this->message,
        );

        if (! empty($this->validationErrors)) {
            $validationMessages = array_map(
                fn (array $error): string => "{$error['field']}: {$error['message']}",
                $this->validationErrors,
            );

            $description .= ' | Validation errors: '.implode(', ', $validationMessages);
        }

        return $description;
    }

    /**
     * Check if this error has validation errors.
     */
    public function hasValidationErrors(): bool
    {
        return ! empty($this->validationErrors);
    }

    /**
     * Check if this error is retriable.
     */
    public function isRetriable(): bool
    {
        return $this->code->isRetriable();
    }

    /**
     * Check if this is a client error.
     */
    public function isClientError(): bool
    {
        return $this->code->isClientError();
    }

    /**
     * Check if this is a system error.
     */
    public function isSystemError(): bool
    {
        return $this->code->isSystemError();
    }

    /**
     * Convert to array.
     *
     * @return array{code: string, type: string, message: string, validation_errors: array, http_status: int}
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code->value,
            'type' => $this->type->value,
            'message' => $this->message,
            'validation_errors' => $this->validationErrors,
            'http_status' => $this->httpStatusCode,
        ];
    }
}
