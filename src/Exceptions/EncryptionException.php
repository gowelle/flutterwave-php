<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Exceptions;

use Throwable;

/**
 * Exception thrown when encryption operations fail
 */
final class EncryptionException extends FlutterwaveException
{
    /**
     * Create exception for missing encryption key
     */
    public static function missingEncryptionKey(string $message): self
    {
        return new self($message);
    }

    /**
     * Create exception for invalid encryption key
     */
    public static function invalidEncryptionKey(string $message): self
    {
        return new self($message);
    }

    /**
     * Create exception for invalid nonce
     */
    public static function invalidNonce(string $message): self
    {
        return new self($message);
    }

    /**
     * Create exception for encryption failures
     */
    public static function encryptionFailed(string $message, ?Throwable $previous = null): self
    {
        return new self("Encryption failed: {$message}", previous: $previous);
    }

    /**
     * Create exception for invalid card data
     */
    public static function invalidCardData(string $message): self
    {
        return new self("Invalid card data: {$message}");
    }
}
