# Release v2.12.0

**Release Date:** 2025-02-02

## What's Changed

### ✨ Added

- **FlutterwaveBanksService**: `resolveFromDto(BankAccountResolveRequest $request)` – type-safe alternative to `resolveAccount()` for bank account resolution. Matches `BankAccountResolveApi::resolveFromDto()`.

### 🔄 Changed

- **Customer API v4 alignment** ([customers_create](https://developer.flutterwave.com/reference/customers_create), [customers_put](https://developer.flutterwave.com/reference/customers_put)):
  - Only `email` is required for create/update; `name`, `phone`, and `address` are optional.
  - `phone` is now an object: `{ country_code: string (ISO 3166 alpha-3), number: string (7–10 digits) }` instead of a string `phone_number`.
  - Optional `address` support added to `CreateCustomerRequest` and `UpdateCustomerRequest` (line1, line2, city, state, postal_code, country).
  - `CustomerApi::validateCreateData()` and `validateUpdateData()` relaxed to match v4 (email only required; `phone` object validated when present).
- **FlutterwaveCustomerService**: DTO methods exposed – `createFromDto(CreateCustomerRequest)`, `updateFromDto(string $id, UpdateCustomerRequest)`, `searchFromDto(SearchCustomerRequest)` (previously only on `CustomerApi`).

## Files Changed

- `CHANGELOG.md`, `README.md`, `RELEASE_NOTES.md`
- `src/Api/Customer/CustomerApi.php`
- `src/Contracts/CustomerServiceInterface.php`
- `src/Data/Customer/CreateCustomerRequest.php`, `UpdateCustomerRequest.php`
- `src/Services/FlutterwaveBanksService.php`, `FlutterwaveCustomerService.php`
- `tests/Integration/*`, `tests/Unit/Data/Customer/*`, `tests/Unit/Services/FlutterwaveBanksServiceTest.php`, `FlutterwaveCustomerServiceTest.php`

## Upgrade Notes

**Customer API (breaking for DTO callers):** If you use `CreateCustomerRequest` or `UpdateCustomerRequest`, replace `phoneNumber: '+255...'` with `phone: ['country_code' => 'TZA', 'number' => '712345678']`. Array-based `create()`/`update()` now accept optional `phone` object; `phone_number` is no longer validated for Customer API.

**Full Changelog**: https://github.com/gowelle/flutterwave-php/compare/v2.11.2...v2.12.0

---

# Release v2.11.0

**Release Date:** 2026-01-15

## What's Changed

### 🎨 UI Improvements

- **Dark Mode Support** - Added full dark mode support to all Vue components:
  - `PaymentForm.vue` - Inputs, text, and backgrounds adapt to system theme.
  - `PaymentMethods.vue` - Payment cards and method lists are dark-mode ready.
  - `OtpInput.vue` & `PinInput.vue` - Styled for visibility in dark environments.
  - `PaymentStatus.vue` - Success/Error states optimized for dark mode.
- **Mobile Responsiveness** - Improved layouts for smaller screens:
  - Better padding and width control on mobile devices.
  - Responsive grid layouts for form inputs.

## Files Changed

- `resources/js/components/PaymentForm.vue`
- `resources/js/components/PaymentMethods.vue`
- `resources/js/components/OtpInput.vue`
- `resources/js/components/PinInput.vue`
- `resources/js/components/PaymentStatus.vue`
- `package.json`

## Upgrade Notes

No breaking changes. This update purely affects styles. Ensure your build pipeline processes the new CSS.

**Full Changelog**: https://github.com/gowelle/flutterwave-php/compare/v2.10.0...v2.11.0

# Release v2.9.2

**Release Date:** 2026-01-14

## What's Changed

### 📚 Documentation Improvements

- **Enhanced UI Component Documentation** - Major expansion of README.md UI Components section with:
  - Step-by-step Backend API Setup with example controller
  - Complete Payment Flow Example showing PaymentForm → PinInput → OtpInput → PaymentStatus integration
  - Detailed Props & Events Reference tables for all Vue components
  - `useFlutterwave` Composable documentation
  - Styling & Customization guide with CSS class reference

- **Livewire Documentation** - Added comprehensive Livewire how-to guide:
  - Complete `CheckoutPage` Livewire component example
  - Blade template examples
  - Livewire Events Reference table with all component events and payloads

### 🐛 Bug Fixes

- **Fixed Card Brand Icons** - Replaced broken CDN URLs with working GitHub raw URLs
  - Updated `PaymentForm.vue`, `PaymentMethods.vue`, `payment-form.blade.php`, `payment-methods.blade.php`
  - Icons now load correctly for Visa, Mastercard, Amex
  - Added generic fallback for Verve cards

## Files Changed

- `README.md` - Enhanced UI component documentation
- `resources/js/components/PaymentForm.vue` - Fixed card brand icon URLs
- `resources/js/components/PaymentMethods.vue` - Fixed card brand icon URLs
- `resources/views/livewire/payment-form.blade.php` - Fixed card brand icon URLs
- `resources/views/livewire/payment-methods.blade.php` - Fixed card brand icon URLs

## Upgrade Notes

No breaking changes. This is a documentation and bug fix release.

**Full Changelog**: https://github.com/gowelle/flutterwave-php/compare/v2.9.1...v2.9.2
