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
