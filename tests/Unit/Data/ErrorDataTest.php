<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ErrorData;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorCode;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorType;

describe('ErrorData', function () {
    it('creates with all properties', function () {
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::REQUEST_NOT_VALID,
            type: FlutterwaveErrorType::REQUEST_NOT_VALID,
            message: 'Invalid request',
            validationErrors: [
                ['field' => 'email', 'message' => 'Email is required'],
            ],
            httpStatusCode: 400,
        );

        expect($errorData)
            ->code->toBe(FlutterwaveErrorCode::REQUEST_NOT_VALID)
            ->type->toBe(FlutterwaveErrorType::REQUEST_NOT_VALID)
            ->message->toBe('Invalid request')
            ->validationErrors->toHaveCount(1)
            ->httpStatusCode->toBe(400);
    });

    it('creates with default values', function () {
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::UNKNOWN,
            type: FlutterwaveErrorType::UNKNOWN,
            message: 'Something went wrong',
        );

        expect($errorData)
            ->validationErrors->toBe([])
            ->httpStatusCode->toBe(500);
    });

    it('returns user-friendly message without validation errors', function () {
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::INTERNAL_SERVER_ERROR,
            type: FlutterwaveErrorType::INTERNAL_SERVER_ERROR,
            message: 'Internal server error',
        );

        expect($errorData->getUserFriendlyMessage())
            ->toBe('An unexpected error occurred on our end. Please try again in a few moments.');
    });

    it('returns user-friendly message with validation errors', function () {
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::UNPROCESSABLE,
            type: FlutterwaveErrorType::UNPROCESSABLE,
            message: 'Validation failed',
            validationErrors: [
                ['field' => 'email', 'message' => 'Invalid email format'],
                ['field' => 'amount', 'message' => 'Amount must be positive'],
            ],
        );

        $message = $errorData->getUserFriendlyMessage();

        expect($message)
            ->toContain('email: Invalid email format')
            ->toContain('amount: Amount must be positive');
    });

    it('returns original API message', function () {
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::FORBIDDEN,
            type: FlutterwaveErrorType::FORBIDDEN,
            message: 'Access denied for this resource',
        );

        expect($errorData->getApiMessage())->toBe('Access denied for this resource');
    });

    it('returns technical description without validation errors', function () {
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::UNAUTHORIZATION,
            type: FlutterwaveErrorType::UNAUTHORIZATION,
            message: 'Invalid token',
            httpStatusCode: 401,
        );

        $description = $errorData->getTechnicalDescription();

        expect($description)
            ->toContain('10401')
            ->toContain('UNAUTHORIZATION')
            ->toContain('Invalid token');
    });

    it('returns technical description with validation errors', function () {
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::UNPROCESSABLE,
            type: FlutterwaveErrorType::UNPROCESSABLE,
            message: 'Validation failed',
            validationErrors: [
                ['field' => 'currency', 'message' => 'Unsupported currency'],
            ],
        );

        $description = $errorData->getTechnicalDescription();

        expect($description)
            ->toContain('10422')
            ->toContain('Validation errors')
            ->toContain('currency: Unsupported currency');
    });

    it('checks if has validation errors', function () {
        $withErrors = new ErrorData(
            code: FlutterwaveErrorCode::UNPROCESSABLE,
            type: FlutterwaveErrorType::UNPROCESSABLE,
            message: 'Invalid',
            validationErrors: [['field' => 'test', 'message' => 'error']],
        );

        $withoutErrors = new ErrorData(
            code: FlutterwaveErrorCode::INTERNAL_SERVER_ERROR,
            type: FlutterwaveErrorType::INTERNAL_SERVER_ERROR,
            message: 'Server error',
        );

        expect($withErrors->hasValidationErrors())->toBeTrue();
        expect($withoutErrors->hasValidationErrors())->toBeFalse();
    });

    it('checks if error is retriable', function () {
        $retriable = new ErrorData(
            code: FlutterwaveErrorCode::INTERNAL_SERVER_ERROR,
            type: FlutterwaveErrorType::INTERNAL_SERVER_ERROR,
            message: 'Server error',
        );

        $notRetriable = new ErrorData(
            code: FlutterwaveErrorCode::UNAUTHORIZATION,
            type: FlutterwaveErrorType::UNAUTHORIZATION,
            message: 'Invalid credentials',
        );

        expect($retriable->isRetriable())->toBeTrue();
        expect($notRetriable->isRetriable())->toBeFalse();
    });

    it('checks if error is client error', function () {
        $clientErrors = [
            FlutterwaveErrorCode::REQUEST_NOT_VALID,
            FlutterwaveErrorCode::UNPROCESSABLE,
            FlutterwaveErrorCode::UNAUTHORIZATION,
            FlutterwaveErrorCode::FORBIDDEN,
            FlutterwaveErrorCode::RESOURCE_NOT_FOUND,
            FlutterwaveErrorCode::RESOURCE_CONFLICT,
        ];

        foreach ($clientErrors as $errorCode) {
            $errorData = new ErrorData(
                code: $errorCode,
                type: FlutterwaveErrorType::UNPROCESSABLE,
                message: 'Test',
            );
            expect($errorData->isClientError())->toBeTrue("ErrorCode {$errorCode->value} should be client error");
        }

        $serverError = new ErrorData(
            code: FlutterwaveErrorCode::INTERNAL_SERVER_ERROR,
            type: FlutterwaveErrorType::INTERNAL_SERVER_ERROR,
            message: 'Server error',
        );
        expect($serverError->isClientError())->toBeFalse();
    });

    it('checks if error is system error', function () {
        $systemError = new ErrorData(
            code: FlutterwaveErrorCode::INTERNAL_SERVER_ERROR,
            type: FlutterwaveErrorType::INTERNAL_SERVER_ERROR,
            message: 'Server error',
        );

        $clientError = new ErrorData(
            code: FlutterwaveErrorCode::REQUEST_NOT_VALID,
            type: FlutterwaveErrorType::REQUEST_NOT_VALID,
            message: 'Bad request',
        );

        expect($systemError->isSystemError())->toBeTrue();
        expect($clientError->isSystemError())->toBeFalse();
    });

    it('converts to array', function () {
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::UNPROCESSABLE,
            type: FlutterwaveErrorType::UNPROCESSABLE,
            message: 'Validation failed',
            validationErrors: [
                ['field' => 'email', 'message' => 'Required'],
            ],
            httpStatusCode: 422,
        );

        $array = $errorData->toArray();

        expect($array)
            ->toHaveKey('code', '10422')
            ->toHaveKey('type', 'UNPROCESSABLE')
            ->toHaveKey('message', 'Validation failed')
            ->toHaveKey('validation_errors')
            ->toHaveKey('http_status', 422);

        expect($array['validation_errors'])->toHaveCount(1);
    });

    it('handles all error codes', function (FlutterwaveErrorCode $errorCode) {
        $errorData = new ErrorData(
            code: $errorCode,
            type: FlutterwaveErrorType::UNKNOWN,
            message: 'Test message',
        );

        // All codes should return a non-empty user-friendly message
        expect($errorData->getUserFriendlyMessage())->not->toBeEmpty();

        // All codes should have an HTTP status code
        expect($errorCode->getHttpStatusCode())->toBeGreaterThan(0);
    })->with([
        'REQUEST_NOT_VALID' => [FlutterwaveErrorCode::REQUEST_NOT_VALID],
        'UNPROCESSABLE' => [FlutterwaveErrorCode::UNPROCESSABLE],
        'UNAUTHORIZATION' => [FlutterwaveErrorCode::UNAUTHORIZATION],
        'FORBIDDEN' => [FlutterwaveErrorCode::FORBIDDEN],
        'RESOURCE_NOT_FOUND' => [FlutterwaveErrorCode::RESOURCE_NOT_FOUND],
        'RESOURCE_CONFLICT' => [FlutterwaveErrorCode::RESOURCE_CONFLICT],
        'INTERNAL_SERVER_ERROR' => [FlutterwaveErrorCode::INTERNAL_SERVER_ERROR],
        'UNKNOWN' => [FlutterwaveErrorCode::UNKNOWN],
    ]);
});
