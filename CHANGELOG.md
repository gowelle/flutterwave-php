# Changelog

All notable changes to `gowelle/flutterwave-php` will be documented in this file.

## [Unreleased]

## [2.4.0] - 2025-12-16

### Added

- **Wallets API**: Complete support for Flutterwave v4 Wallets API:

  - `resolveAccount(string $provider, string $identifier)` - Verify wallet account information
  - `getStatement(array $params)` - Retrieve wallet transaction statement with pagination
  - `getBalance(string $currency)` - Fetch available balance for a specific currency
  - `getBalances()` - Fetch available balances for all currencies

- **Wallet API Classes**:

  - `WalletAccountResolveApi` - POST endpoint for wallet account lookup
  - `WalletStatementApi` - GET endpoint for wallet statement retrieval with query parameters
  - `WalletBalanceApi` - GET endpoint for single currency balance (path parameter)
  - `WalletBalancesApi` - GET endpoint for multiple currency balances

- **Wallet Data Transfer Objects**:

  - `WalletAccountResolveData` - Response DTO with provider, identifier, and name
  - `WalletStatementData` - Response DTO with cursor pagination and transactions array
  - `WalletStatementCursor` - Pagination cursor DTO with next, previous, limit, total, and hasMoreItems
  - `WalletBalanceData` - Response DTO with currency and available balance
  - `WalletStatementRequest` - Request DTO for statement query parameters (optional)

- **Wallet Service**:

  - `FlutterwaveWalletService` - High-level service for all wallet operations
  - Registered in service provider with `wallets()` facade method
  - Full error handling and type-safe DTOs

- **Comprehensive Tests**:

  - 4 API test files covering all wallet endpoints (25 tests)
  - 1 service test file with 8 tests for FlutterwaveWalletService
  - 4 data test files for all wallet DTOs (18 tests)
  - 43 total passing tests with 169 assertions

### Improved

- **API Provider**: Updated `FlutterwaveApi` enum with 4 new wallet API cases:
  - `WALLET_ACCOUNT_RESOLVE` - `/wallets/account-resolve`
  - `WALLET_STATEMENT` - `/wallets/statement`
  - `WALLET_BALANCE` - `/wallets/balances/{currency}`
  - `WALLET_BALANCES` - `/wallets/balances`
- **Type Safety**: Full PHP 8.2 readonly properties and strict typing for wallet operations
- **Error Handling**: Comprehensive exception handling with user-friendly error messages
- **Validation**: Built-in validation for all request parameters (currency format, size limits, date formats)

### Features

- **Wallet Account Resolution**: Verify wallet account details by provider and identifier
- **Transaction Statements**: Retrieve paginated wallet statements with date range filtering
- **Balance Queries**: Fetch single or multiple currency balances
- **Cursor Pagination**: Full support for cursor-based pagination in statements
- **Query Parameters**: Support for size, from, to, next, and previous parameters
- **Type-Safe DTOs**: All responses wrapped in type-safe data transfer objects

## [2.3.0] - 2025-12-16

### Added

- **Virtual Accounts API**: Complete support for Flutterwave v4 Virtual Accounts API:

  - `list()` - List all virtual accounts with pagination and date filtering
  - `listWithParams(array $params)` - Advanced listing with custom parameters (page, size, from, to, reference)
  - `create(array $data)` - Create static or dynamic virtual accounts
  - `retrieve(string $id)` - Get virtual account details
  - `update(string $id, array $data)` - Update account status or BVN

- **Virtual Account Enums**:

  - `VirtualAccountStatus` - Account status (active, inactive) with helper methods
  - `VirtualAccountType` - Account type (static, dynamic) with helper methods
  - `VirtualAccountCurrency` - Supported currencies (NGN, GHS, EGP, KES) with conversion methods
  - `VirtualAccountUpdateAction` - Update actions (update_bvn, update_status) with helper methods

- **Virtual Account DTOs**:

  - `VirtualAccountData` - Response DTO with helper methods (`isActive()`, `isStatic()`, `isExpired()`)
  - `CreateVirtualAccountRequestDTO` - Type-safe request DTO for account creation with validation
  - `UpdateVirtualAccountRequestDTO` - Type-safe request DTO for account updates with validation

