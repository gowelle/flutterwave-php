# Upgrade Guide

## Upgrading from v1.x to v2.0

Version 2.0 introduces several breaking changes to improve code quality, maintainability, and adherence to best practices.

---

## Breaking Changes

### 1. Event Names Standardized

Event class names have been renamed to use the `Flutterwave` prefix for consistency.

**Before (v1.x):**
```php
use Gowelle\Flutterwave\Events\DirectChargeCreated;
use Gowelle\Flutterwave\Events\DirectChargeUpdated;

Event::listen(DirectChargeCreated::class, function ($event) {
    // Handle event
});
```

**After (v2.0):**
```php
use Gowelle\Flutterwave\Events\FlutterwaveChargeCreated;
use Gowelle\Flutterwave\Events\FlutterwaveChargeUpdated;

Event::listen(FlutterwaveChargeCreated::class, function ($event) {
    // Handle event
});
```

**Migration Steps:**
1. Update all event listener registrations in your `EventServiceProvider`
2. Update any manual `Event::listen()` or `Event::dispatch()` calls
3. Search your codebase for `DirectChargeCreated` and `DirectChargeUpdated` and replace with the new names

---

### 2. Service Interfaces Introduced

Services now implement interfaces, enabling better dependency injection and testing.

**Impact:** If you were type-hinting concrete service classes in your constructors, you can now use interfaces instead.

**Before (v1.x):**
```php
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;

class PaymentController
{
    public function __construct(
        private FlutterwaveDirectChargeService $chargeService
    ) {}
}
```

**After (v2.0) - Recommended:**
```php
use Gowelle\Flutterwave\Contracts\DirectChargeServiceInterface;

class PaymentController
{
    public function __construct(
        private DirectChargeServiceInterface $chargeService
    ) {}
}
```

**Available Interfaces:**
- `DirectChargeServiceInterface` → `FlutterwaveDirectChargeService`
- `CustomerServiceInterface` → `FlutterwaveCustomerService`
- `PaymentsServiceInterface` → `FlutterwavePaymentsService`

**Note:** Using concrete classes still works, but interfaces are recommended for better testability.

---

### 3. Internal DI Improvements

The internal dependency injection in `FlutterwaveBaseService` has been improved. This is an internal change and should not affect your application unless you were extending or mocking this class directly.

**Impact:** Minimal - only affects advanced use cases where you were extending package internals.

---

## New Features in v2.0

### 1. Artisan Command for Credential Verification

Test your Flutterwave API credentials:

```bash
php artisan flutterwave:verify
```

This command will:
- Verify your credentials are configured
- Test authentication with Flutterwave API
- Provide detailed feedback on any issues

### 2. Debug Mode

Enable detailed logging of API requests and responses:

```env
FLUTTERWAVE_DEBUG=true
```

> **Warning:** Only enable in development. This logs sensitive data including API keys and customer information.

### 3. Encryption Key Configuration

Configure the encryption key for card data:

```env
FLUTTERWAVE_ENCRYPTION_KEY=your-encryption-key
```

---

## Step-by-Step Migration Guide

### Step 1: Update Dependencies

Update your `composer.json`:

```bash
composer require gowelle/flutterwave-php:^2.0
```

### Step 2: Update Event Listeners

Search your codebase for the old event names:

```bash
# Find all occurrences
grep -r "DirectChargeCreated" app/
grep -r "DirectChargeUpdated" app/
```

Replace with new names:
- `DirectChargeCreated` → `FlutterwaveChargeCreated`
- `DirectChargeUpdated` → `FlutterwaveChargeUpdated`

### Step 3: Update EventServiceProvider (if applicable)

If you have event listeners registered in `app/Providers/EventServiceProvider.php`:

```php
// Before
protected $listen = [
    \Gowelle\Flutterwave\Events\DirectChargeCreated::class => [
        \App\Listeners\HandleChargeCreated::class,
    ],
];

// After
protected $listen = [
    \Gowelle\Flutterwave\Events\FlutterwaveChargeCreated::class => [
        \App\Listeners\HandleChargeCreated::class,
    ],
];
```

### Step 4: (Optional) Adopt Service Interfaces

Update your type hints to use interfaces for better testability:

```php
// Find service injections
grep -r "FlutterwaveDirectChargeService" app/
grep -r "FlutterwaveCustomerService" app/
grep -r "FlutterwavePaymentsService" app/
```

Replace with interface type hints where appropriate.

### Step 5: Test Your Application

Run your test suite to ensure everything works:

```bash
php artisan test
```

### Step 6: Verify Credentials (Optional)

Test your Flutterwave configuration:

```bash
php artisan flutterwave:verify
```

---

## Estimated Migration Time

- **Small applications** (1-5 event listeners): ~15 minutes
- **Medium applications** (5-20 event listeners): ~30 minutes  
- **Large applications** (20+ event listeners): ~1 hour

---

## Need Help?

If you encounter issues during migration:

1. Check the [CHANGELOG](CHANGELOG.md) for detailed changes
2. Review the [README](README.md) for updated examples
3. Open an issue on [GitHub](https://github.com/gowelle/flutterwave-php/issues)

---

## Rollback Instructions

If you need to rollback to v1.x:

```bash
composer require gowelle/flutterwave-php:^1.0
```

Then revert your event listener changes.
