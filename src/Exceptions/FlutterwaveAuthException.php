<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

final class FlutterwaveAuthException extends FlutterwaveException
{
    public static function credentialsNotConfigured(): self
    {
        return new self(
            'Flutterwave credentials not configured. '
            .'Please set FLW_CLIENT_ID and FLW_CLIENT_SECRET in your .env file.',
        );
    }

    public static function invalidCredentials(string $message): self
    {
        return new self("Invalid Flutterwave credentials: {$message}");
    }

    public static function tokenRefreshFailed(string $message): self
    {
        return new self("Failed to refresh Flutterwave access token: {$message}");
    }
}