- **Comprehensive Tests**:

  - 12 unit tests for VirtualAccountApi (listing, creation, validation, error handling)
  - 16 unit tests for Virtual Account DTOs and data transformation
  - 28 total passing tests validating complete Virtual Accounts workflow

### Improved

- **API Provider**: Updated `FlutterwaveApi` enum and `FlutterwaveApiProvider` to support virtual accounts
- **Type Safety**: Full PHP 8.2 readonly properties and strict typing for virtual account operations

### Features

- **Multi-Currency Support**: NGN, GHS, EGP (with customer account number), KES (with customer account number)
- **Account Types**: Static accounts (permanent, reusable) and Dynamic accounts (temporary, expiring)
- **Advanced Filtering**: List accounts by date range, reference, pagination
- **Validation**: Built-in validation for all request parameters (reference length, currency-specific requirements, expiry ranges)
- **Status Management**: Activate/deactivate accounts with BVN updates
- **Metadata Storage**: Custom metadata support on all operations

## [2.2.0] - 2025-12-16

### Breaking Changes

- **Refund Service API Rewrite**: The Refund Service now uses typed DTOs:
  - `create()` method now requires `CreateRefundRequest` DTO instead of raw array
  - `list()` method now accepts optional `ListRefundsRequest` DTO for pagination/filtering
  - See migration examples below

### Added

- **Card Encryption Service**: New `EncryptionService` for secure card payments:

  - AES-256-GCM encryption as required by Flutterwave v4 API
  - `encrypt(string $data, string $nonce)` - Encrypt arbitrary data
  - `encryptCardData(array $card)` - Encrypt card number, expiry, CVV with shared nonce
  - `generateNonce()` - Generate random 12-character nonces
  - Automatic validation of encryption keys and card data formats

- **Enhanced ChargeRequestBuilder**: Fluent builder now supports direct charge with encryption:

  - `card(cardNumber, expiryMonth, expiryYear, cvv, billingAddress)` - Set encrypted card payment
  - `mobileMoney(network, phoneNumber)` - Set mobile money payment
  - `bankAccount(accountNumber, bankCode)` - Set bank account payment
  - `idempotencyKey(key)` - Set request deduplication key
  - `traceId(id)` - Set request tracking ID
  - `scenarioKey(key)` - Set test scenario key
  - `userId(id)` / `paymentId(id)` - Set charge session tracking IDs
  - `build()` now returns `DirectChargeRequestDTO` instead of raw array
  - `buildArray()` available for backwards compatibility

- **Refund Request DTOs**:

  - `CreateRefundRequest` - Type-safe refund creation with amount, chargeId, reason, meta
  - `ListRefundsRequest` - Pagination and date filtering for refund listings

- **New Enums**:

  - `RefundReason` - duplicate, fraudulent, requested_by_customer, expired_uncaptured_charge
  - `RefundStatus` - new, pending, succeeded, failed (with helper methods)

- **New Exception**: `EncryptionException` for encryption-related errors:

  - `missingEncryptionKey()` - When encryption key is not configured
  - `invalidEncryptionKey()` - When key format is invalid
  - `invalidNonce()` - When nonce length is incorrect
  - `encryptionFailed()` - When OpenSSL encryption fails
  - `invalidCardData()` - When card data validation fails

- **New DTO**: `DirectChargeRequestDTO` for type-safe direct charge requests

### Improved

- **RefundData**: Now includes `status` as `RefundStatus` enum with helper methods:

  - `isSuccessful()` - Check if refund completed successfully
  - `isPending()` - Check if refund is still processing
  - `isTerminal()` - Check if refund reached final state

- **Refund List Filtering**: `list()` now supports pagination and date range filtering via `ListRefundsRequest`

### Migration from v2.1.x

**Refund Creation - Before (v2.1.x):**

```php
$refund = Flutterwave::refunds()->create([
    'charge_id' => 'charge-123',
    'amount' => 500,
    'reason' => 'Customer requested refund',
]);
```

**Refund Creation - After (v2.2.0):**

```php
use Gowelle\Flutterwave\Data\Refund\CreateRefundRequest;
use Gowelle\Flutterwave\Enums\RefundReason;

$refund = Flutterwave::refunds()->create(
    new CreateRefundRequest(
        amount: 500.00,
        chargeId: 'charge-123',
        reason: RefundReason::REQUESTED_BY_CUSTOMER,
    )
);
```

