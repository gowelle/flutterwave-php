<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Livewire component for PIN authorization input.
 *
 * Used when a card charge requires PIN authorization.
 * Handles secure PIN input with auto-submission.
 *
 * @method void dispatch(string $event, mixed ...$params)
 */
class PinInput extends Component
{
    public string $chargeId = '';

    public string $pin = '';

    public bool $processing = false;

    public string $error = '';

    public int $pinLength = 4;

    /**
     * Mount the component with charge context.
     */
    public function mount(string $chargeId, int $pinLength = 4): void
    {
        $this->chargeId = $chargeId;
        $this->pinLength = $pinLength;
    }

    /**
     * Submit the encrypted PIN.
     * PIN is encrypted on client-side before submission.
     *
     * @param  array<string, mixed>  $encryptedData
     */
    public function submitPin(array $encryptedData): void
    {
        $this->processing = true;
        $this->error = '';

        if (empty($encryptedData['encrypted_pin']) || empty($encryptedData['nonce'])) {
            $this->error = __('flutterwave::messages.pin_encryption_failed');
            $this->processing = false;

            return;
        }

        // Dispatch to parent PaymentForm component
        $this->dispatch('submit-pin',
            encryptedPin: $encryptedData['encrypted_pin'],
            nonce: $encryptedData['nonce']
        );
    }

    /**
     * Cancel PIN input and go back.
     */
    public function cancel(): void
    {
        $this->dispatch('pin-cancelled', chargeId: $this->chargeId);
    }

    /**
     * Get the encryption key for client-side encryption.
     */
    public function getEncryptionKey(): string
    {
        return config('flutterwave.encryption_key', '');
    }

    public function render(): View
    {
        return view('flutterwave::livewire.pin-input');
    }
}
