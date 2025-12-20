<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Livewire;

use Gowelle\Flutterwave\Data\DirectChargeData;
use Gowelle\Flutterwave\Enums\DirectChargeStatus;
use Gowelle\Flutterwave\Facades\Flutterwave;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Livewire component for displaying payment status.
 *
 * Shows real-time payment status with polling capability.
 * Used for redirect flows and async payment methods.
 *
 * @method void dispatch(string $event, mixed ...$params)
 */
class PaymentStatus extends Component
{
    public string $chargeId = '';

    public string $status = 'pending';

    public string $statusMessage = ''; // Will be set in mount or checkStatus

    public ?float $amount = null;

    public ?string $currency = null;

    public ?string $reference = null;

    public bool $polling = false;

    public int $pollInterval = 3000; // milliseconds

    public int $maxPolls = 60;

    public int $pollCount = 0;

    public bool $showDetails = false;

    /** @var array<string, mixed> */
    public array $chargeData = [];

    /**
     * Mount the component with charge ID.
     */
    public function mount(
        string $chargeId,
        bool $startPolling = false,
        int $pollInterval = 3000,
        int $maxPolls = 60,
    ): void {
        $this->chargeId = $chargeId;
        $this->pollInterval = $pollInterval;
        $this->maxPolls = $maxPolls;

        // Fetch initial status
        $this->checkStatus();

        if ($startPolling && ! $this->isTerminalStatus()) {
            $this->polling = true;
        }
    }

    /**
     * Check the current charge status.
     */
    public function checkStatus(): void
    {
        if (empty($this->chargeId)) {
            return;
        }

        try {
            // Use retrieve() to get full charge data, not just status enum
            $charge = Flutterwave::directCharge()->retrieve($this->chargeId);
            $this->updateFromCharge($charge);
        } catch (\Exception $e) {
            $this->statusMessage = __('flutterwave::messages.payment_failed');
        }
    }

    /**
     * Poll for status updates (called from JS timer).
     */
    public function poll(): void
    {
        if (! $this->polling || $this->isTerminalStatus()) {
            $this->polling = false;

            return;
        }

        $this->pollCount++;

        if ($this->pollCount >= $this->maxPolls) {
            $this->polling = false;
            $this->statusMessage = __('flutterwave::messages.payment_timeout');
            $this->dispatch('polling-timeout', chargeId: $this->chargeId);

            return;
        }

        $this->checkStatus();
    }

    /**
     * Update component state from charge data.
     */
    protected function updateFromCharge(DirectChargeData $charge): void
    {
        $this->status = $charge->status->value;
        $this->amount = $charge->amount;
        $this->currency = $charge->currency;
        $this->reference = $charge->reference;
        $this->chargeData = $charge->toArray();

        $this->statusMessage = match ($charge->status) {
            DirectChargeStatus::PENDING => __('flutterwave::messages.processing_payment'),
            DirectChargeStatus::REQUIRES_ACTION => __('flutterwave::messages.awaiting_authorization'),
            DirectChargeStatus::SUCCEEDED => __('flutterwave::messages.payment_successful'),
            DirectChargeStatus::FAILED => $charge->getIssuerResponseMessage() ?? __('flutterwave::messages.payment_failed'),
            DirectChargeStatus::CANCELLED => __('flutterwave::messages.payment_cancelled'),
            DirectChargeStatus::TIMEOUT => __('flutterwave::messages.payment_timeout'),
        };

        if ($charge->status->isSuccessful()) {
            $this->polling = false;
            $this->showDetails = true;
            $this->dispatch('payment-success', charge: $charge->toArray());
        }

        if ($charge->status->isTerminal() && ! $charge->status->isSuccessful()) {
            $this->polling = false;
            $this->dispatch('payment-failed', charge: $charge->toArray());
        }
    }

    /**
     * Check if current status is terminal (no more changes expected).
     */
    protected function isTerminalStatus(): bool
    {
        return \in_array($this->status, ['succeeded', 'failed', 'cancelled', 'timeout']);
    }

    /**
     * Start polling for status updates.
     */
    public function startPolling(): void
    {
        $this->polling = true;
        $this->pollCount = 0;
    }

    /**
     * Stop polling.
     */
    public function stopPolling(): void
    {
        $this->polling = false;
    }

    /**
     * Get the status icon name.
     */
    public function getStatusIcon(): string
    {
        return match ($this->status) {
            'succeeded' => 'check-circle',
            'failed', 'cancelled', 'timeout' => 'x-circle',
            'pending', 'requires_action' => 'clock',
            default => 'loader',
        };
    }

    /**
     * Get the status color class.
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'succeeded' => 'text-green-500',
            'failed', 'cancelled', 'timeout' => 'text-red-500',
            'pending', 'requires_action' => 'text-yellow-500',
            default => 'text-gray-500',
        };
    }

    public function render(): View
    {
        return view('flutterwave::livewire.payment-status');
    }
}
