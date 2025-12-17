# Flutterwave - Laravel Wrapper

[![Tests](https://github.com/gowelle/flutterwave-php/actions/workflows/tests.yml/badge.svg)](https://github.com/gowelle/flutterwave-php/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/gowelle/flutterwave-php.svg)](https://packagist.org/packages/gowelle/flutterwave-php)
[![Total Downloads](https://img.shields.io/packagist/dt/gowelle/flutterwave-php.svg)](https://packagist.org/packages/gowelle/flutterwave-php)

A comprehensive Laravel wrapper for Flutterwave Services API v4. This package provides a type-safe, feature-rich integration for Flutterwave payment processing with automatic retry logic, rate limiting, webhook verification, and comprehensive error handling.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Direct Charges](#direct-charges)
  - [Payments](#payments)
  - [Payment Methods](#payment-methods)
  - [Customers](#customers)
  - [Orders](#orders)
  - [Refunds](#refunds)
  - [Transfers/Payouts](#transferspayouts)
  - [Settlements](#settlements)
  - [Banks](#banks)
  - [Mobile Networks](#mobile-networks)
  - [Virtual Accounts](#virtual-accounts)
  - [Wallets](#wallets)
- [Charge Sessions](#charge-sessions)
- [Events & Listeners](#events--listeners)
- [Webhooks](#webhooks)
- [Error Handling](#error-handling)
- [Card Encryption](#card-encryption)
- [Advanced Usage](#advanced-usage)
- [Retry Logic](#retry-logic)
- [Rate Limiting](#rate-limiting)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Static Analysis](#static-analysis)
- [Code Style](#code-style)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)
- [Changelog](#changelog)

## Features

- **Complete Flutterwave v4 API Support** - Full coverage of Flutterwave's v4 API including payments, refunds, transfers, settlements, virtual accounts, wallets, and more
- **Direct Charge Orchestrator** - Simplified payment flow that combines customer, payment method, and charge creation in a single request
- **Payment Methods Management** - Create, list, and manage payment methods for customers
- **Orders API** - Complete order management with create, read, update, and list operations
- **Bank Operations** - Get banks by country, resolve bank accounts, and retrieve bank branches
- **Mobile Networks Support** - List mobile money networks by country for mobile payments
- **Virtual Accounts** - Create and manage virtual bank accounts for receiving payments with multi-currency support
- **Wallets API** - Resolve wallet accounts, retrieve transaction statements with pagination, and query balances for single or multiple currencies
- **Charge Session Tracking** - Database-backed tracking of charge sessions with automatic status updates via webhooks
- **Event System** - Laravel events for direct charge lifecycle and webhook processing
- **Automatic Retry Logic** - Exponential backoff for transient failures (5xx errors, rate limits, timeouts)
- **Rate Limiting** - Configurable per-request rate limiting to prevent API quota exhaustion
- **Webhook Verification** - Secure webhook signature validation with automatic event dispatching
- **Type-Safe DTOs** - Full TypeScript-like typing with PHP 8.2+ for better IDE support and fewer runtime errors
- **Comprehensive Error Handling** - Detailed error messages with categorization (validation, authentication, API errors)
- **Database Migrations** - Built-in migrations for charge session tracking
- **Testing Ready** - Full test suite with Pest framework and HTTP faking support
- **Laravel Integration** - Service provider, facade, and comprehensive configuration system

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or 12.0
- Composer
- Flutterwave account with API credentials

## Installation

Install the package via Composer:

```bash
composer require gowelle/flutterwave-php
```

The package will automatically register its service provider and facade.

## Quick Start

1. **Publish the configuration file:**

```bash
php artisan vendor:publish --tag="flutterwave-config"
```

Or publish all package assets:

```bash
php artisan vendor:publish --tag="flutterwave-config"
php artisan vendor:publish --tag="flutterwave-migrations"
```

2. **Configure your Flutterwave credentials in `.env`:**

```env
FLUTTERWAVE_CLIENT_ID=your_client_id
FLUTTERWAVE_CLIENT_SECRET=your_client_secret
FLUTTERWAVE_SECRET_HASH=your_secret_hash
FLUTTERWAVE_ENCRYPTION_KEY=your_encryption_key
FLUTTERWAVE_ENVIRONMENT=staging  # or production
```

3. **Verify your credentials:**

```bash
php artisan flutterwave:verify
```

4. **Retrieve your encryption key:**

Get your encryption key from your Flutterwave dashboard under **API Settings**. You'll need this to encrypt card data before sending requests.

5. **Run migrations (if using charge sessions):**

```bash
php artisan migrate
```

6. **Start using the package:**

> **Important:** When making card charge requests, card data must be encrypted using AES-256-GCM encryption. See the [Flutterwave Encryption Documentation](https://developer.flutterwave.com/docs/encryption) for encryption requirements and PHP examples.

```php
use Gowelle\Flutterwave\Facades\Flutterwave;

// Create a direct charge
// NOTE: Card data shown below must be encrypted before sending
// See: https://developer.flutterwave.com/docs/encryption
$charge = Flutterwave::directCharge()->create([
    'amount' => 1000,
    'currency' => 'TZS',
    'reference' => 'ORDER-123',
    'customer' => [
        'email' => 'customer@example.com',      // Required
        'name' => [
            'first' => 'John',                   // Required
            'last' => 'Doe',                     // Required
        ],
        'phone_number' => '+255123456789',        // Required
    ],
    'payment_method' => [
        'type' => 'card',
        'card' => [
            'nonce' => 'RANDOMLY_GENERATED_12_CHAR_NONCE',
            'encrypted_card_number' => 'BASE64_ENCRYPTED_CARD_NUMBER',
            'encrypted_cvv' => 'BASE64_ENCRYPTED_CVV',
            'encrypted_expiry_month' => 'BASE64_ENCRYPTED_EXPIRY_MONTH',
            'encrypted_expiry_year' => 'BASE64_ENCRYPTED_EXPIRY_YEAR',
        ],
    ],
    'redirect_url' => 'https://example.com/callback',
]);
```

## Configuration

The package is configured via `config/flutterwave.php`. After publishing, you can customize all settings:

### API Credentials

```php
'client_id' => env('FLUTTERWAVE_CLIENT_ID'),
'client_secret' => env('FLUTTERWAVE_CLIENT_SECRET'),
'secret_hash' => env('FLUTTERWAVE_SECRET_HASH'),
```

Your Flutterwave API credentials can be found in your Flutterwave dashboard under Settings > API.

### Environment

```php
'environment' => env('FLUTTERWAVE_ENVIRONMENT', 'staging'),
```

Set to `'staging'` for testing or `'production'` for live transactions.

### API Settings

```php
'timeout' => env('FLUTTERWAVE_TIMEOUT', 30),           // Request timeout in seconds
'max_retries' => env('FLUTTERWAVE_MAX_RETRIES', 3),    // Maximum retry attempts
'retry_delay' => env('FLUTTERWAVE_RETRY_DELAY', 1000), // Retry delay in milliseconds
```

### Rate Limiting

```php
'rate_limit' => [
    'enabled' => env('FLUTTERWAVE_RATE_LIMIT_ENABLED', true),
    'max_requests' => env('FLUTTERWAVE_RATE_LIMIT_MAX', 100),
    'per_seconds' => env('FLUTTERWAVE_RATE_LIMIT_WINDOW', 60),
],
```

Configure rate limiting to prevent hitting Flutterwave API limits. The default allows 100 requests per 60 seconds.

### Logging

```php
'logging' => [
    'enabled' => env('FLUTTERWAVE_LOGGING_ENABLED', true),
    'channel' => env('FLUTTERWAVE_LOG_CHANNEL', 'stack'),
    'level' => env('FLUTTERWAVE_LOG_LEVEL', 'info'),
    'log_requests' => env('FLUTTERWAVE_LOG_REQUESTS', false),
    'log_responses' => env('FLUTTERWAVE_LOG_RESPONSES', false),
],
```

Control logging behavior. Enable `log_requests` and `log_responses` for debugging API interactions.

### Webhook Settings

```php
'webhook' => [
    'verify_signature' => env('FLUTTERWAVE_WEBHOOK_VERIFY', true),
    'route_path' => env('FLUTTERWAVE_WEBHOOK_PATH', 'webhooks/flutterwave'),
    'route_name' => 'flutterwave.webhook',
    'middleware' => ['api'],
],
```

Configure webhook handling. The package automatically registers a webhook route that verifies signatures and dispatches events.

### Default Currency

```php
'default_currency' => env('FLUTTERWAVE_DEFAULT_CURRENCY', 'TZS'),
```

Set the default currency for transactions if not specified in the request.

### Charge Sessions

```php
'charge_sessions' => [
    'enabled' => true,
    'table_name' => 'flutterwave_charge_sessions',
    'cleanup_after_days' => env('FLUTTERWAVE_SESSION_CLEANUP_DAYS', 30),
    'auto_create' => env('FLUTTERWAVE_SESSION_AUTO_CREATE', false),
    'max_polls' => env('FLUTTERWAVE_SESSION_MAX_POLLS', 60),
],
```

Configure charge session tracking:

- `enabled`: Enable/disable charge session tracking
- `auto_create`: Automatically create sessions when direct charges are created
- `cleanup_after_days`: Days before old sessions are cleaned up
- `max_polls`: Maximum polling attempts for charge status

### Cache Settings

```php
'cache' => [
    'enabled' => env('FLUTTERWAVE_CACHE_ENABLED', true),
    'prefix' => 'flutterwave',
    'ttl' => [
        'access_token' => 3600,      // 1 hour (managed by auth service)
        'banks' => 86400,            // 24 hours
        'mobile_networks' => 86400,  // 24 hours
    ],
],
```

Configure caching for frequently accessed data like access tokens, bank lists, and mobile networks.

### Model Classes

```php
'models' => [
    'user' => env('FLUTTERWAVE_USER_MODEL', 'App\Models\User'),
    'payment' => env('FLUTTERWAVE_PAYMENT_MODEL', 'App\Domain\Payment\Models\Payment'),
],
```

Configure the model classes used by the ChargeSession model for relationships. These should be the fully qualified class names of your application's User and Payment models.

### Environment Variables Reference

| Variable                           | Description                            | Default                             |
| ---------------------------------- | -------------------------------------- | ----------------------------------- |
| `FLUTTERWAVE_CLIENT_ID`            | Your Flutterwave client ID             | -                                   |
| `FLUTTERWAVE_CLIENT_SECRET`        | Your Flutterwave client secret         | -                                   |
| `FLUTTERWAVE_SECRET_HASH`          | Your webhook secret hash               | -                                   |
| `FLUTTERWAVE_ENCRYPTION_KEY`       | Encryption key for card data           | -                                   |
| `FLUTTERWAVE_ENVIRONMENT`          | Environment: `staging` or `production` | `staging`                           |
| `FLUTTERWAVE_DEBUG`                | Enable debug logging (dev only)        | `false`                             |
| `FLUTTERWAVE_TIMEOUT`              | Request timeout in seconds             | `30`                                |
| `FLUTTERWAVE_MAX_RETRIES`          | Maximum retry attempts                 | `3`                                 |
| `FLUTTERWAVE_RETRY_DELAY`          | Retry delay in milliseconds            | `1000`                              |
| `FLUTTERWAVE_RATE_LIMIT_ENABLED`   | Enable rate limiting                   | `true`                              |
| `FLUTTERWAVE_RATE_LIMIT_MAX`       | Max requests per window                | `100`                               |
| `FLUTTERWAVE_RATE_LIMIT_WINDOW`    | Time window in seconds                 | `60`                                |
| `FLUTTERWAVE_LOGGING_ENABLED`      | Enable logging                         | `true`                              |
| `FLUTTERWAVE_LOG_CHANNEL`          | Log channel                            | `stack`                             |
| `FLUTTERWAVE_LOG_LEVEL`            | Log level                              | `info`                              |
| `FLUTTERWAVE_LOG_REQUESTS`         | Log API requests                       | `false`                             |
| `FLUTTERWAVE_LOG_RESPONSES`        | Log API responses                      | `false`                             |
| `FLUTTERWAVE_WEBHOOK_VERIFY`       | Verify webhook signatures              | `true`                              |
| `FLUTTERWAVE_WEBHOOK_PATH`         | Webhook route path                     | `webhooks/flutterwave`              |
| `FLUTTERWAVE_DEFAULT_CURRENCY`     | Default currency code                  | `TZS`                               |
| `FLUTTERWAVE_SESSION_CLEANUP_DAYS` | Days before session cleanup            | `30`                                |
| `FLUTTERWAVE_SESSION_AUTO_CREATE`  | Auto-create charge sessions            | `false`                             |
| `FLUTTERWAVE_SESSION_MAX_POLLS`    | Max polling attempts                   | `60`                                |
| `FLUTTERWAVE_CACHE_ENABLED`        | Enable caching                         | `true`                              |
| `FLUTTERWAVE_USER_MODEL`           | User model class                       | `App\Models\User`                   |
| `FLUTTERWAVE_PAYMENT_MODEL`        | Payment model class                    | `App\Domain\Payment\Models\Payment` |

## Usage

### Direct Charges

The Direct Charge service uses Flutterwave's orchestrator endpoint to simplify the payment flow by combining customer, payment method, and charge creation in a single request.

> **Important:** When making card charge requests, you **must encrypt** the card information before sending the request. Card data (card number, CVV, expiry month, expiry year) must be encrypted using AES-256-GCM encryption. See the [Flutterwave Encryption Documentation](https://developer.flutterwave.com/docs/encryption) for detailed encryption requirements and examples.

#### Creating a Direct Charge

```php
use Gowelle\Flutterwave\Facades\Flutterwave;
use Gowelle\Flutterwave\Exceptions\FlutterwaveException;

try {
    // IMPORTANT: Card data must be encrypted before sending
    // Retrieve your encryption key from Flutterwave dashboard > API Settings
    // Use AES-256-GCM encryption with a 12-character nonce
    // See: https://developer.flutterwave.com/docs/encryption

    $charge = Flutterwave::directCharge()->create([
        'amount' => 10000,        // Amount in smallest currency unit (e.g., cents)
        'currency' => 'TZS',       // Currency code
        'reference' => 'ORDER-123', // Your unique reference
        'customer' => [
            'email' => 'customer@example.com',
            'name' => [
                'first' => 'John',
                'last' => 'Doe',
            ],
            'phone_number' => '+255123456789',
        ],
        'payment_method' => [
            'type' => 'card',
            'card' => [
                'nonce' => 'RANDOMLY_GENERATED_12_CHAR_NONCE',
                'encrypted_card_number' => 'BASE64_ENCRYPTED_CARD_NUMBER',
                'encrypted_cvv' => 'BASE64_ENCRYPTED_CVV',
                'encrypted_expiry_month' => 'BASE64_ENCRYPTED_EXPIRY_MONTH',
                'encrypted_expiry_year' => 'BASE64_ENCRYPTED_EXPIRY_YEAR',
            ],
        ],
        'redirect_url' => 'https://example.com/callback',
        'meta' => [
            'order_id' => '12345',
            'user_id' => '67890',
        ],
    ]);

    // Check charge status
    if ($charge->status->isSuccessful()) {
        // Payment succeeded
    } elseif ($charge->status->requiresAction()) {
        // Handle next action (PIN, OTP, redirect, etc.)
        $nextAction = $charge->nextAction;

        if ($nextAction->type->requiresCustomerInput()) {
            // Show PIN or OTP input form
        } elseif ($nextAction->type->requiresRedirect()) {
            // Redirect to authorization URL
            return redirect($nextAction->data['redirect_url']);
        }
    }
} catch (FlutterwaveException $e) {
    // Handle error
    logger()->error('Charge failed', [
        'error' => $e->getUserFriendlyMessage(),
        'details' => $e->getErrorData(),
    ]);
}
```

**Using DTO (type-safe):**

```php
use Gowelle\Flutterwave\Data\DirectCharge\CreateDirectChargeRequest;
use Gowelle\Flutterwave\Facades\Flutterwave;

$request = CreateDirectChargeRequest::make(
    amount: 10000,
    currency: 'NGN',
    reference: 'ORDER-' . uniqid(),
    customer: [
        'email' => 'customer@example.com',
        'name' => [
            'first' => 'John',
            'last' => 'Doe',
        ],
        'phone_number' => '+2341234567890',
    ],
    paymentMethod: [
        'type' => 'card',
        'card' => [
            'nonce' => 'RANDOMLY_GENERATED_12_CHAR_NONCE',
            'encrypted_card_number' => 'BASE64_ENCRYPTED_CARD_NUMBER',
            'encrypted_cvv' => 'BASE64_ENCRYPTED_CVV',
            'encrypted_expiry_month' => 'BASE64_ENCRYPTED_EXPIRY_MONTH',
            'encrypted_expiry_year' => 'BASE64_ENCRYPTED_EXPIRY_YEAR',
        ],
    ],
    redirectUrl: 'https://example.com/callback',
    meta: ['order_id' => '12345'],
);

$charge = Flutterwave::directCharge()->createFromDto($request);

// Access charge details including new fields
echo $charge->fees;          // Transaction fees
echo $charge->settlementId;  // Settlement ID
$charge->isSettled();        // Check if settled
$charge->isDisputed();       // Check if disputed
```

#### Updating Charge Authorization

When a charge requires additional authorization (PIN, OTP, AVS), submit the authorization data:

```php
use Gowelle\Flutterwave\Data\AuthorizationData;
use Gowelle\Flutterwave\Enums\NextActionType;

// For PIN authorization
$authorization = AuthorizationData::createPin(
    nonce: $nonce,              // Nonce from Flutterwave
    encryptedPin: $encryptedPin // Encrypted PIN
);

// For OTP authorization
$authorization = AuthorizationData::createOtp(
    code: $otpCode // OTP code from customer
);

// For AVS (Address Verification System)
$authorization = AuthorizationData::createAvs([
    'line1' => '123 Main St',
    'city' => 'Dar es Salaam',
    'state' => 'Dar es Salaam',
    'country' => 'TZ',
    'postal_code' => '11101',
]);

// Submit authorization
$updatedCharge = Flutterwave::directCharge()->updateChargeAuthorization(
    chargeId: $charge->id,
    authorizationData: $authorization
);

// Check if charge is now complete
if ($updatedCharge->status->isSuccessful()) {
    // Payment completed successfully
} elseif ($updatedCharge->status->requiresAction()) {
    // May require additional authorization steps
}
```

#### Checking Charge Status

```php
use Gowelle\Flutterwave\Enums\DirectChargeStatus;

$status = Flutterwave::directCharge()->status('charge-id');

if ($status->isSuccessful()) {
    // Payment succeeded
} elseif ($status->isTerminal()) {
    // Payment failed, cancelled, or timed out
} else {
    // Payment is pending or requires action
}
```

### Payments

The Payments service handles the traditional charge flow where you create customers and payment methods separately.

#### Processing a Payment

```php
use Gowelle\Flutterwave\Facades\Flutterwave;

// Process a payment with callback for trace ID
$payment = Flutterwave::payments()->process([
    'amount' => 1000,
    'currency' => 'TZS',
    'reference' => 'ORDER-123',
    'customer_id' => 'CUST-456',
    'payment_method_id' => 'PM-789',
    'payment_method_type' => 'card',
    'redirect_url' => 'https://example.com/callback',
], function ($traceId) {
    // Callback executed when charge is successfully created
    logger()->info('Charge created', ['trace_id' => $traceId]);
});

// Get payment status
$status = Flutterwave::payments()->status('charge-id');
```

### Payment Methods

Manage payment methods for customers.

#### List Payment Methods

```php
$methods = Flutterwave::payments()->methods([
    'customer_id' => 'CUST-456',
    'currency' => 'TZS',
]);
```

#### Create Payment Method

> **Important:** Card data must be encrypted using AES-256-GCM encryption before sending. See the [Flutterwave Encryption Documentation](https://developer.flutterwave.com/docs/encryption) for encryption requirements.

```php
// IMPORTANT: Card data must be encrypted before sending
// Retrieve your encryption key from Flutterwave dashboard > API Settings
// Use AES-256-GCM encryption with a 12-character nonce
// See: https://developer.flutterwave.com/docs/encryption

$paymentMethod = Flutterwave::payments()->createMethod([
    'customer_id' => 'CUST-456',
    'type' => 'card',
    'card' => [
        'nonce' => 'RANDOMLY_GENERATED_12_CHAR_NONCE',
        'encrypted_card_number' => 'BASE64_ENCRYPTED_CARD_NUMBER',
        'encrypted_cvv' => 'BASE64_ENCRYPTED_CVV',
        'encrypted_expiry_month' => 'BASE64_ENCRYPTED_EXPIRY_MONTH',
        'encrypted_expiry_year' => 'BASE64_ENCRYPTED_EXPIRY_YEAR',
    ],
]);
```

#### Get Payment Method

```php
$paymentMethod = Flutterwave::payments()->getMethod('payment-method-id');
```

### Customers

Manage customer records.

#### Create Customer

> **Required Fields:** When creating a customer, the following fields are required: `email`, `name.first`, `name.last`, and `phone_number`. The `name.middle` field is optional.

```php
$customer = Flutterwave::customers()->create([
    'email' => 'john@example.com',        // Required
    'name' => [
        'first' => 'John',                 // Required
        'middle' => 'Michael',             // Optional
        'last' => 'Doe',                   // Required
    ],
    'phone_number' => '+255123456789',      // Required
]);
```

**Using DTO (type-safe):**

```php
use Gowelle\Flutterwave\Data\Customer\CreateCustomerRequest;

$request = new CreateCustomerRequest(
    email: 'john@example.com',
    firstName: 'John',
    lastName: 'Doe',
    phoneNumber: '+255123456789',
    middleName: 'Michael',  // optional
);

$customer = Flutterwave::customers()->createFromDto($request);
```

#### Update Customer

```php
use Gowelle\Flutterwave\Data\Customer\UpdateCustomerRequest;

$request = new UpdateCustomerRequest(
    email: 'john.updated@example.com',
    firstName: 'John',
    lastName: 'Doe',
    phoneNumber: '+255987654321',
);

$customer = Flutterwave::customers()->updateFromDto('customer-id', $request);
```

#### Search Customer

```php
use Gowelle\Flutterwave\Data\Customer\SearchCustomerRequest;

$request = new SearchCustomerRequest(email: 'john@example.com');
$customer = Flutterwave::customers()->searchFromDto($request);
```

#### Get Customer

```php
$customer = Flutterwave::customers()->get('customer-id');
```

#### List Customers

```php
$customers = Flutterwave::customers()->list([
    'page' => 1,
    'limit' => 20,
]);
```

### Orders

Manage orders for tracking purchases and payments. The Order API supports both a simple creation method (using existing customer and payment method IDs) and an orchestrator method (with inline customer and payment details).

#### Create Order (Simple)

Create an order using existing customer and payment method IDs:

```php
use Gowelle\Flutterwave\Facades\Flutterwave;

$order = Flutterwave::orders()->create([
    'amount' => 10000,
    'currency' => 'NGN',
    'reference' => 'ORDER-' . uniqid(),  // 6-42 chars, unique
    'customer_id' => 'cust_abc123',
    'payment_method_id' => 'pm_xyz789',
    'meta' => ['order_type' => 'subscription'],  // optional
    'redirect_url' => 'https://example.com/callback',  // optional
]);
```

**Using DTO (type-safe):**

```php
use Gowelle\Flutterwave\Data\Order\CreateOrderRequest;

$request = CreateOrderRequest::make(
    amount: 10000.00,
    currency: 'NGN',
    reference: 'ORDER-' . uniqid(),
    customerId: 'cust_abc123',
    paymentMethodId: 'pm_xyz789',
    meta: ['source' => 'api'],      // optional
    redirectUrl: 'https://example.com/callback',  // optional
);

$order = Flutterwave::orders()->createFromDto($request);
```

#### Create Order with Orchestrator

Create an order with inline customer and payment method details:

```php
use Gowelle\Flutterwave\Data\Order\CreateOrchestratorOrderRequest;

$request = CreateOrchestratorOrderRequest::make(
    amount: 10000.00,
    currency: 'NGN',
    reference: 'ORDER-' . uniqid(),
    customer: [
        'email' => 'customer@example.com',
        'name' => [
            'first' => 'John',
            'last' => 'Doe',
        ],
        'phone' => '+2341234567890',
    ],
    paymentMethod: [
        'type' => 'card',
        'card' => [
            'nonce' => 'RANDOM_12_CHAR',
            'encrypted_card_number' => 'ENCRYPTED_DATA',
            'encrypted_cvv' => 'ENCRYPTED_DATA',
            'encrypted_expiry_month' => 'ENCRYPTED_DATA',
            'encrypted_expiry_year' => 'ENCRYPTED_DATA',
        ],
    ],
    meta: ['order_type' => 'subscription'],  // optional
    redirectUrl: 'https://example.com/callback',  // optional
);

$order = Flutterwave::orders()->createWithOrchestrator($request);
```

#### Get Order

```php
$order = Flutterwave::orders()->retrieve('order-id');
```

#### List Orders

List orders with optional filtering and pagination:

```php
use Gowelle\Flutterwave\Data\Order\ListOrdersRequest;
use Gowelle\Flutterwave\Data\Order\OrderStatus;

// List all orders (default pagination)
$orders = Flutterwave::orders()->list();

// List with filters
$request = new ListOrdersRequest(
    status: OrderStatus::Completed,      // Filter by status
    from: new DateTime('2024-01-01'),   // Start date
    to: new DateTime('2024-12-31'),     // End date
    customerId: 'cust_abc123',          // Filter by customer
    paymentMethodId: 'pm_xyz789',       // Filter by payment method
    page: 1,                            // Page number (>=1)
    size: 20,                           // Results per page (10-50)
);

$orders = Flutterwave::orders()->listWithFilters($request);
```

**Available Order Statuses:**

```php
use Gowelle\Flutterwave\Data\Order\OrderStatus;

OrderStatus::Completed          // Order completed successfully
OrderStatus::Pending            // Order is pending
OrderStatus::Authorized         // Order is authorized, awaiting capture
OrderStatus::PartiallyCompleted // Partially completed
OrderStatus::Voided             // Order was voided
OrderStatus::Failed             // Order failed
```

#### Update Order

Update order metadata or perform actions (void/capture):

```php
use Gowelle\Flutterwave\Data\Order\UpdateOrderRequest;
use Gowelle\Flutterwave\Data\Order\OrderAction;

// Update with metadata only
$request = UpdateOrderRequest::withMeta(['updated' => true]);
$order = Flutterwave::orders()->updateFromDto('order-id', $request);

// Void an order
$request = UpdateOrderRequest::void(['reason' => 'Customer cancelled']);
$order = Flutterwave::orders()->updateFromDto('order-id', $request);

// Capture an authorized order
$request = UpdateOrderRequest::capture();
$order = Flutterwave::orders()->updateFromDto('order-id', $request);
```

**Convenience methods:**

```php
// Void an order directly
$order = Flutterwave::orders()->void('order-id', ['reason' => 'Cancelled']);

// Capture an authorized order directly
$order = Flutterwave::orders()->capture('order-id');
```



### Refunds

Process refunds for completed charges with type-safe DTOs and filtering.

#### Create Refund

```php
use Gowelle\Flutterwave\Data\Refund\CreateRefundRequest;
use Gowelle\Flutterwave\Enums\RefundReason;

// Create a refund with DTO (type-safe)
$refund = Flutterwave::refunds()->create(
    new CreateRefundRequest(
        amount: 500.00,
        chargeId: 'charge-123',
        reason: RefundReason::REQUESTED_BY_CUSTOMER,
        meta: ['note' => 'Customer requested refund'],  // optional
    )
);

// Check refund status
if ($refund->isSuccessful()) {
    // Refund succeeded
} elseif ($refund->isPending()) {
    // Refund is processing
}
```

#### Get Refund

```php
$refund = Flutterwave::refunds()->get('refund-id');

// Access refund properties with type safety
echo $refund->amountRefunded;  // Float
echo $refund->status->value;   // String via enum
echo $refund->status->isSuccessful();  // Boolean
```

#### List Refunds

List all refunds with optional pagination and date filtering:

```php
use Gowelle\Flutterwave\Data\Refund\ListRefundsRequest;

// List all refunds (default page=1, size=10)
$refunds = Flutterwave::refunds()->list();

// List with custom pagination
$refunds = Flutterwave::refunds()->list(
    new ListRefundsRequest(page: 2, size: 20)
);

// List refunds within date range
$refunds = Flutterwave::refunds()->list(
    new ListRefundsRequest(
        page: 1,
        size: 50,
        from: now()->subDays(30),
        to: now(),
    )
);

// Access refund data
foreach ($refunds as $refund) {
    echo $refund->id;
    echo $refund->chargeId;
    echo $refund->amountRefunded;
    echo $refund->status->value;
    echo $refund->reason;
}
```

#### Refund Reasons

The `RefundReason` enum provides type-safe reason values:

```php
use Gowelle\Flutterwave\Enums\RefundReason;

RefundReason::DUPLICATE                  // Duplicate charge
RefundReason::FRAUDULENT                 // Fraudulent transaction
RefundReason::REQUESTED_BY_CUSTOMER      // Customer requested
RefundReason::EXPIRED_UNCAPTURED_CHARGE  // Expired uncaptured charge
```

#### Refund Status

The `RefundStatus` enum tracks refund state:

```php
use Gowelle\Flutterwave\Enums\RefundStatus;

RefundStatus::NEW       // Refund created, not yet processed
RefundStatus::PENDING   // Refund is being processed
RefundStatus::SUCCEEDED // Refund completed successfully
RefundStatus::FAILED    // Refund failed

// Use helper methods for type-safe checks
$refund->status->isSuccessful();  // true if SUCCEEDED
$refund->status->isPending();     // true if NEW or PENDING
$refund->status->isTerminal();    // true if SUCCEEDED or FAILED
```

### Transfers/Payouts

Send money to bank accounts, mobile money wallets, or Flutterwave wallets.

#### Bank Transfer (Orchestrator)

The recommended approach - creates the recipient inline:

```php
use Gowelle\Flutterwave\Data\Transfer\BankTransferRequest;

$transfer = Flutterwave::transfers()->bankTransfer(
    new BankTransferRequest(
        amount: 50000,
        sourceCurrency: 'NGN',
        destinationCurrency: 'NGN',
        accountNumber: '0123456789',
        bankCode: '044',
        reference: 'PAYOUT-' . uniqid(),
        narration: 'Monthly payout',     // optional
    )
);
```

#### Mobile Money Transfer (Orchestrator)

```php
use Gowelle\Flutterwave\Data\Transfer\MobileMoneyTransferRequest;

$transfer = Flutterwave::transfers()->mobileMoneyTransfer(
    new MobileMoneyTransferRequest(
        amount: 1000,
        sourceCurrency: 'NGN',
        destinationCurrency: 'GHS',
        network: 'MTN',
        phoneNumber: '2339012345678',
        firstName: 'John',
        lastName: 'Doe',
        reference: 'MOMO-' . uniqid(),
    )
);
```

#### Get Transfer

```php
$transfer = Flutterwave::transfers()->get('transfer-id');
```

#### List Transfers

```php
$transfers = Flutterwave::transfers()->list();
```

#### Retry Failed Transfer

```php
$transfer = Flutterwave::transfers()->retry('transfer-id');
```

#### Create Recipient

For the general flow, pre-create recipients. The SDK provides factory methods for all Flutterwave recipient types.

```php
use Gowelle\Flutterwave\Data\Transfer\CreateRecipientRequest;
```

**Simple Bank Recipients** (account number + bank code only):

```php
// Nigerian (NGN) - simplest form
$recipient = Flutterwave::transfers()->createRecipient(
    CreateRecipientRequest::bankNgn(
        accountNumber: '0123456789',
        bankCode: '044',
    )
);
```

**African Bank Recipients** (with name):

```php
// Ethiopian (ETB)
CreateRecipientRequest::bankEtb($accountNumber, $bankCode, $firstName, $lastName);

// Kenyan (KES)
CreateRecipientRequest::bankKes($accountNumber, $bankCode, $firstName, $lastName);

// Malawian (MWK)
CreateRecipientRequest::bankMwk($accountNumber, $bankCode, $firstName, $lastName);

// Rwandan (RWF)
CreateRecipientRequest::bankRwf($accountNumber, $bankCode, $firstName, $lastName);

// Sierra Leonean (SLL)
CreateRecipientRequest::bankSll($accountNumber, $bankCode, $firstName, $lastName);

// Ugandan (UGX)
CreateRecipientRequest::bankUgx($accountNumber, $bankCode, $firstName, $lastName);
```

**African Bank Recipients** (with name + branch):

```php
// Ghanaian (GHS)
CreateRecipientRequest::bankGhs($accountNumber, $bankCode, $branch, $firstName, $lastName);

// Central African (XAF)
CreateRecipientRequest::bankXaf($accountNumber, $bankCode, $branch, $firstName, $lastName);

// West African (XOF)
CreateRecipientRequest::bankXof($accountNumber, $bankCode, $branch, $firstName, $lastName);
```

**International Bank Recipients** (full KYC required):

```php
// US (USD) bank recipient
$recipient = Flutterwave::transfers()->createRecipient(
    CreateRecipientRequest::bankUsd(
        accountNumber: '1234567890',
        bankCode: '021000021',
        accountType: 'checking', // or 'savings'
        routingNumber: '021000021',
        swiftCode: 'CHASUS33',
        firstName: 'John',
        lastName: 'Doe',
        phone: ['country_code' => '1', 'number' => '2025551234'],
        email: 'john@example.com',
        address: [
            'city' => 'New York',
            'country' => 'US',
            'line1' => '123 Main St',
            'postal_code' => '10001',
            'state' => 'NY',
        ],
    )
);

// UK (GBP) bank recipient
CreateRecipientRequest::bankGbp(
    accountNumber: 'GB82WEST12345698765432',
    accountType: 'individual', // or 'corporate'
    bankName: 'HSBC',
    sortCode: '401276',
    firstName: 'John',
    lastName: 'Doe',
    phone: ['country_code' => '44', 'number' => '7911123456'],
    email: 'john@example.com',
    address: ['city' => 'London', 'country' => 'GB', 'line1' => '123 High St', 'postal_code' => 'EC1A 1BB'],
);

// European (EUR) bank recipient
CreateRecipientRequest::bankEur(
    accountNumber: 'DE89370400440532013000',
    bankName: 'Deutsche Bank',
    swiftCode: 'DEUTDEFF',
    firstName: 'Hans',
    lastName: 'Mueller',
    phone: ['country_code' => '49', 'number' => '1701234567'],
    email: 'hans@example.com',
    address: ['city' => 'Berlin', 'country' => 'DE', 'line1' => 'Alexanderplatz 1', 'postal_code' => '10178'],
);

// South African (ZAR) bank recipient
CreateRecipientRequest::bankZar(
    accountNumber: '1234567890',
    bankCode: 'ABSAZAJJ',
    firstName: 'John',
    lastName: 'Doe',
    phone: ['country_code' => '27', 'number' => '823456789'],
    email: 'john@example.com',
    address: ['city' => 'Cape Town', 'country' => 'ZA', 'line1' => '123 Long St', 'postal_code' => '8001'],
);
```

**Mobile Money Recipients** (ETB, GHS, KES, RWF, TZS, UGX, XAF, XOF, ZMW):

```php
$recipient = Flutterwave::transfers()->createRecipient(
    CreateRecipientRequest::mobileMoney(
        currency: 'TZS',
        network: 'VODACOM',
        phoneNumber: '255123456789',
        firstName: 'John',
        lastName: 'Doe',
    )
);
```

**Custom/New Types** (use constructor directly):

```php
// For any type not covered by factory methods
$recipient = Flutterwave::transfers()->createRecipient(
    new CreateRecipientRequest(
        type: 'bank_custom',
        bank: ['account_number' => '...', 'code' => '...'],
        name: ['first' => 'John', 'last' => 'Doe'],
    )
);
```

#### Create Sender

The SDK supports all Flutterwave sender types:

```php
use Gowelle\Flutterwave\Data\Transfer\CreateSenderRequest;
```

**Generic Sender** (for most transfers):

```php
// Basic generic sender
$sender = Flutterwave::transfers()->createSender(
    CreateSenderRequest::generic(
        firstName: 'John',
        lastName: 'Doe',
    )
);

// Generic sender with full details
$sender = Flutterwave::transfers()->createSender(
    CreateSenderRequest::generic(
        firstName: 'John',
        lastName: 'Doe',
        middleName: 'Michael',
        phone: ['country_code' => '234', 'number' => '8012345678'],
        email: 'john@example.com',
        address: [
            'city' => 'Lagos',
            'country' => 'NG',
            'line1' => '123 Main Street',
            'postal_code' => '100001',
            'state' => 'Lagos',
        ],
    )
);
```

**GBP/EUR Bank Sender** (requires full KYC for international transfers):

```php
// GBP sender for UK bank transfers
$sender = Flutterwave::transfers()->createSender(
    CreateSenderRequest::bankGbp(
        firstName: 'John',
        lastName: 'Doe',
        phone: ['country_code' => '44', 'number' => '7911123456'],
        email: 'john@example.com',
        address: [
            'city' => 'London',
            'country' => 'GB',
            'line1' => '123 High Street',
            'postal_code' => 'EC1A 1BB',
            'state' => 'Greater London',
        ],
    )
);

// EUR sender for European bank transfers
$sender = Flutterwave::transfers()->createSender(
    CreateSenderRequest::bankEur(
        firstName: 'Hans',
        lastName: 'Mueller',
        phone: ['country_code' => '49', 'number' => '1701234567'],
        email: 'hans@example.com',
        address: [
            'city' => 'Berlin',
            'country' => 'DE',
            'line1' => 'Alexanderplatz 1',
            'postal_code' => '10178',
            'state' => 'Berlin',
        ],
    )
);
```

#### Get Transfer Rate

```php
use Gowelle\Flutterwave\Data\Transfer\GetRateRequest;

$rate = Flutterwave::transfers()->getRate(
    new GetRateRequest(
        sourceCurrency: 'NGN',
        destinationCurrency: 'GHS',
        amount: 10000,
    )
);
```

#### General Flow Transfer

For the general flow, use pre-created recipient and sender IDs:

```php
use Gowelle\Flutterwave\Data\Transfer\CreateTransferRequest;

// First, create recipient and sender (see above)
$recipient = Flutterwave::transfers()->createRecipient(...);
$sender = Flutterwave::transfers()->createSender(...);

// Then create the transfer
$transfer = Flutterwave::transfers()->create(
    new CreateTransferRequest(
        amount: 50000,
        sourceCurrency: 'NGN',
        destinationCurrency: 'NGN',
        recipientId: $recipient->id,
        senderId: $sender->id,
        reference: 'PAYOUT-' . uniqid(),
    )
);
```

### Settlements

Retrieve settlement information (read-only).

#### Get Settlement

```php
$settlement = Flutterwave::settlements()->get('settlement-id');
```

#### List Settlements

```php
$settlements = Flutterwave::settlements()->list([
    'page' => 1,
    'limit' => 20,
]);
```

### Banks

Get bank information and resolve account details.

#### Get Banks by Country

```php
$banks = Flutterwave::banks()->get('NG'); // Country code (e.g., NG, TZ, KE)
```

#### Get Bank Branches

```php
$branches = Flutterwave::banks()->branches('bank-id');
```

#### Resolve Bank Account

```php
$account = Flutterwave::banks()->resolveAccount(
    bankCode: '044',
    accountNumber: '0123456789',
    currency: 'NGN'
);

// Access resolved account details
echo $account->accountName;
echo $account->accountNumber;
```

**Using DTO (type-safe):**

```php
use Gowelle\Flutterwave\Data\Banks\BankAccountResolveRequest;

$request = new BankAccountResolveRequest(
    bankCode: '044',
    accountNumber: '0123456789',
    currency: 'NGN',  // defaults to NGN if omitted
);

$account = Flutterwave::banks()->resolveFromDto($request);
```

### Mobile Networks

Get mobile money networks by country.

#### List Mobile Networks

```php
$networks = Flutterwave::mobileNetworks()->list('TZ'); // Country code

foreach ($networks as $network) {
    echo $network->name;
    echo $network->code;
}
```

### Wallets

Manage Flutterwave wallet operations including account lookup, statement retrieval, and balance queries.

#### Resolve Wallet Account

Verify wallet account information for a customer:

```php
use Gowelle\Flutterwave\Facades\Flutterwave;

$account = Flutterwave::wallets()->resolveAccount(
    provider: 'flutterwave',
    identifier: 'wallet_123'
);

// Access resolved account details
echo $account->provider;      // 'flutterwave'
echo $account->identifier;    // 'wallet_123'
echo $account->name;          // Account holder name
```

#### Get Wallet Statement

Retrieve wallet transaction statement with pagination and filtering:

```php
use Gowelle\Flutterwave\Facades\Flutterwave;

$statement = Flutterwave::wallets()->getStatement([
    'currency' => 'NGN',           // Required: 3-letter currency code
    'size' => 20,                  // Optional: Page size (10-50, default 10)
    'from' => '2024-01-01T00:00:00Z', // Optional: Start date (ISO 8601)
    'to' => '2024-12-31T23:59:59Z',   // Optional: End date (ISO 8601)
    'next' => 'next_cursor',       // Optional: Next page cursor
    'previous' => 'prev_cursor',   // Optional: Previous page cursor
]);

// Access statement data
echo $statement->cursor->total;        // Total transactions
echo $statement->cursor->limit;       // Page limit
echo $statement->cursor->hasMoreItems; // Whether more items exist
echo $statement->cursor->next;         // Next page cursor
echo $statement->cursor->previous;      // Previous page cursor

// Access transactions
foreach ($statement->transactions as $transaction) {
    echo $transaction['transaction_direction']; // 'credit' or 'debit'
    echo $transaction['amount']['value'];
    echo $transaction['amount']['currency'];
    echo $transaction['balance']['before'];
    echo $transaction['balance']['after'];
}
```

#### Get Wallet Balance (Single Currency)

Fetch the available balance for a specific currency:

```php
use Gowelle\Flutterwave\Facades\Flutterwave;

$balance = Flutterwave::wallets()->getBalance('NGN');

echo $balance->currency;           // 'NGN'
echo $balance->availableBalance;   // 1200.09
```

#### Get Wallet Balances (Multiple Currencies)

Fetch available balances for all currencies:

```php
use Gowelle\Flutterwave\Facades\Flutterwave;

$balances = Flutterwave::wallets()->getBalances();

foreach ($balances as $balance) {
    echo $balance->currency;           // 'NGN', 'USD', etc.
    echo $balance->availableBalance;   // Available balance
}
```

### Virtual Accounts

Create and manage virtual bank accounts for receiving payments. Virtual account operations are now integrated into the Banks service for a unified banking experience.

#### Create Virtual Account

Create a virtual account for a specific customer using type-safe DTOs:

```php
use Gowelle\Flutterwave\Facades\Flutterwave;
use Gowelle\Flutterwave\Data\VirtualAccount\CreateVirtualAccountRequestDTO;
use Gowelle\Flutterwave\Enums\VirtualAccountCurrency;
use Gowelle\Flutterwave\Enums\VirtualAccountType;

$request = new CreateVirtualAccountRequestDTO(
    reference: 'unique-ref-' . time(),      // 6-42 chars, unique
    customerId: 'cus_123',                  // Existing customer ID
    amount: 0,                              // 0 for static accounts
    currency: VirtualAccountCurrency::NGN,  // NGN, GHS, EGP, or KES
    accountType: VirtualAccountType::STATIC, // STATIC or DYNAMIC
    narration: 'Payment for Order #123',    // Optional
    meta: ['order_id' => '123'],            // Optional metadata
);

$account = Flutterwave::banks()->createVirtualAccount($request);

// Access account details
echo $account->accountNumber;      // Virtual account number
echo $account->accountBankName;    // Bank name (e.g., "WEMA BANK")
echo $account->reference;          // Your reference
echo $account->status->value;      // 'active' or 'inactive'
```

#### Retrieve Virtual Account

Get details of a specific virtual account:

```php
$account = Flutterwave::banks()->retrieveVirtualAccount('va_123');

echo $account->accountNumber;
echo $account->accountBankName;
echo $account->status->value;
echo $account->currency->value;
```

#### List Virtual Accounts

List all virtual accounts:

```php
$accounts = Flutterwave::banks()->listVirtualAccounts();

foreach ($accounts as $account) {
    echo $account->accountNumber;
    echo $account->status->value;
    echo $account->currency->value;
}
```

#### List Virtual Accounts with Filters

List virtual accounts with pagination and filtering using DTOs:

```php
use Gowelle\Flutterwave\Data\VirtualAccount\ListVirtualAccountsParamsDTO;

$params = new ListVirtualAccountsParamsDTO(
    page: 1,                                // Page number (min: 1)
    size: 20,                               // Page size (10-50)
    from: '2024-01-01T00:00:00Z',          // Start date (ISO 8601)
    to: '2024-12-31T23:59:59Z',            // End date (ISO 8601)
    reference: 'unique-ref-123',            // Filter by reference
);

$accounts = Flutterwave::banks()->listVirtualAccountsWithParams($params);

foreach ($accounts as $account) {
    echo $account->accountNumber;
    echo $account->reference;
}
```

#### Update Virtual Account

Update account status or BVN using DTOs:

```php
use Gowelle\Flutterwave\Data\VirtualAccount\UpdateVirtualAccountRequestDTO;
use Gowelle\Flutterwave\Enums\VirtualAccountStatus;

// Deactivate an account
$request = UpdateVirtualAccountRequestDTO::forStatusUpdate(
    VirtualAccountStatus::INACTIVE
);

$updated = Flutterwave::banks()->updateVirtualAccount('va_123', $request);

// Update BVN
$request = UpdateVirtualAccountRequestDTO::forBvnUpdate(
    bvn: '12345678901',
    meta: ['updated_by' => 'admin']  // Optional
);

$updated = Flutterwave::banks()->updateVirtualAccount('va_123', $request);
```

#### Supported Currencies

Virtual accounts support the following currencies:

- **NGN** - Nigerian Naira
- **GHS** - Ghanaian Cedi
- **EGP** - Egyptian Pound (requires `customer_account_number`)
- **KES** - Kenyan Shilling (requires `customer_account_number`)

#### Account Types

- **STATIC** - Permanent account that can be reused (amount should be 0)
- **DYNAMIC** - Temporary account for specific transaction (expires after configured duration)

#### Example: Complete Virtual Account Flow

```php
use Gowelle\Flutterwave\Facades\Flutterwave;
use Gowelle\Flutterwave\Data\VirtualAccount\CreateVirtualAccountRequestDTO;
use Gowelle\Flutterwave\Enums\VirtualAccountCurrency;
use Gowelle\Flutterwave\Enums\VirtualAccountType;

// Create a static account for a customer
$request = new CreateVirtualAccountRequestDTO(
    reference: 'order-' . $order->id,
    customerId: 'cus_' . $customer->id,
    amount: 0,
    currency: VirtualAccountCurrency::NGN,
    accountType: VirtualAccountType::STATIC,
    narration: "Payment for Order #{$order->id}",
    meta: [
        'order_id' => $order->id,
        'customer_id' => $customer->id,
    ],
);

$account = Flutterwave::banks()->createVirtualAccount($request);

// Store the account number for the customer to pay into
$bankAccount = $account->accountNumber;
$bankName = $account->accountBankName;

// Later, retrieve to check status
$current = Flutterwave::banks()->retrieveVirtualAccount($account->id);
if ($current->isActive()) {
    // Account is still active
}

// When no longer needed, deactivate
$api->update($account['data']['id'], [
    'action_type' => 'update_status',
    'status' => 'inactive',
]);
```

## Charge Sessions

Charge Sessions provide database-backed tracking of direct charge transactions. This feature is particularly useful for tracking charges that require multiple authorization steps (PIN, OTP, redirects).

### Features

- Automatic status updates via webhooks
- Event-driven session creation and updates
- Relationship tracking with User and Payment models
- Metadata storage for custom data
- Automatic cleanup of old sessions

### Enabling Charge Sessions

1. **Publish and run the migration:**

```bash
php artisan vendor:publish --tag="flutterwave-migrations"
php artisan migrate
```

2. **Configure in `config/flutterwave.php`:**

```php
'charge_sessions' => [
    'enabled' => true,
    'auto_create' => true, // Automatically create sessions on charge creation
],
```

### Using Charge Sessions

#### Automatic Creation

When `auto_create` is enabled, sessions are automatically created when direct charges are created:

```php
use Gowelle\Flutterwave\Facades\Flutterwave;
use Gowelle\Flutterwave\Models\ChargeSession;

$charge = Flutterwave::directCharge()->create([
    'amount' => 1000,
    'currency' => 'TZS',
    'reference' => 'ORDER-123',
    'customer' => [...],
    'payment_method' => [...],
    'user_id' => auth()->id(),        // Required for auto-create
    'payment_id' => $payment->id,     // Required for auto-create
]);

// Session is automatically created and linked
$session = ChargeSession::byRemoteChargeId($charge->id)->first();
```

#### Manual Creation

You can also create sessions manually:

```php
use Gowelle\Flutterwave\Models\ChargeSession;

$session = ChargeSession::create([
    'user_id' => auth()->id(),
    'payment_id' => $payment->id,
    'remote_charge_id' => $charge->id,
    'status' => $charge->status->value,
    'next_action_type' => $charge->nextAction->type->value ?? null,
    'next_action_data' => $charge->nextAction->data ?? null,
    'payment_method_type' => 'card',
    'meta' => [
        'order_id' => '12345',
    ],
]);
```

#### Querying Sessions

```php
use Gowelle\Flutterwave\Models\ChargeSession;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;

// Find by remote charge ID
$session = ChargeSession::byRemoteChargeId('charge-id')->first();

// Find pending sessions
$pendingSessions = ChargeSession::pending()->get();

// Find completed sessions
$completedSessions = ChargeSession::completed()->get();

// Find by status
$succeededSessions = ChargeSession::withStatus(DirectChargeStatus::SUCCEEDED)->get();

// Access relationships
$user = $session->user;
$payment = $session->payment;
```

#### Updating Sessions

Sessions are automatically updated via webhooks when `enabled` is true. You can also update them manually:

```php
$session->updateStatus(DirectChargeStatus::SUCCEEDED);
$session->updateNextAction($nextActionData);
$session->setMeta('custom_key', 'custom_value');
$session->save();
```

#### Session Cleanup

Run the cleanup command to remove old sessions:

```bash
php artisan flutterwave:cleanup-sessions
```

Or schedule it in your `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('flutterwave:cleanup-sessions')->daily();
}
```

## Events & Listeners

The package dispatches Laravel events for important actions, allowing you to hook into the payment flow.

### Available Events

#### FlutterwaveChargeCreated

Dispatched when a direct charge is created:

```php
use Gowelle\Flutterwave\Events\FlutterwaveChargeCreated;

Event::listen(FlutterwaveChargeCreated::class, function (FlutterwaveChargeCreated $event) {
    $chargeData = $event->chargeData;
    $requestData = $event->requestData;

    // Create charge session, send notification, etc.
});
```

#### FlutterwaveChargeUpdated

Dispatched when charge authorization is submitted:

```php
use Gowelle\Flutterwave\Events\FlutterwaveChargeUpdated;

Event::listen(FlutterwaveChargeUpdated::class, function (FlutterwaveChargeUpdated $event) {
    $chargeData = $event->chargeData;
    $authorizationData = $event->authorizationData;

    // Update charge session, process completion, etc.
});
```

#### FlutterwaveTransferCreated

Dispatched when a transfer is created (bank, mobile money, or wallet):

```php
use Gowelle\Flutterwave\Events\FlutterwaveTransferCreated;

Event::listen(FlutterwaveTransferCreated::class, function (FlutterwaveTransferCreated $event) {
    $transferData = $event->transferData;

    // Log transfer, update records, send notification, etc.
    logger()->info('Transfer created', [
        'id' => $transferData->id,
        'status' => $transferData->status->value,
        'amount' => $transferData->amount,
    ]);
});
```

#### FlutterwaveWebhookReceived

Dispatched when a webhook is received and verified:

```php
use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;

Event::listen(FlutterwaveWebhookReceived::class, function (FlutterwaveWebhookReceived $event) {
    $eventType = $event->getEventType(); // String (backward compatible)
    $eventTypeEnum = $event->getEventTypeEnum(); // WebhookEventType enum (recommended)
    $transactionData = $event->getTransactionData();

    // Using helper methods on the event
    if ($event->isPaymentEvent()) {
        // Handle payment-related webhook
    } elseif ($event->isTransferEvent()) {
        // Handle transfer-related webhook
    }

    // Or using the enum directly
    if ($eventTypeEnum?->isPaymentEvent()) {
        // Handle payment-related webhook
    } elseif ($eventTypeEnum?->isTransferEvent()) {
        // Handle transfer-related webhook
    }

    if ($event->isSuccessful()) {
        // Transaction was successful
    }
});
```

### Built-in Listeners

The package includes built-in listeners that are automatically registered:

- **CreateChargeSession** - Creates charge sessions when `auto_create` is enabled
- **UpdateChargeSession** - Updates charge sessions when authorization is submitted
- **UpdateChargeSessionFromWebhook** - Updates charge sessions from webhook events

You can disable these by setting the appropriate configuration options.

### Custom Event Listeners

Create your own event listeners:

```php
// app/Listeners/ProcessSuccessfulPayment.php
namespace App\Listeners;

use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;

class ProcessSuccessfulPayment
{
    public function handle(FlutterwaveWebhookReceived $event): void
    {
        if (!$event->isPaymentEvent() || !$event->isSuccessful()) {
            return;
        }

        $transactionData = $event->getTransactionData();
        $chargeId = $transactionData['id'] ?? null;

        // Update your payment record, send confirmation email, etc.
    }
}
```

Register in `app/Providers/EventServiceProvider.php`:

```php
use App\Listeners\ProcessSuccessfulPayment;
use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;

protected $listen = [
    FlutterwaveWebhookReceived::class => [
        ProcessSuccessfulPayment::class,
    ],
];
```

## Webhooks

The package includes automatic webhook handling with signature verification and event dispatching.

### Using the Built-in Webhook Route

The package automatically registers a webhook route at `/webhooks/flutterwave` (configurable via `FLUTTERWAVE_WEBHOOK_PATH`). This route:

1. Verifies the webhook signature
2. Dispatches the `FlutterwaveWebhookReceived` event
3. Returns a 200 response

Configure the webhook URL in your Flutterwave dashboard to point to:

```
https://yourdomain.com/webhooks/flutterwave
```

### Listening to Webhook Events

You can listen to webhook events and process them based on the event type. The package provides both string-based and enum-based methods for type safety.

#### Using String Event Types (Backward Compatible)

```php
use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;
use Illuminate\Support\Facades\Event;

Event::listen(FlutterwaveWebhookReceived::class, function (FlutterwaveWebhookReceived $event) {
    $payload = $event->payload;
    $eventType = $event->getEventType(); // Returns string
    $data = $event->getTransactionData();

    // Process webhook event based on type
    match ($eventType) {
        'charge.completed' => $this->handleChargeCompleted($data),
        'charge.failed' => $this->handleChargeFailed($data),
        'transfer.completed' => $this->handleTransferCompleted($data),
        default => logger()->info('Unhandled webhook event', ['type' => $eventType]),
    };
});
```

#### Using WebhookEventType Enum (Recommended)

For better type safety, use the `WebhookEventType` enum:

```php
use Gowelle\Flutterwave\Enums\WebhookEventType;
use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;
use Illuminate\Support\Facades\Event;

Event::listen(FlutterwaveWebhookReceived::class, function (FlutterwaveWebhookReceived $event) {
    $eventTypeEnum = $event->getEventTypeEnum(); // Returns WebhookEventType enum
    $data = $event->getTransactionData();

    if ($eventTypeEnum === null) {
        logger()->warning('Unknown webhook event type', ['payload' => $event->payload]);
        return;
    }

    // Use enum helper methods
    if ($eventTypeEnum->isPaymentEvent()) {
        // Handle payment-related webhook
        if ($eventTypeEnum->isSuccessful()) {
            $this->handleSuccessfulPayment($data);
        } else {
            $this->handleFailedPayment($data);
        }
    } elseif ($eventTypeEnum->isTransferEvent()) {
        // Handle transfer-related webhook
        $this->handleTransfer($data);
    }

    // Or use match with enum cases
    match ($eventTypeEnum) {
        WebhookEventType::CHARGE_COMPLETED => $this->handleChargeCompleted($data),
        WebhookEventType::CHARGE_FAILED => $this->handleChargeFailed($data),
        WebhookEventType::CHARGE_SUCCESSFUL => $this->handleChargeSuccessful($data),
        WebhookEventType::PAYMENT_COMPLETED => $this->handlePaymentCompleted($data),
        WebhookEventType::PAYMENT_FAILED => $this->handlePaymentFailed($data),
        WebhookEventType::PAYMENT_SUCCESSFUL => $this->handlePaymentSuccessful($data),
        WebhookEventType::TRANSFER_COMPLETED => $this->handleTransferCompleted($data),
    };
});
```

#### WebhookEventType Enum

The `WebhookEventType` enum provides type-safe webhook event handling with helper methods:

**Available Event Types:**

- `CHARGE_COMPLETED` - Charge completed event
- `CHARGE_FAILED` - Charge failed event
- `CHARGE_SUCCESSFUL` - Charge successful event
- `PAYMENT_COMPLETED` - Payment completed event
- `PAYMENT_FAILED` - Payment failed event
- `PAYMENT_SUCCESSFUL` - Payment successful event
- `TRANSFER_COMPLETED` - Transfer completed event

**Helper Methods:**

- `fromString(?string $event): ?self` - Convert string to enum (returns null for unknown types)
- `isPaymentEvent(): bool` - Check if event is payment-related (charge._ or payment._)
- `isTransferEvent(): bool` - Check if event is transfer-related (transfer.\*)
- `isChargeEvent(): bool` - Check if event is charge-related (charge.\*)
- `isSuccessful(): bool` - Check if event indicates success

**Example Usage:**

```php
use Gowelle\Flutterwave\Enums\WebhookEventType;

// Convert string to enum
$enum = WebhookEventType::fromString('charge.completed');
if ($enum !== null) {
    // Type-safe event handling
    if ($enum->isPaymentEvent()) {
        // Handle payment event
    }
}

// Check event type
if ($enum?->isSuccessful()) {
    // Event indicates success
}
```

### Manual Webhook Verification

If you need to verify webhooks manually (e.g., in a custom route):

```php
use Gowelle\Flutterwave\Facades\Flutterwave;
use Gowelle\Flutterwave\Exceptions\WebhookVerificationException;
use Illuminate\Http\Request;

Route::post('/custom-webhook', function (Request $request) {
    try {
        // Verify webhook signature
        Flutterwave::webhook()->verifyRequest($request);

        // Get event details (string)
        $eventType = Flutterwave::webhook()->getEventType($request);

        // Get event details (enum - recommended)
        $eventTypeEnum = Flutterwave::webhook()->getEventTypeEnum($request);
        $data = Flutterwave::webhook()->getEventData($request);

        // Process webhook
        // ...

        return response()->json(['status' => 'success']);
    } catch (WebhookVerificationException $e) {
        // Invalid webhook signature
        return response()->json(['error' => 'Invalid signature'], 401);
    }
});
```

**Note:** Flutterwave sends the signature in the `flutterwave-signature` header, which is automatically handled by the `verifyRequest` method.

## Error Handling

All API calls throw `FlutterwaveException` on error. The exception provides detailed information about the error:

```php
use Gowelle\Flutterwave\Exceptions\FlutterwaveException;
use Gowelle\Flutterwave\Facades\Flutterwave;

try {
    $payment = Flutterwave::payments()->process($data);
} catch (FlutterwaveException $e) {
    // Get user-friendly message
    $userMessage = $e->getUserFriendlyMessage();

    // Check error type
    if ($e->isValidationError()) {
        // Handle validation error (400)
        logger()->warning('Validation error', ['message' => $userMessage]);
    } elseif ($e->isAuthenticationError()) {
        // Handle authentication error (401)
        logger()->error('Authentication failed', ['message' => $userMessage]);
    } elseif ($e->isRateLimitError()) {
        // Handle rate limit error (429)
        logger()->warning('Rate limit exceeded', ['message' => $userMessage]);
    } else {
        // Handle other API errors
        logger()->error('API error', ['message' => $userMessage]);
    }

    // Get technical details
    $errorData = $e->getErrorData();
    logger()->error('Error details', [
        'message' => $errorData->getMessage(),
        'code' => $errorData->getCode(),
        'type' => $errorData->getType(),
    ]);
}
```

### Error Types

- **ValidationError** - Invalid request data (400)
- **AuthenticationError** - Invalid credentials (401)
- **RateLimitError** - Too many requests (429)
- **ApiError** - Other API errors (500, 502, 503, etc.)

## Card Encryption

**Critical:** When making card charge requests, you **must encrypt** all card data using AES-256-GCM encryption before sending to Flutterwave.

### Encryption Implementation Guide

For a complete encryption implementation guide with examples, security best practices, and integration patterns, see [ENCRYPTION_IMPLEMENTATION.md](ENCRYPTION_IMPLEMENTATION.md).

### Quick Start - Using ChargeRequestBuilder

The simplest way to handle card encryption is using the `ChargeRequestBuilder`. It returns a type-safe `DirectChargeRequestDTO`:

```php
use Gowelle\Flutterwave\Builders\ChargeRequestBuilder;

$dto = ChargeRequestBuilder::for('ORDER-123')
    ->amount(150, 'NGN')
    ->customer('customer@example.com', 'John', 'Doe')
    ->card(
        cardNumber: '5531886652142950',
        expiryMonth: '09',
        expiryYear: '32',
        cvv: '564'
    )
    ->redirectUrl('https://example.com/callback')
    ->build();

// Convert to array for API request
$request = $dto->toArray();

// All card data is automatically encrypted and removed from plaintext
```

### Using ChargeRequestBuilder with Different Payment Methods

The builder supports multiple payment methods:

**Card Payments:**

```php
$dto = ChargeRequestBuilder::for('ORDER-123')
    ->amount(150, 'NGN')
    ->customer('customer@example.com', 'John', 'Doe')
    ->card('5531886652142950', '09', '32', '564')
    ->redirectUrl('https://example.com/callback')
    ->build();
```

**Mobile Money:**

```php
$dto = ChargeRequestBuilder::for('ORDER-123')
    ->amount(1000, 'TZS')
    ->customer('user@example.com', 'Jane', 'Doe', '+255712345678')
    ->mobileMoney('VODACOM', '255712345678')
    ->redirectUrl('https://example.com/callback')
    ->build();
```

**Bank Account:**

```php
$dto = ChargeRequestBuilder::for('ORDER-123')
    ->amount(50000, 'NGN')
    ->customer('user@example.com', 'Bank', 'User')
    ->bankAccount('0123456789', '044')
    ->redirectUrl('https://example.com/callback')
    ->build();
```

**With Additional Options:**

```php
$dto = ChargeRequestBuilder::for('ORDER-123')
    ->amount(150, 'NGN')
    ->customer('customer@example.com', 'John', 'Doe', '+234812345678')
    ->card('5531886652142950', '09', '32', '564')
    ->redirectUrl('https://example.com/callback')
    ->meta(['order_id' => '12345'])
    ->customizations('My Store', 'Complete your purchase')
    ->idempotencyKey('unique-key-' . time())
    ->traceId('trace-' . uniqid())
    ->userId(auth()->id())
    ->paymentId($payment->id)
    ->build();
```

### Manual Encryption

For more control, use the `EncryptionService` directly:

```php
use Gowelle\Flutterwave\Support\EncryptionService;

$encryptionService = new EncryptionService(config('flutterwave.encryption_key'));

$encryptedCard = $encryptionService->encryptCardData([
    'card_number' => '5531886652142950',
    'expiry_month' => '09',
    'expiry_year' => '32',
    'cvv' => '564',
]);

// Returns: [
//     'nonce' => 'random_12_char_nonce',
//     'encrypted_card_number' => 'base64_encoded_ciphertext',
//     'encrypted_expiry_month' => 'base64_encoded_ciphertext',
//     'encrypted_expiry_year' => 'base64_encoded_ciphertext',
//     'encrypted_cvv' => 'base64_encoded_ciphertext',
// ]
```

### Type-Safe DTOs

The `ChargeRequestBuilder` returns a `DirectChargeRequestDTO` for type safety:

```php
use Gowelle\Flutterwave\Builders\ChargeRequestBuilder;

$dto = ChargeRequestBuilder::for('ORDER-123')
    ->amount(150, 'NGN')
    ->customer('customer@example.com', 'John', 'Doe')
    ->card('5531886652142950', '09', '32', '564')
    ->redirectUrl('https://example.com/callback')
    ->build();

// Type-safe DTO with validation
$request = $dto->toArray(); // Get array for API
```

Available DTOs:

- **DirectChargeRequestDTO** - Direct charge orchestrator requests
- **ChargeRequestDTO** - Traditional charge flow requests

### Key Features

- **AES-256-GCM Encryption** - Industry-standard authenticated encryption
- **Automatic Nonce Generation** - Cryptographically secure 12-character nonces
- **Input Validation** - Validates card data before encryption
- **Base64 Encoding** - Safe transmission of binary data
- **Zero Plaintext** - Card data never exposed in request payloads

> For detailed documentation including security best practices, error handling, and advanced usage, see [ENCRYPTION_IMPLEMENTATION.md](ENCRYPTION_IMPLEMENTATION.md).

## Advanced Usage

### Card Data Encryption (Advanced)

**Critical:** When making card charge requests, you **must encrypt** all card data (card number, CVV, expiry month, expiry year) using AES-256-GCM encryption before sending the request to Flutterwave.

#### Encryption Requirements

1. **Retrieve your encryption key** from your Flutterwave dashboard under **API Settings**
2. **Use AES-256-GCM encryption** with a 12-character nonce
3. **Base64 encode** the encrypted data
4. **Include the nonce** in your request

#### Encryption Process

1. Generate a cryptographically secure random 12-character nonce (alphanumeric)
2. Encrypt each card field (card number, CVV, expiry month, expiry year) using:
   - Algorithm: AES-256-GCM
   - Key: Your encryption key from Flutterwave dashboard (base64 decoded)
   - IV/Nonce: The 12-character nonce
3. Base64 encode the encrypted result
4. Include both the nonce and encrypted fields in your request

#### Example Request Structure

```php
'payment_method' => [
    'type' => 'card',
    'card' => [
        'nonce' => 'RANDOMLY_GENERATED_12_CHAR_NONCE',
        'encrypted_card_number' => 'BASE64_ENCRYPTED_CARD_NUMBER',
        'encrypted_cvv' => 'BASE64_ENCRYPTED_CVV',
        'encrypted_expiry_month' => 'BASE64_ENCRYPTED_EXPIRY_MONTH',
        'encrypted_expiry_year' => 'BASE64_ENCRYPTED_EXPIRY_YEAR',
    ],
]
```

#### PHP Encryption Example

For PHP, you can use OpenSSL or libsodium. Here's a basic example using OpenSSL:

```php
/**
 * Generate a cryptographically secure 12-character alphanumeric nonce
 */
function generateSecureNonce(int $length = 12): string
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $charactersLength = strlen($characters);
    $nonce = '';

    // Generate cryptographically secure random bytes
    $randomBytes = random_bytes($length);

    // Map bytes to alphanumeric characters
    for ($i = 0; $i < $length; $i++) {
        $nonce .= $characters[ord($randomBytes[$i]) % $charactersLength];
    }

    return $nonce;
}

function encryptCardData(string $plainText, string $encryptionKey, string $nonce): string
{
    $key = base64_decode($encryptionKey);
    $iv = $nonce; // 12-character nonce

    // Encrypt using AES-256-GCM
    $encrypted = openssl_encrypt(
        $plainText,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    // Combine encrypted data with authentication tag
    $encryptedWithTag = $encrypted . $tag;

    // Base64 encode
    return base64_encode($encryptedWithTag);
}

// Usage
$encryptionKey = 'your_base64_encoded_encryption_key_from_dashboard';
$nonce = generateSecureNonce(12); // Generate cryptographically secure 12-character nonce

$encryptedCardNumber = encryptCardData('5531886652142950', $encryptionKey, $nonce);
$encryptedCvv = encryptCardData('564', $encryptionKey, $nonce);
$encryptedExpiryMonth = encryptCardData('09', $encryptionKey, $nonce);
$encryptedExpiryYear = encryptCardData('32', $encryptionKey, $nonce);
```

> **Reference:** For detailed encryption documentation, examples, and best practices, see the [Flutterwave Encryption Documentation](https://developer.flutterwave.com/docs/encryption).

#### Error Handling

If you send unencrypted or improperly encrypted card details, Flutterwave will return a `422` error:

```json
{
  "status": "failed",
  "error": {
    "type": "CLIENT_ENCRYPTION_ERROR",
    "code": "11100",
    "message": "Unable to decrypt encrypted fields provided",
    "validation_errors": []
  }
}
```

### Idempotency Keys

Use idempotency keys to safely retry requests:

```php
$charge = Flutterwave::directCharge()->create([
    'amount' => 1000,
    'currency' => 'TZS',
    'reference' => 'ORDER-123',
    'idempotency_key' => 'unique-key-' . time(),
    // ... other data
]);
```

### Trace IDs

Trace IDs help track requests across systems:

```php
$charge = Flutterwave::directCharge()->create([
    'amount' => 1000,
    'currency' => 'TZS',
    'reference' => 'ORDER-123',
    'trace_id' => 'trace-' . uniqid(),
    // ... other data
]);
```

### Custom Retry Strategies

The package automatically retries on transient failures. You can customize retry behavior:

```env
FLUTTERWAVE_MAX_RETRIES=5
FLUTTERWAVE_RETRY_DELAY=2000
```

### Rate Limiting Customization

Adjust rate limiting based on your needs:

```env
FLUTTERWAVE_RATE_LIMIT_ENABLED=true
FLUTTERWAVE_RATE_LIMIT_MAX=200
FLUTTERWAVE_RATE_LIMIT_WINDOW=60
```

### Direct Service Access

Access services directly without the facade:

```php
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;

$service = app(FlutterwaveDirectChargeService::class);
$charge = $service->create($data);
```

### Dependency Injection

Inject services into your classes:

```php
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;

class PaymentController
{
    public function __construct(
        private FlutterwaveDirectChargeService $chargeService
    ) {}

    public function process()
    {
        $charge = $this->chargeService->create([...]);
    }
}
```

## Retry Logic

The package automatically retries failed requests with exponential backoff for:

- 5xx server errors
- 429 rate limit errors
- 408 timeout errors
- 503 service unavailable

### Configuration

Configure retry behavior in `.env`:

```env
FLUTTERWAVE_MAX_RETRIES=3
FLUTTERWAVE_RETRY_DELAY=1000  # milliseconds
```

The retry delay doubles with each attempt (exponential backoff).

## Rate Limiting

Rate limiting prevents hitting Flutterwave API quotas. It's enabled by default and limits requests per time window.

### Configuration

```env
FLUTTERWAVE_RATE_LIMIT_ENABLED=true
FLUTTERWAVE_RATE_LIMIT_MAX_REQUESTS=100
FLUTTERWAVE_RATE_LIMIT_PER_SECONDS=60
```

When the limit is reached, requests will wait until the window resets or throw a `RateLimitException`.

## Testing

The package is fully testable using Laravel's HTTP faking capabilities.

### Running Tests

```bash
vendor/bin/pest
```

### Example Test

```php
use Gowelle\Flutterwave\Facades\Flutterwave;
use Illuminate\Support\Facades\Http;

it('creates a direct charge successfully', function () {
    Http::fake([
        'api.flutterwave.com/*' => Http::response([
            'status' => 'success',
            'data' => [
                'id' => 'charge-123',
                'status' => 'pending',
                'amount' => 1000,
                'currency' => 'TZS',
            ],
        ], 200),
    ]);

    $charge = Flutterwave::directCharge()->create([
        'amount' => 1000,
        'currency' => 'TZS',
        'reference' => 'ORDER-123',
        'customer' => [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ],
        'payment_method' => [
            'type' => 'card',
            'card' => [
                'number' => '5531886652142950',
                'cvv' => '564',
                'expiry_month' => '09',
                'expiry_year' => '32',
            ],
        ],
    ]);

    expect($charge->id)->toBe('charge-123');
    expect($charge->status->value)->toBe('pending');
});
```

### Testing Webhooks

```php
use Gowelle\Flutterwave\Events\FlutterwaveWebhookReceived;
use Illuminate\Support\Facades\Event;

it('handles webhook events', function () {
    Event::fake();

    $payload = [
        'event' => 'charge.completed',
        'data' => [
            'id' => 'charge-123',
            'status' => 'successful',
        ],
    ];

    event(new FlutterwaveWebhookReceived($payload));

    Event::assertDispatched(FlutterwaveWebhookReceived::class);
});
```

## Troubleshooting

### Common Issues

#### Authentication Errors

**Problem:** `401 Unauthorized` errors

**Solutions:**

- Verify your `FLUTTERWAVE_CLIENT_ID` and `FLUTTERWAVE_CLIENT_SECRET` are correct
- Check that credentials match your environment (staging vs production)
- Ensure credentials haven't been rotated in Flutterwave dashboard

#### Webhook Verification Failures

**Problem:** Webhook signature verification fails

**Solutions:**

- Verify `FLUTTERWAVE_SECRET_HASH` matches your webhook secret in Flutterwave dashboard
- Ensure the webhook route is accessible (not behind authentication)
- Check that the `flutterwave-signature` header is being received

#### Rate Limit Errors

**Problem:** `429 Too Many Requests` errors

**Solutions:**

- Increase `FLUTTERWAVE_RATE_LIMIT_MAX_REQUESTS` if you have higher quotas
- Implement request queuing for high-volume operations
- Use caching for frequently accessed data (banks, networks)

#### Charge Status Not Updating

**Problem:** Charge sessions not updating from webhooks

**Solutions:**

- Verify `charge_sessions.enabled` is `true` in config
- Check that webhook route is properly configured
- Ensure webhook events are being received (check logs)
- Verify database migrations have been run

#### Timeout Errors

**Problem:** Requests timing out

**Solutions:**

- Increase `FLUTTERWAVE_TIMEOUT` value
- Check network connectivity to Flutterwave API
- Verify firewall rules allow outbound connections

### Debugging

Enable detailed logging:

```env
FLUTTERWAVE_LOGGING_ENABLED=true
FLUTTERWAVE_LOG_LEVEL=debug
FLUTTERWAVE_LOG_REQUESTS=true
FLUTTERWAVE_LOG_RESPONSES=true
```

Check logs in `storage/logs/laravel.log` for detailed API interactions.

### Testing in Different Environments

Always test in staging before moving to production:

```env
# Staging
FLUTTERWAVE_ENVIRONMENT=staging
FLUTTERWAVE_CLIENT_ID=your_staging_client_id
FLUTTERWAVE_CLIENT_SECRET=your_staging_client_secret

# Production
FLUTTERWAVE_ENVIRONMENT=production
FLUTTERWAVE_CLIENT_ID=your_production_client_id
FLUTTERWAVE_CLIENT_SECRET=your_production_client_secret
```

## Static Analysis

Run PHPStan for type checking:

```bash
vendor/bin/phpstan analyse --memory-limit=512M
```

For lower-resource systems, adjust the memory limit:

```bash
vendor/bin/phpstan analyse --memory-limit=256M
```

## Code Style

Format code with Laravel Pint:

```bash
vendor/bin/pint
```

## Contributing

Contributions are welcome! Please ensure:

1. Tests pass: `vendor/bin/pest`
2. Code style: `vendor/bin/pint`
3. Type safety: `vendor/bin/phpstan analyse`

## License

MIT License. See [LICENSE](LICENSE) file for details.

## Support

For issues and questions, please visit [GitHub Issues](https://github.com/gowelle/flutterwave-php/issues).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.
