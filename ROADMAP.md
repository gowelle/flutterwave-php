# Flutterwave PHP Package Roadmap

> **Current Version:** 2.1.0  
> **Last Updated:** December 2025  
> **Maintainer:** Gowelle

This roadmap outlines the planned features, improvements, and milestones for the `gowelle/flutterwave-php` Laravel package.

---

## Legend

| Status | Meaning |
|--------|---------|
| ✅ | Completed |
| 🚧 | In Progress |
| 📋 | Planned |
| 💡 | Under Consideration |

---

## v2.x Series (Current)

### ✅ v2.1.0 - Enhanced Transfers (December 2025)

**Transfer Orchestrator:**
- ✅ Direct Transfer Service (bank, mobile money, wallet)
- ✅ `BankTransferRequest`, `MobileMoneyTransferRequest`, `WalletTransferRequest` DTOs
- ✅ Transfer retry/retry functionality
- ✅ Transfer rates API

**Transfer Recipients:**
- ✅ Create/Get/List/Delete recipients
- ✅ `CreateRecipientRequest` and `RecipientData` DTOs

**Transfer Senders:**
- ✅ Create/Get/List senders
- ✅ `CreateSenderRequest` and `SenderData` DTOs

**General Flow:**
- ✅ `CreateTransferRequest` with recipient_id/sender_id
- ✅ `TransferServiceInterface` for DI

**Events:**
- ✅ `FlutterwaveTransferCreated` event

**Enums:**
- ✅ `TransferAction` (instant, deferred, scheduled)
- ✅ `TransferType` (bank, mobile_money, wallet)
- ✅ `TransferStatus` (NEW, PENDING, SUCCEEDED, FAILED, etc.)

---

### ✅ v2.0.0 - Released (December 2025)

**Core Features:**
- ✅ Complete Flutterwave v4 API Support
- ✅ Direct Charge Orchestrator with multi-step authorization
- ✅ Payment Methods Management (create, list, get)
- ✅ Customers API (CRUD operations)
- ✅ Orders API (create, read, update, list)
- ✅ Refunds API (create, get, list)
- ✅ Transfers/Payouts API (bank accounts)
- ✅ Settlements API (read-only)
- ✅ Banks API (list, branches, account resolution)
- ✅ Mobile Networks API
- ✅ Charge Session Tracking (database-backed)
- ✅ Webhook Verification & Event Dispatching
- ✅ Retry Logic with Exponential Backoff
- ✅ Rate Limiting
- ✅ Service Interfaces for DI/Testing
- ✅ PHPStan Level 6 Compliance

---

## v2.2.0 - Additional Payment Methods (Q1 2026)

### 📋 Bulk Transfers
**Features:**
- 📋 Bulk transfer support
- 📋 Batch status tracking

### 📋 USSD Payments
Add support for USSD payment flow.

**Features:**
- 📋 USSD charge initiation
- 📋 Bank-specific USSD codes
- 📋 USSD status polling

### 📋 Pay With Bank Transfer
Enable customers to pay via direct bank transfer.

**Features:**
- 📋 Virtual account creation
- 📋 Dynamic virtual accounts
- 📋 Bank transfer charge tracking

### 📋 OPay Integration
**Features:**
- 📋 OPay payment method support
- 📋 OPay-specific authorization flow

---

## v2.3.0 - Payment Operations (Q3 2026)

### 📋 Chargebacks API
Full chargeback management capabilities.

**Features:**
- 📋 List chargebacks
- 📋 Get chargeback details
- 📋 Accept/Decline chargebacks
- 📋 Chargeback webhook events
- 📋 `FlutterwaveChargebackReceived` event

### 📋 FX (Foreign Exchange) API
Real-time currency conversion support.

**Features:**
- 📋 Get FX rates
- 📋 Currency conversion
- 📋 Rate caching for performance

### 📋 Transaction History
Enhanced transaction querying and reporting.

