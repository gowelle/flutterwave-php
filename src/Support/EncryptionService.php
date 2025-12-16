<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Support;

use Gowelle\Flutterwave\Exceptions\EncryptionException;

/**
 * Flutterwave Encryption Service
 *
 * Handles AES-256-GCM encryption of sensitive card data for Flutterwave API requests.
 * Uses OpenSSL with base64-encoded keys and nonces as specified in Flutterwave v4 API.
 *
 * @see https://developer.flutterwave.com/docs/encryption
 */
final class EncryptionService
{
    private const CIPHER = 'aes-256-gcm';

    private const NONCE_LENGTH = 12;

    /**
     * Create a new EncryptionService instance
     *
     * @param  string  $encryptionKey  Base64-encoded encryption key from Flutterwave dashboard
     *
     * @throws EncryptionException If the encryption key is invalid
     */
    public function __construct(
        private readonly string $encryptionKey,
    ) {
        $this->validateEncryptionKey($encryptionKey);
    }

    /**
     * Encrypt sensitive data using AES-256-GCM
     *
     * @param  string  $data  The plaintext data to encrypt
     * @param  string  $nonce  12-character nonce (initialization vector)
     * @return string Base64-encoded ciphertext with authentication tag
     *
     * @throws EncryptionException If encryption fails
     */
    public function encrypt(string $data, string $nonce): string
    {
        if (\strlen($nonce) !== self::NONCE_LENGTH) {
            throw EncryptionException::invalidNonce(
                "Nonce must be exactly 12 characters long, got {$this->nonce_length($nonce)}",
            );
        }

        try {
            // Decode the base64-encoded key
            $key = base64_decode($this->encryptionKey, true);
            if ($key === false) {
                throw new \Exception('Failed to decode encryption key');
            }

            // Encrypt using AES-256-GCM
            // The nonce is treated as the IV for GCM mode
            $tag = '';
            $encrypted = openssl_encrypt(
                $data,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $nonce,
                $tag,
            );

            if ($encrypted === false) {
                throw new \Exception(openssl_error_string() ?: 'OpenSSL encryption failed');
            }

            // Append the authentication tag to the ciphertext and base64-encode
            $ciphertext = $encrypted.$tag;

            return base64_encode($ciphertext);
        } catch (\Exception $e) {
            throw EncryptionException::encryptionFailed($e->getMessage(), $e);
        }
    }

    /**
     * Encrypt card data for Flutterwave direct charge requests
     *
     * Encrypts sensitive card fields (number, expiry month/year, CVV) individually
     * while keeping the nonce shared across all encrypted fields.
     *
     * @param  array{
     *     card_number: string,
     *     expiry_month: string,
     *     expiry_year: string,
     *     cvv?: string,
     * }  $card  Card details with raw (unencrypted) values
     * @return array{
     *     nonce: string,
     *     encrypted_card_number: string,
     *     encrypted_expiry_month: string,
     *     encrypted_expiry_year: string,
     *     encrypted_cvv?: string,
     * } Encrypted card data with shared nonce
     *
     * @throws EncryptionException If encryption fails or card data is invalid
     */
    public function encryptCardData(array $card): array
    {
        $this->validateCardData($card);

        $nonce = self::generateNonce();

        $encrypted = [
            'nonce' => $nonce,
            'encrypted_card_number' => $this->encrypt($card['card_number'], $nonce),
            'encrypted_expiry_month' => $this->encrypt($card['expiry_month'], $nonce),
            'encrypted_expiry_year' => $this->encrypt($card['expiry_year'], $nonce),
        ];

        // CVV is optional
        if (isset($card['cvv']) && ! empty($card['cvv'])) {
            $encrypted['encrypted_cvv'] = $this->encrypt($card['cvv'], $nonce);
        }

        return $encrypted;
    }

    /**
     * Generate a random 12-character nonce
     *
     * @return string Random 12-character alphanumeric string
     */
    public static function generateNonce(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $nonce = '';

        for ($i = 0; $i < self::NONCE_LENGTH; $i++) {
            $nonce .= $characters[random_int(0, \strlen($characters) - 1)];
        }

        return $nonce;
    }

    /**
     * Validate that the encryption key is properly formatted
     *
     * @throws EncryptionException If the key is invalid
     */
    private function validateEncryptionKey(string $key): void
    {
        if (empty($key)) {
            throw EncryptionException::missingEncryptionKey(
                'Encryption key is required for card payments. '
                .'Set FLUTTERWAVE_ENCRYPTION_KEY in your .env file.',
            );
        }

        // Try to decode the key to validate it's valid base64
        $decoded = base64_decode($key, true);
        if ($decoded === false || \strlen($decoded) !== 32) {
            throw EncryptionException::invalidEncryptionKey(
                'Encryption key must be a valid base64-encoded 256-bit (32 bytes) key.',
            );
        }
    }

    /**
     * Validate card data structure and required fields
     *
     * @throws EncryptionException If card data is invalid
     */
    private function validateCardData(array $card): void
    {
        $required = ['card_number', 'expiry_month', 'expiry_year'];

        foreach ($required as $field) {
            if (! isset($card[$field]) || empty($card[$field])) {
                throw EncryptionException::invalidCardData(
                    "Missing required card field: {$field}",
                );
            }
        }

        // Validate card number format (basic check - should be numeric and 13-19 digits)
        if (! preg_match('/^\d{13,19}$/', (string) $card['card_number'])) {
            throw EncryptionException::invalidCardData('Card number must be 13-19 digits');
        }

        // Validate expiry month (01-12)
        if (! preg_match('/^(0[1-9]|1[0-2])$/', (string) $card['expiry_month'])) {
            throw EncryptionException::invalidCardData('Expiry month must be between 01 and 12');
        }

        // Validate expiry year (2-digit or 4-digit)
        if (! preg_match('/^\d{2}|\d{4}$/', (string) $card['expiry_year'])) {
            throw EncryptionException::invalidCardData('Expiry year must be 2 or 4 digits');
        }

        // Validate CVV if provided
        if (isset($card['cvv']) && ! empty($card['cvv'])) {
            if (! preg_match('/^\d{3,4}$/', (string) $card['cvv'])) {
                throw EncryptionException::invalidCardData('CVV must be 3 or 4 digits');
            }
        }
    }

    /**
     * Helper to get nonce length for error messages
     */
    private function nonce_length(string $nonce): int
    {
        return \strlen($nonce);
    }
}
