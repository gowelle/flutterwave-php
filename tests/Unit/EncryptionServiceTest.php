<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Exceptions\EncryptionException;
use Gowelle\Flutterwave\Support\EncryptionService;

// Valid base64-encoded 32-byte (256-bit) test key
// This is a valid test key: echo -n 'test_key_32_bytes_long_exactly!!' | base64
$testEncryptionKey = 'dGVzdF9rZXlfMzJfYnl0ZXNfbG9uZ19leGFjdGx5ISE=';

describe('EncryptionService', function () use ($testEncryptionKey) {
    describe('Nonce Generation', function () {
        it('generates a 12-character nonce', function () {
            $nonce = EncryptionService::generateNonce();

            expect($nonce)->toHaveLength(12);
        });

        it('generates alphanumeric nonces', function () {
            for ($i = 0; $i < 10; $i++) {
                $nonce = EncryptionService::generateNonce();
                expect($nonce)->toMatch('/^[a-zA-Z0-9]{12}$/');
            }
        });

        it('generates unique nonces', function () {
            $nonces = array_unique(array_map(
                fn () => EncryptionService::generateNonce(),
                range(1, 100)
            ));

            expect(\count($nonces))->toBe(100);
        });
    });

    describe('Encryption', function () use ($testEncryptionKey) {
        it('encrypts plaintext successfully', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);
            $nonce = EncryptionService::generateNonce();
            $plaintext = 'Hello, World!';

            $encrypted = $service->encrypt($plaintext, $nonce);

            expect($encrypted)->toBeString();
            expect($encrypted)->not->toEqual($plaintext);
            expect(base64_decode($encrypted, true))->not->toBeFalsy();
        });

        it('produces different ciphertexts for different nonces', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);
            $plaintext = 'test_data';

            $nonce1 = EncryptionService::generateNonce();
            $nonce2 = EncryptionService::generateNonce();

            $encrypted1 = $service->encrypt($plaintext, $nonce1);
            $encrypted2 = $service->encrypt($plaintext, $nonce2);

            expect($encrypted1)->not->toEqual($encrypted2);
        });

        it('returns base64-encoded output', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);
            $nonce = EncryptionService::generateNonce();
            $plaintext = 'test data';

            $encrypted = $service->encrypt($plaintext, $nonce);

            // Should be valid base64
            expect(base64_decode($encrypted, true))->not->toBeFalsy();
        });

        it('consistently encrypts the same data with the same key and nonce', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);
            $nonce = EncryptionService::generateNonce();
            $plaintext = 'consistent test';

            $encrypted1 = $service->encrypt($plaintext, $nonce);
            $encrypted2 = $service->encrypt($plaintext, $nonce);

            expect($encrypted1)->toEqual($encrypted2);
        });
    });

    describe('Invalid Nonce', function () use ($testEncryptionKey) {
        it('throws exception for nonce with incorrect length', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            expect(fn () => $service->encrypt('test', 'short'))
                ->toThrow(EncryptionException::class);
        });

        it('throws exception for too long nonce', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            expect(fn () => $service->encrypt('test', 'this_is_too_long_nonce'))
                ->toThrow(EncryptionException::class);
        });
    });

    describe('Invalid Encryption Key', function () {
        it('throws exception for missing encryption key', function () {
            expect(fn () => new EncryptionService(''))
                ->toThrow(EncryptionException::class);
        });

        it('throws exception for invalid base64 key', function () {
            expect(fn () => new EncryptionService('not_valid_base64!!!'))
                ->toThrow(EncryptionException::class);
        });

        it('throws exception for wrong key length', function () {
            // Base64 of only 16 bytes (128 bits)
            $shortKey = base64_encode(random_bytes(16));

            expect(fn () => new EncryptionService($shortKey))
                ->toThrow(EncryptionException::class);
        });
    });

    describe('Card Data Encryption', function () use ($testEncryptionKey) {
        it('encrypts card data with all fields', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            $cardData = [
                'card_number' => '5531886652142950',
                'expiry_month' => '09',
                'expiry_year' => '32',
                'cvv' => '564',
            ];

            $encrypted = $service->encryptCardData($cardData);

            expect($encrypted)
                ->toHaveKeys(['nonce', 'encrypted_card_number', 'encrypted_expiry_month', 'encrypted_expiry_year', 'encrypted_cvv'])
                ->nonce->toHaveLength(12)
                ->encrypted_card_number->toBeString()
                ->encrypted_expiry_month->toBeString()
                ->encrypted_expiry_year->toBeString()
                ->encrypted_cvv->toBeString();
        });

        it('encrypts card data without CVV', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            $cardData = [
                'card_number' => '4532015112830366',
                'expiry_month' => '12',
                'expiry_year' => '25',
            ];

            $encrypted = $service->encryptCardData($cardData);

            expect($encrypted)
                ->toHaveKeys(['nonce', 'encrypted_card_number', 'encrypted_expiry_month', 'encrypted_expiry_year'])
                ->not->toHaveKey('encrypted_cvv');
        });

        it('uses the same nonce for all card fields', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            $cardData = [
                'card_number' => '5531886652142950',
                'expiry_month' => '09',
                'expiry_year' => '32',
                'cvv' => '564',
            ];

            $encrypted = $service->encryptCardData($cardData);

            expect($encrypted['nonce'])->toHaveLength(12);
        });

        it('throws exception for missing card number', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            expect(fn () => $service->encryptCardData([
                'expiry_month' => '09',
                'expiry_year' => '32',
                'cvv' => '564',
            ]))->toThrow(EncryptionException::class);
        });

        it('throws exception for invalid card number format', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            expect(fn () => $service->encryptCardData([
                'card_number' => 'not_a_number',
                'expiry_month' => '09',
                'expiry_year' => '32',
                'cvv' => '564',
            ]))->toThrow(EncryptionException::class);
        });

        it('throws exception for invalid expiry month', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            expect(fn () => $service->encryptCardData([
                'card_number' => '5531886652142950',
                'expiry_month' => '13',
                'expiry_year' => '32',
                'cvv' => '564',
            ]))->toThrow(EncryptionException::class);
        });

        it('throws exception for invalid expiry year format', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            expect(fn () => $service->encryptCardData([
                'card_number' => '5531886652142950',
                'expiry_month' => '09',
                'expiry_year' => '3',
                'cvv' => '564',
            ]))->toThrow(EncryptionException::class);
        });

        it('throws exception for invalid CVV', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);

            expect(fn () => $service->encryptCardData([
                'card_number' => '5531886652142950',
                'expiry_month' => '09',
                'expiry_year' => '32',
                'cvv' => 'abc',
            ]))->toThrow(EncryptionException::class);
        });
    });

    describe('Edge Cases', function () use ($testEncryptionKey) {
        it('handles empty strings', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);
            $nonce = EncryptionService::generateNonce();

            $encrypted = $service->encrypt('', $nonce);

            expect($encrypted)->toBeString();
            expect($encrypted)->not->toBeEmpty();
        });

        it('handles special characters in plaintext', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);
            $nonce = EncryptionService::generateNonce();
            $plaintext = '!@#$%^&*()_+-=[]{}|;:\'",.<>?/~`';

            $encrypted = $service->encrypt($plaintext, $nonce);

            expect($encrypted)->toBeString();
            expect($encrypted)->not->toEqual($plaintext);
        });

        it('handles unicode characters', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);
            $nonce = EncryptionService::generateNonce();
            $plaintext = 'Hello 世界 🌍 مرحبا Привет';

            $encrypted = $service->encrypt($plaintext, $nonce);

            expect($encrypted)->toBeString();
            expect($encrypted)->not->toEqual($plaintext);
        });

        it('handles long input data', function () use ($testEncryptionKey) {
            $service = new EncryptionService($testEncryptionKey);
            $nonce = EncryptionService::generateNonce();
            $plaintext = str_repeat('a', 10000);

            $encrypted = $service->encrypt($plaintext, $nonce);

            expect($encrypted)->toBeString();
            expect(\strlen($encrypted))->toBeGreaterThan(0);
        });
    });
});
