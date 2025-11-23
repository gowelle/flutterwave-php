<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Flutterwave payment gateway error codes and their user-friendly messages.
 *
 * Reference: https://developer.flutterwave.com/docs/common-errors
 * Maps Flutterwave API error codes to user-friendly messages.
 */
enum FlutterwaveErrorCode: string
{
    // Client validation errors (user action required)
    case REQUEST_NOT_VALID = '10400';           // Bad Request - Invalid parameters or missing data
    case UNPROCESSABLE = '10422';               // Validation failed - Invalid or incomplete fields

    // Authentication/Authorization errors
    case UNAUTHORIZATION = '10401';             // Authentication required or invalid credentials
    case FORBIDDEN = '10403';                   // Insufficient permissions

    // Resource errors
    case RESOURCE_NOT_FOUND = '10404';          // Resource not found
    case RESOURCE_CONFLICT = '10409';           // Duplicate or conflicting data

    // System errors
    case INTERNAL_SERVER_ERROR = '10500';       // Unexpected server error

    // Unknown/unmapped error
    case UNKNOWN = '00000';

    /**
     * Get error code from Flutterwave API response.
     *
     * @param  array  $errorResponse  The error response from Flutterwave API
     */
    public static function fromResponse(array $errorResponse): self
    {
        // Extract error code from error.code field
        $code = $errorResponse['error']['code'] ?? $errorResponse['code'] ?? null;

        if ($code === null) {
            return self::UNKNOWN;
        }

        $codeString = (string) $code;

        // Try to find matching error code
        foreach (self::cases() as $case) {
            if ($case->value === $codeString) {
                return $case;
            }
        }

        return self::UNKNOWN;
    }

    /**
     * Get error code from HTTP status code.
     *
     * @param  int  $statusCode  HTTP status code
     */
    public static function fromHttpStatus(int $statusCode): self
    {
        return match ($statusCode) {
            400 => self::REQUEST_NOT_VALID,
            401 => self::UNAUTHORIZATION,
            403 => self::FORBIDDEN,
            404 => self::RESOURCE_NOT_FOUND,
            409 => self::RESOURCE_CONFLICT,
            422 => self::UNPROCESSABLE,
            500 => self::INTERNAL_SERVER_ERROR,
            default => self::UNKNOWN,
        };
    }

    /**
     * Get user-friendly message for this error code.
     */
    public function getMessage(): string
    {
        return match ($this) {
            // Validation errors
            self::REQUEST_NOT_VALID => 'Your request contains invalid or missing information. Please check your details and try again.',
            self::UNPROCESSABLE => 'The information provided could not be processed. Please verify all fields are correct.',

            // Authentication/Authorization errors
            self::UNAUTHORIZATION => 'Authentication failed. Please check your credentials and try again.',
            self::FORBIDDEN => 'You do not have permission to perform this action. Please contact support if this is unexpected.',

            // Resource errors
            self::RESOURCE_NOT_FOUND => 'The requested resource could not be found. Please verify the information and try again.',
            self::RESOURCE_CONFLICT => 'A conflict occurred. This may be due to duplicate data or a version mismatch.',

            // System errors
            self::INTERNAL_SERVER_ERROR => 'An unexpected error occurred on our end. Please try again in a few moments.',

            // Unknown
            self::UNKNOWN => 'An unexpected error occurred. Please try again or contact support if the problem persists.',
        };
    }

    /**
     * Get technical description for this error code.
     */
    public function getTechnicalDescription(): string
    {
        return match ($this) {
            self::REQUEST_NOT_VALID => 'The request was rejected due to invalid parameters or missing data.',
            self::UNPROCESSABLE => 'The request was well-formed but contained invalid data.',
            self::UNAUTHORIZATION => 'The request requires authentication or has invalid credentials.',
            self::FORBIDDEN => 'The client does not have permission to access the resource.',
            self::RESOURCE_NOT_FOUND => 'The requested resource could not be found on the server.',
            self::RESOURCE_CONFLICT => 'A conflict occurred due to duplicate or conflicting data.',
            self::INTERNAL_SERVER_ERROR => 'An unexpected server error occurred while processing the request.',
            self::UNKNOWN => 'An unknown error occurred.',
        };
    }

    /**
     * Get HTTP status code associated with this error.
     */
    public function getHttpStatusCode(): int
    {
        return match ($this) {
            self::REQUEST_NOT_VALID => 400,
            self::UNAUTHORIZATION => 401,
            self::FORBIDDEN => 403,
            self::RESOURCE_NOT_FOUND => 404,
            self::RESOURCE_CONFLICT => 409,
            self::UNPROCESSABLE => 422,
            self::INTERNAL_SERVER_ERROR => 500,
            self::UNKNOWN => 500,
        };
    }

    /**
     * Determine if this is a retriable error (user should retry).
     */
    public function isRetriable(): bool
    {
        return match ($this) {
            self::INTERNAL_SERVER_ERROR => true,
            default => false,
        };
    }

    /**
     * Determine if this is a client error (user action needed).
     */
    public function isClientError(): bool
    {
        return match ($this) {
            self::REQUEST_NOT_VALID,
            self::UNPROCESSABLE,
            self::UNAUTHORIZATION,
            self::FORBIDDEN,
            self::RESOURCE_NOT_FOUND,
            self::RESOURCE_CONFLICT => true,

            default => false,
        };
    }

    /**
     * Determine if this is a system error (gateway/server issue).
     */
    public function isSystemError(): bool
    {
        return match ($this) {
            self::INTERNAL_SERVER_ERROR => true,
            default => false,
        };
    }

    /**
     * Determine if this requires admin action.
     */
    public function requiresAdminAction(): bool
    {
        return match ($this) {
            self::UNAUTHORIZATION,
            self::FORBIDDEN => true,

            default => false,
        };
    }
}
