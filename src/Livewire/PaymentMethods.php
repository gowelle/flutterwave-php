<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Livewire;

use Gowelle\Flutterwave\Facades\Flutterwave;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Livewire component for managing saved payment methods.
 *
 * Displays customer's saved payment methods and allows
 * selection or addition of new payment methods.
 *
 * @method void dispatch(string $event, mixed ...$params)
 */
class PaymentMethods extends Component
{
    public string $customerId = '';

    public string $currency = 'TZS';

    public ?string $selectedMethodId = null;

    /** @var array<int, array<string, mixed>> */
    public array $methods = [];

    public bool $loading = true;

    public bool $showAddNew = false;

    public string $error = '';

    /**
     * Mount the component with customer context.
     */
    public function mount(string $customerId, string $currency = 'TZS'): void
    {
        $this->customerId = $customerId;
        $this->currency = $currency ?: config('flutterwave.default_currency', 'TZS');

        $this->loadMethods();
    }

    /**
     * Load customer's payment methods.
     */
    public function loadMethods(): void
    {
        $this->loading = true;
        $this->error = '';

        try {
            $paymentMethods = Flutterwave::payments()->methods([
                'customer_id' => $this->customerId,
                'currency' => $this->currency,
            ]);

            // Convert PaymentMethodData objects to arrays for Livewire
            $this->methods = array_map(
                fn ($method) => $method->toArray(),
                $paymentMethods
            );
        } catch (\Exception $e) {
            $this->error = 'Unable to load payment methods.';
            $this->methods = [];
        } finally {
            $this->loading = false;
        }
    }

    /**
     * Select a payment method.
     */
    public function selectMethod(string $methodId): void
    {
        $this->selectedMethodId = $methodId;
        $this->showAddNew = false;

        $this->dispatch('method-selected', methodId: $methodId);
    }

    /**
     * Toggle the add new payment method form.
     */
    public function toggleAddNew(): void
    {
        $this->showAddNew = ! $this->showAddNew;
        $this->selectedMethodId = null;

        if ($this->showAddNew) {
            $this->dispatch('show-add-method');
        }
    }

    /**
     * Get display text for a payment method.
     *
     * @param  array<string, mixed>  $method
     */
    public function getMethodDisplay(array $method): string
    {
        $type = $method['type'] ?? 'card';

        return match ($type) {
            'card' => $this->getCardDisplay($method),
            'mobile_money' => $this->getMobileMoneyDisplay($method),
            'bank_account' => $this->getBankAccountDisplay($method),
            'ussd' => $this->getUssdDisplay($method),
            'applepay' => $this->getApplePayDisplay($method),
            'googlepay' => $this->getGooglePayDisplay($method),
            'opay' => 'OPay Wallet',
            default => 'Payment Method',
        };
    }

    /**
     * Get subtitle for a payment method.
     *
     * @param  array<string, mixed>  $method
     */
    public function getMethodSubtitle(array $method): string
    {
        $type = $method['type'] ?? 'card';

        return match ($type) {
            'card' => $this->getCardSubtitle($method),
            'mobile_money' => $method['mobile_money']['country_code'] ?? '',
            'bank_account' => 'Bank Transfer',
            'ussd' => isset($method['ussd']['bank_code']) ? "Code: {$method['ussd']['bank_code']}" : '',
            'applepay' => $this->getApplePaySubtitle($method),
            'googlepay' => $this->getGooglePaySubtitle($method),
            'opay' => 'Digital Wallet',
            default => '',
        };
    }

    /**
     * Get formatted card display (last 4 digits, brand).
     *
     * @param  array<string, mixed>  $method
     */
    public function getCardDisplay(array $method): string
    {
        $card = $method['card'] ?? [];
        $last4 = $card['last4'] ?? '****';
        $brand = ucfirst($card['brand'] ?? 'Card');

        return "{$brand} •••• {$last4}";
    }

    /**
     * Get card subtitle (expiry date).
     *
     * @param  array<string, mixed>  $method
     */
    public function getCardSubtitle(array $method): string
    {
        $card = $method['card'] ?? [];
        $expMonth = $card['exp_month'] ?? '--';
        $expYear = $card['exp_year'] ?? '--';

        return "Expires {$expMonth}/{$expYear}";
    }

    /**
     * Get mobile money display.
     *
     * @param  array<string, mixed>  $method
     */
    public function getMobileMoneyDisplay(array $method): string
    {
        $mobileMoney = $method['mobile_money'] ?? [];
        $network = $mobileMoney['network'] ?? 'Mobile Money';
        $phone = $mobileMoney['phone_number'] ?? '';

        return $phone ? "{$network} {$phone}" : $network;
    }