**Features:**
- 📋 Transaction search with filters
- 📋 Transaction timeline
- 📋 Fee breakdown per transaction
- 📋 Export capabilities

---

## v3.0.0 - Major Enhancement (Q4 2026)

### 📋 Sub-accounts API
Complete sub-account management for marketplaces.

**Features:**
- 📋 Create sub-accounts
- 📋 Update sub-accounts
- 📋 Delete sub-accounts
- 📋 Split payment configurations
- 📋 Sub-account settlements

### 📋 Payment Plans (Recurring)
Subscription and recurring payment support.

**Features:**
- 📋 Create payment plans
- 📋 Subscribe customers to plans
- 📋 Cancel subscriptions
- 📋 Update payment plans
- 📋 Recurring payment webhooks
- 📋 `FlutterwaveSubscriptionCreated` event
- 📋 `FlutterwaveSubscriptionCancelled` event

### 📋 Virtual Cards API
Virtual card issuance and management.

**Features:**
- 📋 Create virtual cards
- 📋 Fund virtual cards
- 📋 Block/Unblock cards
- 📋 Terminate cards
- 📋 Card transaction history

### 💡 Bills Payment API
Utility and bill payments.

**Features:**
- 💡 Airtime purchase
- 💡 Data bundles
- 💡 Cable TV subscriptions
- 💡 Electricity bills
- 💡 Bill categories listing

---

## Developer Experience Improvements

### v2.1.0
- 📋 `flutterwave:status` command for health checks
- 📋 IDE helper generation for Facade autocomplete
- 📋 Enhanced debug logging with request/response IDs

### v2.2.0
- 📋 Laravel Telescope integration
- 📋 Livewire components for common payment flows
- 📋 Blade components for payment forms

### v3.0.0
- 📋 Admin dashboard package (optional)
- 📋 Payment analytics and reporting
- 📋 Webhook retry queue with dead letter handling

---

## Testing & Quality

### Ongoing
- 📋 Increase test coverage to 90%+
- 📋 Add mutation testing
- 📋 Performance benchmarking suite
- 📋 Docker-based integration testing

### v3.0.0
- 📋 PHPStan Level 8 compliance
- 📋 Full E2E test suite with Flutterwave sandbox

---

## Documentation

### v2.1.0
- 📋 Video tutorials for common use cases
- 📋 API reference documentation site
- 📋 Migration guides for common payment patterns

### v2.2.0
- 📋 Interactive playground/sandbox
- 📋 Code examples repository
- 📋 Framework integration guides (Livewire, Inertia)

---

## Infrastructure

### v2.1.0
- 📋 GitHub Actions: automated changelogs
- 📋 Dependabot security updates

### v3.0.0
- 📋 Multi-tenant support
- 📋 Queue-based webhook processing
- 📋 Redis cache adapter optimization

---

## Community Contributions Welcome

We welcome contributions in the following areas:

1. **New Payment Methods** - Region-specific payment method implementations
2. **Documentation** - Tutorials, guides, and examples
3. **Bug Fixes** - Issue reports and pull requests
4. **Testing** - Additional test coverage and edge cases
5. **Performance** - Optimization suggestions and implementations

---

## Deprecation Schedule

| Version | Deprecations |
|---------|--------------|
| v3.0.0 | `DirectChargeCreated` → Use `FlutterwaveChargeCreated` |
| v3.0.0 | `DirectChargeUpdated` → Use `FlutterwaveChargeUpdated` |
| v4.0.0 | PHP 8.2 support (minimum PHP 8.3) |

---

## Version Support

| Version | PHP | Laravel | Status |
|---------|-----|---------|--------|
| 2.x | 8.2+ | 11.x, 12.x | Active |
| 1.x | 8.2+ | 11.x | Security fixes only |

---

## Feedback & Suggestions

Have a feature request or suggestion? Open an issue on [GitHub](https://github.com/gowelle/flutterwave-php/issues) with the `enhancement` label.

---

*This roadmap is subject to change based on community feedback, Flutterwave API updates, and development priorities.*