**Card Charge - Before (v2.1.x):**

```php
$request = ChargeRequestBuilder::create('ref-123')
    ->amount(1000, 'NGN')
    ->customer('user@example.com', 'John', 'Doe')
    ->redirectUrl('https://example.com/callback')
    ->build(); // Returns array

// Manual encryption required
```

**Card Charge - After (v2.2.0):**

```php
$request = ChargeRequestBuilder::create('ref-123')
    ->amount(1000, 'NGN')
    ->customer('user@example.com', 'John', 'Doe')
    ->redirectUrl('https://example.com/callback')
    ->card('4111111111111111', '12', '25', '123') // Auto-encrypted
    ->build(); // Returns DirectChargeRequestDTO
```

## [2.1.1] - 2025-12-16

### Added

- **Transfer Testing with Scenario Keys**: Improved integration test reliability using Flutterwave's X-Scenario-Key header:

  - `TransferScenario` enum with all 23 available transfer test scenarios (successful, insufficient_balance, invalid_currency, etc.)
  - Support for scenario keys in transfer request DTOs via `scenarioKey` parameter
  - Deterministic transfer execution tests using `scenario:successful`
  - Comprehensive failure scenario tests for error handling validation

- **Context-Aware Default Scenario Keys**: Transfer operations now use appropriate default scenario keys:
  - Transfer endpoints default to `scenario:successful` for deterministic testing
  - Recipient/Sender endpoints use no scenario key (not supported by API)
  - Charge endpoints continue using `scenario:auth_redirect`

### Improved

- **Header Configuration**: Enhanced header handling to support nullable scenario keys:
  - `AbstractHeadersConfig` now properly handles null scenario keys
  - `HeaderConfig` supports optional scenario keys for endpoints that don't require them
  - Headers are only included when values are present (cleaner HTTP requests)

### Fixed

- Fixed integration tests that were failing due to missing X-Scenario-Key header in recipient/sender operations
- Recipient creation tests now pass consistently in staging environment
- Transfer execution tests work reliably with deterministic scenario key responses

## [2.1.0] - 2025-12-16

### Breaking Changes

- **Transfer API Rewrite**: The entire Transfer API has been rewritten with correct endpoint handling:
  - Old methods using raw arrays are replaced with typed DTOs
  - `create()` method now requires `CreateTransferRequest` DTO
  - Old `TransferData` DTO moved to `Gowelle\Flutterwave\Data\Transfer\TransferData`
  - See migration examples below

### Added

- **Direct Transfer Orchestrator**: New methods for inline recipient creation:

  - `bankTransfer(BankTransferRequest)` - Bank account transfers
  - `mobileMoneyTransfer(MobileMoneyTransferRequest)` - Mobile money transfers
  - `walletTransfer(WalletTransferRequest)` - Flutterwave wallet transfers

- **Transfer Recipients**: Full CRUD support:

  - `createRecipient(CreateRecipientRequest)` - Create recipient
  - `getRecipient(string $id)` - Get recipient by ID
  - `listRecipients()` - List all recipients
  - `deleteRecipient(string $id)` - Delete recipient

- **Transfer Senders**: Sender management:

  - `createSender(CreateSenderRequest)` - Create sender
  - `getSender(string $id)` - Get sender by ID
  - `listSenders()` - List all senders

- **Transfer Rates**: Currency conversion rates:

  - `getRate(GetRateRequest)` - Get rate for currency pair
  - `listRates()` - List available rates

- **New Enums**:

  - `TransferAction` - instant, deferred, scheduled
  - `TransferType` - bank, mobile_money, wallet
  - `TransferStatus` - NEW, PENDING, SUCCEEDED, FAILED, etc.

- **New Interface**: `TransferServiceInterface` for dependency injection

### Migration from v2.0.x

**Before (v2.0.x - incorrect):**

```php
$transfer = Flutterwave::transfers()->create([
    'account_bank' => '044',
    'account_number' => '0123456789',
    'amount' => 5000,
    // ...
]);
```

**After (v2.1.0 - correct):**

```php
use Gowelle\Flutterwave\Data\Transfer\BankTransferRequest;

$transfer = Flutterwave::transfers()->bankTransfer(
    new BankTransferRequest(
        amount: 5000,
        sourceCurrency: 'NGN',
        destinationCurrency: 'NGN',
        accountNumber: '0123456789',
        bankCode: '044',
        reference: 'PAYOUT-' . uniqid(),
    )
);
```