    /**
     * Get bank account display.
     *
     * @param  array<string, mixed>  $method
     */
    public function getBankAccountDisplay(array $method): string
    {
        $bankAccount = $method['bank_account'] ?? [];
        $bankName = $bankAccount['bank_name'] ?? 'Bank';
        $last4 = $bankAccount['account_number_last4'] ?? '****';

        return "{$bankName} •••• {$last4}";
    }

    /**
     * Get USSD display.
     *
     * @param  array<string, mixed>  $method
     */
    public function getUssdDisplay(array $method): string
    {
        $ussd = $method['ussd'] ?? [];
        $bankName = $ussd['bank_name'] ?? 'Bank';

        return "USSD - {$bankName}";
    }

    /**
     * Get Apple Pay display.
     *
     * @param  array<string, mixed>  $method
     */
    public function getApplePayDisplay(array $method): string
    {
        $applepay = $method['applepay'] ?? [];
        $last4 = $applepay['last4'] ?? '';

        return $last4 ? "Apple Pay •••• {$last4}" : 'Apple Pay';
    }

    /**
     * Get Apple Pay subtitle.
     *
     * @param  array<string, mixed>  $method
     */
    public function getApplePaySubtitle(array $method): string
    {
        $applepay = $method['applepay'] ?? [];
        $expMonth = $applepay['exp_month'] ?? null;
        $expYear = $applepay['exp_year'] ?? null;

        return ($expMonth && $expYear) ? "Expires {$expMonth}/{$expYear}" : '';
    }

    /**
     * Get Google Pay display.
     *
     * @param  array<string, mixed>  $method
     */
    public function getGooglePayDisplay(array $method): string
    {
        $googlepay = $method['googlepay'] ?? [];
        $last4 = $googlepay['last4'] ?? '';

        return $last4 ? "Google Pay •••• {$last4}" : 'Google Pay';
    }

    /**
     * Get Google Pay subtitle.
     *
     * @param  array<string, mixed>  $method
     */
    public function getGooglePaySubtitle(array $method): string
    {
        $googlepay = $method['googlepay'] ?? [];
        $expMonth = $googlepay['exp_month'] ?? null;
        $expYear = $googlepay['exp_year'] ?? null;

        return ($expMonth && $expYear) ? "Expires {$expMonth}/{$expYear}" : '';
    }

    /**
     * Get icon type for a payment method.
     *
     * @param  array<string, mixed>  $method
     */
    public function getMethodIconType(array $method): string
    {
        return $method['type'] ?? 'card';
    }

    /**
     * Get card brand icon name.
     *
     * @param  array<string, mixed>  $method
     */
    public function getCardIcon(array $method): string
    {
        $type = $method['type'] ?? 'card';

        // For card-based methods, get the brand
        $brand = match ($type) {
            'card' => strtolower($method['card']['brand'] ?? 'generic'),
            'applepay' => strtolower($method['applepay']['brand'] ?? 'generic'),
            'googlepay' => strtolower($method['googlepay']['brand'] ?? 'generic'),
            default => 'generic',
        };

        return match ($brand) {
            'visa' => 'visa',
            'mastercard' => 'mastercard',
            'verve' => 'verve',
            'amex', 'american-express' => 'amex',
            default => 'credit-card',
        };
    }

    /**
     * Check if method uses inline SVG icon instead of external image.
     *
     * @param  array<string, mixed>  $method
     */
    public function usesInlineIcon(array $method): bool
    {
        $type = $method['type'] ?? 'card';

        return \in_array($type, ['mobile_money', 'bank_account', 'ussd', 'opay', 'applepay', 'googlepay']);
    }

    /**
     * Check if a method is expired.
     *
     * @param  array<string, mixed>  $method
     */
    public function isExpired(array $method): bool
    {
        $type = $method['type'] ?? 'card';

        // Get card-like data based on type
        $cardData = match ($type) {
            'card' => $method['card'] ?? [],
            'applepay' => $method['applepay'] ?? [],
            'googlepay' => $method['googlepay'] ?? [],
            default => [],
        };

        $expYear = (int) ($cardData['exp_year'] ?? 0);
        $expMonth = (int) ($cardData['exp_month'] ?? 0);

        if ($expYear === 0 || $expMonth === 0) {
            return false;
        }

        $now = now();

        return $expYear < $now->year || ($expYear === $now->year && $expMonth < $now->month);
    }

    public function render(): View
    {
        return view('flutterwave::livewire.payment-methods');
    }
}
