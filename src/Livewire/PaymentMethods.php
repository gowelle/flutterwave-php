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
     * Get card brand icon name.
     *
     * @param  array<string, mixed>  $method
     */
    public function getCardIcon(array $method): string
    {
        $brand = strtolower($method['card']['brand'] ?? 'generic');

        return match ($brand) {
            'visa' => 'visa',
            'mastercard' => 'mastercard',
            'verve' => 'verve',
            'amex', 'american-express' => 'amex',
            default => 'credit-card',
        };
    }

    /**
     * Check if a method is expired.
     *
     * @param  array<string, mixed>  $method
     */
    public function isExpired(array $method): bool
    {
        $card = $method['card'] ?? [];
        $expYear = (int) ($card['exp_year'] ?? 0);
        $expMonth = (int) ($card['exp_month'] ?? 0);

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
