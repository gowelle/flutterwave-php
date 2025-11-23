# Changelog

All notable changes to `gowelle/flutterwave-php` will be documented in this file.

## [Unreleased]

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