## [2.0.0] - 2025-12-11

### Breaking Changes

- **Event Renaming**: Events renamed for consistency with package naming convention:

  - `DirectChargeCreated` → `FlutterwaveChargeCreated`
  - `DirectChargeUpdated` → `FlutterwaveChargeUpdated`
  - See [UPGRADE.md](UPGRADE.md) for migration guide

- **PaymentsService Return Types**: Methods now return proper DTOs instead of `ApiResponse`:
  - `methods()` → `PaymentMethodData[]`
  - `createMethod()` → `PaymentMethodData`
  - `getMethod()` → `?PaymentMethodData`
  - `process()` → `ChargeData`

### Added

- **Service Interfaces**: New contracts for better dependency injection and testing:

  - `DirectChargeServiceInterface`
  - `CustomerServiceInterface`
  - `PaymentsServiceInterface`

- **Artisan Command**: `php artisan flutterwave:verify` to test API credentials

- **Configuration Options**:

  - `encryption_key` - For encrypting sensitive card data
  - `debug` - Enable detailed API request/response logging (development only)

- **Developer Experience**:
  - `@example` annotations added to key service methods
  - Comprehensive UPGRADE.md migration guide

### Improved

- **Dependency Injection**: `FlutterwaveBaseService` now receives `FlutterwaveApiProvider` via constructor injection (no more `app()` helper calls)
- **Static Analysis**: PHPStan upgraded from level 5 to level 6 with 0 errors
- **Test Coverage**: Added 30+ new tests covering DTOs, exceptions, models, and integration flows

## [1.0.6] - 2025-12-10

### Added

- Added `DirectChargeApi` to `FlutterwaveApiProvider` for direct charge orchestrator support
- Direct charge API can now be instantiated via the provider using `FlutterwaveApi::DIRECT_CHARGE`

### Improved

- Enhanced exception messages in `FlutterwaveApiProvider` for better debugging
  - Invalid API errors now include the attempted API value
  - Separated `ValidationException` handling for header configuration issues
  - More descriptive messages for API initialization failures
  - Exception chaining preserved for full debugging context

### Fixed

- Fixed PHPStan error: Removed unreachable `default` case in match statement
- Removed unused `InvalidApiException` import

## [1.0.4] - 2025-01-27

### Fixed

- Fixed migration publishing error by adding missing `.stub` file for `create_flutterwave_charge_sessions_table` migration
- Migration publishing now works correctly when using `php artisan vendor:publish --tag="flutterwave-migrations"`

## [1.0.3] - 2025-01-27

### Fixed

- Fixed webhook route registration issue that caused 404 errors in tests
- Added proper exception handling in webhook route to return 500 status codes for verification failures
- Webhook route now always registers regardless of config state (config has default value)

## [1.0.2] - 2025-01-27

### Changed

- Refactored `FlutterwaveServiceProvider` to extend `PackageServiceProvider` from `spatie/laravel-package-tools`
- Updated config, migration, and command registration to use package tools fluent API
- Improved service provider structure and maintainability

### Added

- Added `spatie/laravel-package-tools` as a dependency for better package management

## [1.0.1] - 2025-01-27

### Added

- Laravel 12 support alongside Laravel 11

## [1.0.0] - 2025-11-23

### Added

- Initial release of gowelle/flutterwave-php
- Support for Flutterwave v4 API
- Payment processing and direct charge flow
- Customer management
- Bank operations and mobile money support
- Refunds API with full CRUD operations
- Transfers/Payouts API
- Settlements API (read-only)
- Automatic retry with exponential backoff
- Rate limiting with configurable thresholds
- Comprehensive error handling with user-friendly messages
- Webhook signature verification
- Support for idempotency keys and trace IDs
- Full test suite with Pest framework
- Static analysis with PHPStan
- Code style enforcement with Laravel Pint

### Technical Features

- Strict PHP 8.2+ typing
- PSR-12 compliant code style
- Database migrations for charge session tracking
- Service provider for easy Laravel integration
- Facade for convenient access to services
- Comprehensive configuration options
- Retry logic with exponential backoff
- Rate limiting per API endpoint
- Support for both staging and production environments

### Breaking Changes

None - initial release.
