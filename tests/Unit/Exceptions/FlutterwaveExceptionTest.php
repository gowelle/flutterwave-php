<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Data\ErrorData;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorCode;
use Gowelle\Flutterwave\Enums\FlutterwaveErrorType;
use Gowelle\Flutterwave\Exceptions\FlutterwaveException;

describe('FlutterwaveException', function () {
    it('creates basic exception with message and code', function () {
        $exception = new FlutterwaveException('Test error', 500);

        expect($exception)
            ->getMessage()->toBe('Test error')
            ->getCode()->toBe(500);
    });

    it('creates exception from API response array', function () {
        $response = [
            'status' => 'error',
            'message' => 'Validation failed',
            'error' => [
                'code' => '10422',
                'type' => 'UNPROCESSABLE',
                'message' => 'Invalid request data',
            ],
        ];

        $exception = FlutterwaveException::fromResponse($response, 422);

        expect($exception)
            ->getStatusCode()->toBe(422)
            ->getResponseData()->toBe($response)
            ->getErrorCode()->toBeInstanceOf(FlutterwaveErrorCode::class)
            ->getErrorType()->toBeInstanceOf(FlutterwaveErrorType::class);
    });

    it('creates exception from response body string', function () {
        $responseBody = json_encode([
            'status' => 'error',
            'message' => 'Unauthorized',
            'error' => [
                'code' => '10401',
                'type' => 'UNAUTHORIZATION',
            ],
        ]);

        $exception = FlutterwaveException::fromResponseBody($responseBody, 401);

        expect($exception)
            ->getStatusCode()->toBe(401)
            ->getResponseData()->toBeArray()
            ->isAuthenticationError()->toBeTrue();
    });

    it('handles null response body', function () {
        $exception = FlutterwaveException::fromResponseBody(null, 500);

        expect($exception)
            ->getStatusCode()->toBe(500)
            ->getResponseData()->toBeNull();
    });

    it('handles invalid JSON response body gracefully', function () {
        $exception = FlutterwaveException::fromResponseBody('not valid json', 500);

        expect($exception)
            ->getStatusCode()->toBe(500)
            ->getResponseData()->toBeNull();
    });

    it('creates authentication failed exception', function () {
        $exception = FlutterwaveException::authenticationFailed();

        expect($exception)
            ->getMessage()->toBe('Authentication failed')
            ->getCode()->toBe(401);
    });

    it('creates authentication failed exception with custom message', function () {
        $exception = FlutterwaveException::authenticationFailed('Invalid API key');

        expect($exception->getMessage())->toBe('Invalid API key');
    });

    it('creates invalid configuration exception', function () {
        $exception = FlutterwaveException::invalidConfiguration('Missing client_id');

        expect($exception)
            ->getMessage()->toBe('Invalid configuration: Missing client_id')
            ->getCode()->toBe(500);
    });

    it('creates invalid webhook signature exception', function () {
        $exception = FlutterwaveException::invalidWebhookSignature();

        expect($exception)
            ->getMessage()->toBe('Invalid webhook signature')
            ->getCode()->toBe(403);
    });

    it('checks if validation error', function () {
        $validationException = FlutterwaveException::fromResponseBody(
            json_encode(['status' => 'error']),
            422,
        );
        $serverException = FlutterwaveException::fromResponseBody(
            json_encode(['status' => 'error']),
            500,
        );

        expect($validationException->isValidationError())->toBeTrue();
        expect($serverException->isValidationError())->toBeFalse();
    });

    it('checks if authentication error', function () {
        $authException = FlutterwaveException::fromResponseBody(
            json_encode(['status' => 'error']),
            401,
        );
        $otherException = new FlutterwaveException('Other error', 400);
        $otherException->setStatusCode(400);

        expect($authException->isAuthenticationError())->toBeTrue();
        expect($otherException->isAuthenticationError())->toBeFalse();
    });

    it('checks if not found error', function () {
        $notFoundException = FlutterwaveException::fromResponseBody(
            json_encode(['status' => 'error']),
            404,
        );
        $otherException = FlutterwaveException::fromResponseBody(
            json_encode(['status' => 'error']),
            500,
        );

        expect($notFoundException->isNotFoundError())->toBeTrue();
        expect($otherException->isNotFoundError())->toBeFalse();
    });

    it('returns user-friendly message from error data', function () {
        $exception = FlutterwaveException::fromResponseBody(
            json_encode([
                'error' => [
                    'code' => '10401',
                    'type' => 'UNAUTHORIZATION',
                    'message' => 'Invalid credentials',
                ],
            ]),
            401,
        );

        // Should return the user-friendly message from the error code
        expect($exception->getUserFriendlyMessage())->not->toBeEmpty();
    });

    it('returns exception message as user-friendly message when no error data', function () {
        $exception = new FlutterwaveException('Direct message', 500);

        expect($exception->getUserFriendlyMessage())->toBe('Direct message');
    });

    it('gets detailed error message with validation errors', function () {
        $exception = new FlutterwaveException('Validation failed', 422);
        $exception->setResponseData([
            'error' => [
                'validation_errors' => [
                    ['field_name' => 'email', 'message' => 'Email is required'],
                    ['field_name' => 'amount', 'message' => 'Amount must be positive'],
                ],
            ],
        ]);

        $detailedMessage = $exception->getDetailedErrorMessage();

        expect($detailedMessage)
            ->toContain('email: Email is required')
            ->toContain('amount: Amount must be positive');
    });

    it('checks if error is retriable', function () {
        $retriableException = FlutterwaveException::fromResponseBody(
            json_encode([
                'error' => [
                    'code' => '10500',
                    'type' => 'INTERNAL_SERVER_ERROR',
                ],
            ]),
            500,
        );

        $nonRetriableException = FlutterwaveException::fromResponseBody(
            json_encode([
                'error' => [
                    'code' => '10401',
                    'type' => 'UNAUTHORIZATION',
                ],
            ]),
            401,
        );

        expect($retriableException->isRetriable())->toBeTrue();
        expect($nonRetriableException->isRetriable())->toBeFalse();
    });

    it('checks if client error', function () {
        $clientException = FlutterwaveException::fromResponseBody(
            json_encode([
                'error' => [
                    'code' => '10400',
                    'type' => 'REQUEST_NOT_VALID',
                ],
            ]),
            400,
        );

        $serverException = FlutterwaveException::fromResponseBody(
            json_encode([
                'error' => [
                    'code' => '10500',
                    'type' => 'INTERNAL_SERVER_ERROR',
                ],
            ]),
            500,
        );

        expect($clientException->isClientError())->toBeTrue();
        expect($serverException->isClientError())->toBeFalse();
    });

    it('checks if system error', function () {
        $serverException = FlutterwaveException::fromResponseBody(
            json_encode([
                'error' => [
                    'code' => '10500',
                    'type' => 'INTERNAL_SERVER_ERROR',
                ],
            ]),
            500,
        );

        $clientException = FlutterwaveException::fromResponseBody(
            json_encode([
                'error' => [
                    'code' => '10400',
                    'type' => 'REQUEST_NOT_VALID',
                ],
            ]),
            400,
        );

        expect($serverException->isSystemError())->toBeTrue();
        expect($clientException->isSystemError())->toBeFalse();
    });

    it('sets and gets error data', function () {
        $exception = new FlutterwaveException('Test', 500);
        $errorData = new ErrorData(
            code: FlutterwaveErrorCode::INTERNAL_SERVER_ERROR,
            type: FlutterwaveErrorType::INTERNAL_SERVER_ERROR,
            message: 'Server error',
        );

        $exception->setErrorData($errorData);

        expect($exception->getErrorData())
            ->toBeInstanceOf(ErrorData::class)
            ->code->toBe(FlutterwaveErrorCode::INTERNAL_SERVER_ERROR);
    });

    it('sets and gets error code', function () {
        $exception = new FlutterwaveException('Test', 500);
        $exception->setErrorCode(FlutterwaveErrorCode::FORBIDDEN);

        expect($exception->getErrorCode())->toBe(FlutterwaveErrorCode::FORBIDDEN);
    });

    it('sets and gets error type', function () {
        $exception = new FlutterwaveException('Test', 500);
        $exception->setErrorType(FlutterwaveErrorType::FORBIDDEN);

        expect($exception->getErrorType())->toBe(FlutterwaveErrorType::FORBIDDEN);
    });

    it('maintains fluent interface for setters', function () {
        $exception = new FlutterwaveException('Test', 500);

        $result = $exception
            ->setStatusCode(422)
            ->setResponseData(['test' => 'data'])
            ->setErrorCode(FlutterwaveErrorCode::UNPROCESSABLE)
            ->setErrorType(FlutterwaveErrorType::UNPROCESSABLE);

        expect($result)->toBeInstanceOf(FlutterwaveException::class);
        expect($result->getStatusCode())->toBe(422);
    });

    it('preserves previous exception in chain', function () {
        $previous = new Exception('Original error');
        $exception = new FlutterwaveException('Wrapped error', 500, $previous);

        expect($exception->getPrevious())
            ->toBeInstanceOf(Exception::class)
            ->getMessage()->toBe('Original error');
    });
});
