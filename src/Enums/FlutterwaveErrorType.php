<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Flutterwave error type categorization.
 *
 * Reference: https://developer.flutterwave.com/docs/common-errors
 * Represents the 'error.type' field in Flutterwave API error responses.
 */
enum FlutterwaveErrorType: string
{
    case REQUEST_NOT_VALID = 'REQUEST_NOT_VALID';
    case UNAUTHORIZATION = 'UNAUTHORIZATION';
    case FORBIDDEN = 'FORBIDDEN';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case RESOURCE_CONFLICT = 'RESOURCE_CONFLICT';
    case UNPROCESSABLE = 'UNPROCESSABLE';
    case INTERNAL_SERVER_ERROR = 'INTERNAL_SERVER_ERROR';
    case UNKNOWN = 'UNKNOWN';

    /**
     * Get error type from Flutterwave API response.
     *
     * @param  array  $errorResponse  The error response from Flutterwave API
     */
    public static function fromResponse(array $errorResponse): self
    {
        $type = $errorResponse['error']['type'] ?? $errorResponse['type'] ?? null;

        if ($type === null) {
            return self::UNKNOWN;
        }

        $typeString = (string) $type;

        // Try to find matching error type
        foreach (self::cases() as $case) {
            if ($case->value === $typeString) {
                return $case;
            }
        }

        return self::UNKNOWN;
    }

    /**
     * Get the corresponding error code for this error type.
     */
    public function getErrorCode(): FlutterwaveErrorCode
    {
        return match ($this) {
            self::REQUEST_NOT_VALID => FlutterwaveErrorCode::REQUEST_NOT_VALID,
            self::UNAUTHORIZATION => FlutterwaveErrorCode::UNAUTHORIZATION,
            self::FORBIDDEN => FlutterwaveErrorCode::FORBIDDEN,
            self::RESOURCE_NOT_FOUND => FlutterwaveErrorCode::RESOURCE_NOT_FOUND,
            self::RESOURCE_CONFLICT => FlutterwaveErrorCode::RESOURCE_CONFLICT,
            self::UNPROCESSABLE => FlutterwaveErrorCode::UNPROCESSABLE,
            self::INTERNAL_SERVER_ERROR => FlutterwaveErrorCode::INTERNAL_SERVER_ERROR,
            self::UNKNOWN => FlutterwaveErrorCode::UNKNOWN,
        };
    }

    /**
     * Get human-readable description of the error type.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::REQUEST_NOT_VALID => 'Invalid request parameters or missing data',
            self::UNAUTHORIZATION => 'Authentication required or invalid credentials',
            self::FORBIDDEN => 'Insufficient permissions to access resource',
            self::RESOURCE_NOT_FOUND => 'Requested resource does not exist',
            self::RESOURCE_CONFLICT => 'Duplicate or conflicting resource data',
            self::UNPROCESSABLE => 'Validation failed on request data',
            self::INTERNAL_SERVER_ERROR => 'Server encountered an unexpected error',
            self::UNKNOWN => 'Unknown error type',
        };
    }
}
