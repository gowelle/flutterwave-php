# Release v3.0.0

**Release Date:** 2026-03-22

This is a **major** release: minimum PHP is raised to **8.3**, **Laravel 13** is supported alongside Laravel 11 and 12, and development tooling moves to **Pest 4** and **PHPStan 2**.

## Highlights

### Platform and dependencies

- **PHP `^8.3`** (required by Laravel 13 and Pest 4; PHP 8.2 is no longer supported).
- **`illuminate/*` `^11.0|^12.0|^13.0`** — install this package on Laravel 11, 12, or 13; Composer resolves the matching component versions with the host application.
- **`spatie/laravel-package-tools` `^1.93`** for Laravel 13 compatibility.
- **Development:** `orchestra/testbench` `^9|^10|^11`, `pestphp/pest` `^4.0`, `pestphp/pest-plugin-laravel` `^4.0`, `phpstan/phpstan` `^2.0`.

### CI

- GitHub Actions **tests** workflow runs on **PHP 8.3, 8.4, and 8.5** (unit/feature tests, PHPStan, Pint).
- Integration and Pint workflows use **PHP 8.3** to match the new minimum.

### Package code

- **`ChargeSession` model:** Laravel 13-style Eloquent class attributes — `#[Table('flutterwave_charge_sessions', keyType: 'string', incrementing: false)]` and `#[Guarded(['id'])]` — replacing the previous `$table`, `$keyType`, `$incrementing`, and `$guarded` properties.
- **PHPStan 2:** Stricter analysis; small fixes for customer DTOs (`CreateCustomerRequest` / `UpdateCustomerRequest`), `PaymentMethodFactory::create()` return type, and `FlutterwavePaymentsService::methods()` (removed redundant `array_filter` after non-null factory return).
- **Pint:** `fully_qualified_strict_types` and related rules applied across touched files where applicable.

### Documentation

- **README** requirements updated for PHP 8.3+ and Laravel 11 / 12 / 13.

## Breaking changes

1. **PHP:** Projects on **PHP 8.2** must upgrade to **PHP 8.3 or newer** before using this release.
2. **No API surface change** is intended beyond stricter typing and the customer DTO / payment-method factory adjustments already aligned with PHPStan 2; host applications upgrading to Laravel 13 should follow the [Laravel 13 upgrade guide](https://laravel.com/docs/13.x/upgrade).

## Upgrade steps

1. Upgrade the server and local PHP runtime to **8.3+**.
2. In your application: `composer require gowelle/flutterwave-php:^3.0` (adjust constraint as needed).
3. Run your test suite and `vendor/bin/phpstan analyse` if you analyze this package or your integration code.
4. For Laravel 13 host apps, complete framework upgrades per the official docs (e.g. CSRF middleware naming, cache config).

## Files changed (summary)

- `composer.json`, `README.md`, `phpstan.neon`
- `.github/workflows/tests.yml`, `integration-tests.yml`, `pint.yml`
- `src/Models/ChargeSession.php`, customer DTOs, `PaymentMethodFactory.php`, `FlutterwavePaymentsService.php`, and related services/tests

## Full compare

**Full changelog:** https://github.com/gowelle/flutterwave-php/compare/v2.12.0...v3.0.0
