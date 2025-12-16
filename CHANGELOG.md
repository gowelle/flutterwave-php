# Changelog

All notable changes to `gowelle/flutterwave-php` will be documented in this file.

## [Unreleased]

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
