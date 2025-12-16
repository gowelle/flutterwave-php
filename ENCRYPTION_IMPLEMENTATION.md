# Flutterwave PHP SDK - AES-256-GCM Encryption Implementation

## Overview

This document describes the encryption implementation for the Flutterwave PHP SDK, which follows the [Flutterwave v4 API encryption specification](https://developer.flutterwave.com/docs/encryption).

## What Was Implemented

### 1. EncryptionService (`src/Support/EncryptionService.php`)

A comprehensive encryption service that handles AES-256-GCM encryption of sensitive card data.

**Key Features:**

- **AES-256-GCM Encryption**: Uses PHP's OpenSSL extension with 256-bit keys
- **Nonce Generation**: Generates random 12-character alphanumeric nonces required by Flutterwave
- **Base64 Encoding**: Automatically encodes encrypted output in base64 format
- **Card Data Encryption**: Encrypts sensitive card fields (number, expiry, CVV) with a shared nonce

**Public Methods:**

- `__construct(string $encryptionKey)` - Initialize with base64-encoded encryption key
- `encrypt(string $data, string $nonce): string` - Encrypt plaintext data
- `encryptCardData(array $card): array` - Encrypt card information
- `generateNonce(): string` - Generate a random 12-character nonce (static)

**Validation:**

- Validates encryption key format (must be base64-encoded 32 bytes)
- Validates nonce length (must be exactly 12 characters)
- Validates card data (card number, expiry month/year, optional CVV)

### 2. EncryptionException (`src/Exceptions/EncryptionException.php`)

Exception class for encryption-related errors with static factory methods:

- `missingEncryptionKey(string $message)`
- `invalidEncryptionKey(string $message)`
- `invalidNonce(string $message)`
- `encryptionFailed(string $message, ?Throwable $previous)`
- `invalidCardData(string $message)`

### 3. Updated FlutterwaveConfig (`src/Data/FlutterwaveConfig.php`)

Added support for encryption key configuration:

- New `encryptionKey` parameter in constructor (nullable)
- Automatically loads from `flutterwave.encryption_key` config via `fromConfig()`

### 4. Enhanced ChargeRequestBuilder (`src/Builders/ChargeRequestBuilder.php`)

Added convenience method for card payments with automatic encryption:

```php
$request = ChargeRequestBuilder::for('ORDER-123')
    ->amount(150, 'NGN')
    ->customer('user@example.com', 'John Doe')
    ->card(
        cardNumber: '5531886652142950',
        cvv: '564',
        expiryMonth: '09',
        expiryYear: '32',
        billingAddress: [
            'city' => 'Lagos',
            'country' => 'NG',
            'line1' => '123 Main St',
            'state' => 'Lagos',
            'postal_code' => '100001',
        ]
    )
    ->redirectUrl('https://example.com/callback')
    ->build();
```

**Features:**

- Automatically initializes EncryptionService from config
- Encrypts all card data with a single shared nonce
- Removes plaintext card data from output
- Supports optional billing address
- Chainable API

### 5. Comprehensive Test Suites

#### `tests/Unit/EncryptionServiceTest.php` (24 tests)

Tests for core encryption functionality:

- Nonce generation (uniqueness, format, length)
- Basic encryption (output format, consistency)
- Invalid nonce handling
- Invalid encryption key handling
- Card data encryption (with/without CVV)
- Edge cases (empty strings, special chars, unicode, long data)

#### `tests/Unit/ChargeRequestBuilderEncryptionTest.php` (10 tests)

Tests for builder integration:

- Encrypted card request building
- Nonce inclusion and encryption
- Field encryption verification (ensures no plaintext card data)
- Optional CVV handling
- Billing address support
- Method chaining
- Encryption service injection
- Complete charge request scenarios

**Total: 34 tests, all passing ✓**

## Configuration

### Environment Variables

Add to your `.env` file:

```env
FLUTTERWAVE_ENCRYPTION_KEY=your_base64_encoded_256bit_key_here
```

Get your encryption key from your Flutterwave dashboard:

1. Go to Settings > API
2. Find your encryption key
3. Ensure it's base64-encoded (32 bytes when decoded)

### Laravel Configuration

The SDK automatically loads the encryption key from `config/flutterwave.php`:

```php
'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
```

## Usage Examples

### Basic Card Encryption

```php
use Gowelle\Flutterwave\Support\EncryptionService;

$encryptionService = new EncryptionService(config('flutterwave.encryption_key'));

// Encrypt individual data
$nonce = EncryptionService::generateNonce();
$encrypted = $encryptionService->encrypt('5531886652142950', $nonce);

// Encrypt card data
$cardData = [
    'card_number' => '5531886652142950',
    'expiry_month' => '09',
    'expiry_year' => '32',
    'cvv' => '564',
];

$encrypted = $encryptionService->encryptCardData($cardData);
// Returns: [
//     'nonce' => 'abc123def456',
//     'encrypted_card_number' => 'sAE3hEDaDQ+yLzo4Py+Lx15OZjBGduHu/DcdILh3En0=',
//     'encrypted_expiry_month' => 'sQpvQEb7GrUCjPuEN/NmHiPl',
//     'encrypted_expiry_year' => 'sgHNEDkJ/RmwuWWq/RymToU5',
//     'encrypted_cvv' => 'tAUzH7Qjma7diGdi7938F/ESNA==',
// ]
```

### Using ChargeRequestBuilder

```php
use Gowelle\Flutterwave\Builders\ChargeRequestBuilder;

$request = ChargeRequestBuilder::for('ORDER-001')
    ->amount(150.00, 'NGN')
    ->customer('customer@example.com', 'John Doe', '+234812345678')
    ->card(
        cardNumber: '5531886652142950',
        cvv: '564',
        expiryMonth: '09',
        expiryYear: '32'
    )
    ->redirectUrl('https://example.com/callback')
    ->meta(['order_id' => 'ORD-001'])
    ->build();

// $request now contains encrypted card data, safe to send to Flutterwave API
```

### Direct Charge Service with Encryption

```php
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;

$service = app(FlutterwaveDirectChargeService::class);

$charge = $service->create([
    'amount' => 10000,
    'currency' => 'TZS',
    'reference' => 'ORDER-' . uniqid(),
    'customer' => [
        'email' => 'customer@example.com',
        'name' => 'John Doe',
    ],
    'payment_method' => [
        'type' => 'card',
        'card' => [
            'nonce' => EncryptionService::generateNonce(),
            'encrypted_card_number' => $encryptedCard['encrypted_card_number'],
            'encrypted_expiry_month' => $encryptedCard['encrypted_expiry_month'],
            'encrypted_expiry_year' => $encryptedCard['encrypted_expiry_year'],
            'encrypted_cvv' => $encryptedCard['encrypted_cvv'],
        ],
    ],
    'redirect_url' => route('payment.callback'),
]);
```

## Security Considerations

1. **Never log card data**: The SDK automatically prevents logging of sensitive card information
2. **Use HTTPS**: Always transmit encrypted data over HTTPS
3. **Secure key storage**: Store `FLUTTERWAVE_ENCRYPTION_KEY` securely in environment variables
4. **Key rotation**: Rotate encryption keys periodically and follow Flutterwave's key management guidelines
5. **Production keys**: Use different keys for staging and production environments

## Compliance

- **AES-256-GCM**: Industry-standard authenticated encryption
- **Nonce handling**: 12-character random nonces prevent replay attacks
- **Base64 encoding**: Ensures safe transmission of binary data
- **Field validation**: Validates card data before encryption

## Testing

Run the test suite:

```bash
# Run all encryption tests
php ./vendor/bin/pest tests/Unit/EncryptionServiceTest.php tests/Unit/ChargeRequestBuilderEncryptionTest.php

# Run with coverage
php ./vendor/bin/pest tests/Unit/EncryptionServiceTest.php tests/Unit/ChargeRequestBuilderEncryptionTest.php --coverage
```

## References

- [Flutterwave v4 API Documentation](https://developer.flutterwave.com/docs)
- [Flutterwave Encryption Guide](https://developer.flutterwave.com/docs/encryption)
- [PHP OpenSSL Functions](https://www.php.net/manual/en/ref.openssl.php)
