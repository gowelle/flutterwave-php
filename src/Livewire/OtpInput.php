<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Livewire component for OTP verification input.
 *
 * Used when a card charge requires OTP (soft token) verification.
 * Handles OTP input with resend functionality and countdown timer.
 *
 * @method void dispatch(string $event, mixed ...$params)
 */
class OtpInput extends Component
{
    public string $chargeId = '';

    public string $otp = '';

    public bool $processing = false;

    public string $error = '';

    public int $otpLength = 6;

    public int $resendCountdown = 0;

    public bool $canResend = false;

    public string $maskedPhone = '';

    /**
     * Mount the component with charge context.
     */
    public function mount(string $chargeId, int $otpLength = 6, string $maskedPhone = ''): void
    {
        $this->chargeId = $chargeId;
        $this->otpLength = $otpLength;
        $this->maskedPhone = $maskedPhone;
        $this->startResendCountdown();
    }

    /**
     * Start the resend countdown timer.
     */
    public function startResendCountdown(): void
    {
        $this->resendCountdown = 60;
        $this->canResend = false;
    }

    /**
     * Tick the countdown timer (called from JS).
     */
    public function tickCountdown(): void
    {
        if ($this->resendCountdown > 0) {
            $this->resendCountdown--;
        }

        if ($this->resendCountdown === 0) {
            $this->canResend = true;
        }
    }

    /**
     * Submit the OTP for verification.
     */
    public function submitOtp(): void
    {
        $this->processing = true;
        $this->error = '';

        // Validate OTP format
        if (\strlen($this->otp) !== $this->otpLength) {
            $this->error = __('flutterwave::messages.invalid_otp_length', ['length' => $this->otpLength]);
            $this->processing = false;

            return;
        }

        if (! ctype_digit($this->otp)) {
            $this->error = __('flutterwave::messages.otp_digits_only');
            $this->processing = false;

            return;
        }

        // Dispatch to parent PaymentForm component
        $this->dispatch('submit-otp', otp: $this->otp);
    }

    /**
     * Request to resend OTP.
     */
    public function resendOtp(): void
    {
        if (! $this->canResend) {
            return;
        }

        $this->dispatch('resend-otp', chargeId: $this->chargeId);
        $this->startResendCountdown();
    }

    /**
     * Cancel OTP input and go back.
     */
    public function cancel(): void
    {
        $this->dispatch('otp-cancelled', chargeId: $this->chargeId);
    }

    public function render(): View
    {
        return view('flutterwave::livewire.otp-input');
    }
}
