<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Data\ErrorData;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorCode;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorType;
use JsonException;

/**
 * Service for mapping Flutterwave API error responses to structured error data.
 */
final class FlutterwaveErrorMapper
{
    /**
     * Parse error from API response body and HTTP status code.
     *
     * @param  string|null  $responseBody  The response body from the API
     * @param  int|null  $httpStatusCode  The HTTP status code
     */
    public static function mapFromResponse(?string $responseBody, ?int $httpStatusCode = null): ErrorData
    {
        $httpStatusCode ??= 500;

        // If no response body, create error from HTTP status code
        if (empty($responseBody)) {
            return self::createErrorFromHttpStatus($httpStatusCode);
        }

        // Parse JSON response
        try {
            $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);

            if (! \is_array($decoded)) {
                return self::createErrorFromHttpStatus($httpStatusCode);
            }

            return self::parseErrorFromArray($decoded, $httpStatusCode);
        } catch (JsonException) {
            return self::createErrorFromHttpStatus($httpStatusCode);
        }
    }

    /**
     * Parse error from array response.
     *
     * @param  array  $response  The decoded API response
     * @param  int  $httpStatusCode  The HTTP status code
     */
    public static function parseErrorFromArray(array $response, int $httpStatusCode): ErrorData
    {
        // Extract error object from response
        $errorObj = $response['error'] ?? [];

        if (! \is_array($errorObj)) {
            return self::createErrorFromHttpStatus($httpStatusCode);
        }

        // Extract error code, type, and message
        $errorCode = self::extractErrorCode($errorObj, $httpStatusCode);
        $errorType = self::extractErrorType($errorObj);
        $message = self::extractErrorMessage($errorObj, $response);
        $validationErrors = self::extractValidationErrors($errorObj);

        return new ErrorData(
            code: $errorCode,
            type: $errorType,
            message: $message,
            validationErrors: $validationErrors,
            httpStatusCode: $httpStatusCode,
        );
    }

    /**
     * Extract error code from error object.
     *
     * @param  array  $errorObj  The error object
     * @param  int  $httpStatusCode  The HTTP status code
     */
    private static function extractErrorCode(array $errorObj, int $httpStatusCode): FlutterwaveErrorCode
    {
        // Try to get from error.code field
        if (isset($errorObj['code'])) {
            $code = FlutterwaveErrorCode::fromResponse(['error' => ['code' => $errorObj['code']]]);

            if ($code !== FlutterwaveErrorCode::UNKNOWN) {
                return $code;
            }
        }

        // Fallback to HTTP status code
        return FlutterwaveErrorCode::fromHttpStatus($httpStatusCode);
    }

    /**
     * Extract error type from error object.
     *
     * @param  array  $errorObj  The error object
     */
    private static function extractErrorType(array $errorObj): FlutterwaveErrorType
    {
        return FlutterwaveErrorType::fromResponse(['error' => ['type' => $errorObj['type'] ?? null]]);
    }

    /**
     * Extract error message from error object.
     *
     * @param  array  $errorObj  The error object
     * @param  array  $response  The full response
     */
    private static function extractErrorMessage(array $errorObj, array $response): string
    {
        // Try error.message first
        if (isset($errorObj['message']) && \is_string($errorObj['message'])) {
            return $errorObj['message'];
        }

        // Fallback to root-level message
        if (isset($response['message']) && \is_string($response['message'])) {
            return $response['message'];
        }

        return 'An error occurred while processing your request';
    }

    /**
     * Extract validation errors from error object.
     *
     * @param  array  $errorObj  The error object
     * @return array<int, array{field: string, message: string}>
     */
    private static function extractValidationErrors(array $errorObj): array
    {
        $validationErrors = [];

        if (! isset($errorObj['validation_errors']) || ! \is_array($errorObj['validation_errors'])) {
            return $validationErrors;
        }

        foreach ($errorObj['validation_errors'] as $validationError) {
            if (! \is_array($validationError)) {
                continue;
            }

            $field = $validationError['field'] ?? $validationError['field_name'] ?? 'unknown';
            $message = $validationError['message'] ?? 'Invalid value';

            $validationErrors[] = [
                'field' => (string) $field,
                'message' => (string) $message,
            ];
        }

        return $validationErrors;
    }

    /**
     * Create error data from HTTP status code when response body is unavailable.
     *
     * @param  int  $httpStatusCode  The HTTP status code
     */
    private static function createErrorFromHttpStatus(int $httpStatusCode): ErrorData
    {
        $errorCode = FlutterwaveErrorCode::fromHttpStatus($httpStatusCode);

        // Infer error type from error code
        $errorType = match ($errorCode) {
            FlutterwaveErrorCode::REQUEST_NOT_VALID => FlutterwaveErrorType::REQUEST_NOT_VALID,
            FlutterwaveErrorCode::UNAUTHORIZATION => FlutterwaveErrorType::UNAUTHORIZATION,
            FlutterwaveErrorCode::FORBIDDEN => FlutterwaveErrorType::FORBIDDEN,
            FlutterwaveErrorCode::RESOURCE_NOT_FOUND => FlutterwaveErrorType::RESOURCE_NOT_FOUND,
            FlutterwaveErrorCode::RESOURCE_CONFLICT => FlutterwaveErrorType::RESOURCE_CONFLICT,
            FlutterwaveErrorCode::UNPROCESSABLE => FlutterwaveErrorType::UNPROCESSABLE,
            FlutterwaveErrorCode::INTERNAL_SERVER_ERROR => FlutterwaveErrorType::INTERNAL_SERVER_ERROR,
            default => FlutterwaveErrorType::UNKNOWN,
        };

        return new ErrorData(
            code: $errorCode,
            type: $errorType,
            message: $errorCode->getTechnicalDescription(),
            validationErrors: [],
            httpStatusCode: $httpStatusCode,
        );
    }
}
