<div
    x-data="flutterwaveOtpInput({ otpLength: {{ $otpLength }} })"
    x-init="startCountdown()"
    class="flw-otp-input"
>
    <div class="flw-otp-header">
        <div class="flw-otp-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h3 class="flw-otp-title">{{ __('flutterwave::messages.enter_verification_code') }}</h3>
        <p class="flw-otp-subtitle">
            {{ __('flutterwave::messages.sent_code_message', ['length' => $otpLength]) }}
            @if ($maskedPhone)
                <strong>{{ $maskedPhone }}</strong>
            @endif
        </p>
    </div>

    {{-- Error Alert --}}
    @if ($error)
        <div class="flw-alert flw-alert-error" role="alert">
            <span>{{ $error }}</span>
        </div>
    @endif

    {{-- OTP Input Boxes --}}
    <div class="flw-otp-boxes">
        @for ($i = 0; $i < $otpLength; $i++)
            <input
                type="text"
                class="flw-otp-box"
                maxlength="1"
                x-ref="otp{{ $i }}"
                @input="handleOtpInput($event, {{ $i }})"
                @keydown="handleKeyDown($event, {{ $i }})"
                @paste="handlePaste($event)"
                @focus="$event.target.select()"
                :disabled="processing"
                inputmode="numeric"
                pattern="[0-9]*"
                autocomplete="one-time-code"
            >
        @endfor
    </div>

    {{-- Submit Button --}}
    <button
        type="button"
        class="flw-btn flw-btn-primary flw-btn-full"
        @click="submitOtp()"
        :disabled="processing || !isOtpComplete()"
        x-bind:class="{ 'flw-btn-loading': processing }"
    >
        <span x-show="!processing">{{ __('flutterwave::messages.verify_code') }}</span>
        <span x-show="processing" class="flw-btn-spinner">
            <svg class="flw-spinner" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="32" stroke-dashoffset="12"></circle>
            </svg>
            {{ __('flutterwave::messages.verifying') }}
        </span>
    </button>

    {{-- Resend OTP --}}
    <div class="flw-resend-section">
        <p class="flw-resend-text">
            {{ __('flutterwave::messages.didnt_receive_code') }}
            <template x-if="countdown > 0">
                <span class="flw-countdown">{!! __('flutterwave::messages.resend_in', ['seconds' => '<strong x-text="countdown"></strong>']) !!}</span>
            </template>
            <template x-if="countdown <= 0">
                <button type="button" wire:click="resendOtp" class="flw-resend-link" @click="resendClicked()">
                    {{ __('flutterwave::messages.resend_code') }}
                </button>
            </template>
        </p>
    </div>

    {{-- Cancel Link --}}
    <button type="button" wire:click="cancel" class="flw-cancel-link">
        {{ __('flutterwave::messages.cancel_payment') }}
    </button>
</div>

@push('scripts')
<script>
function flutterwaveOtpInput(config) {
    return {
        otpLength: config.otpLength,
        otp: Array(config.otpLength).fill(''),
        processing: false,
        countdown: 60,
        countdownInterval: null,

        startCountdown() {
            this.countdown = 60;
            this.countdownInterval = setInterval(() => {
                if (this.countdown > 0) {
                    this.countdown--;
                    @this.call('tickCountdown');
                } else {
                    clearInterval(this.countdownInterval);
                }
            }, 1000);
        },

        resendClicked() {
            clearInterval(this.countdownInterval);
            this.startCountdown();
        },

        handleOtpInput(event, index) {
            const value = event.target.value.replace(/\D/g, '');
            event.target.value = value;
            this.otp[index] = value;

            if (value && index < this.otpLength - 1) {
                this.$refs['otp' + (index + 1)].focus();
            }

            this.updateWireModel();

            if (this.isOtpComplete()) {
                this.submitOtp();
            }
        },

        handleKeyDown(event, index) {
            if (event.key === 'Backspace' && !this.otp[index] && index > 0) {
                this.$refs['otp' + (index - 1)].focus();
            }
        },

        handlePaste(event) {
            event.preventDefault();
            const paste = (event.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, this.otpLength);
            
            digits.split('').forEach((digit, i) => {
                this.otp[i] = digit;
                this.$refs['otp' + i].value = digit;
            });

            this.updateWireModel();

            const focusIndex = Math.min(digits.length, this.otpLength - 1);
            this.$refs['otp' + focusIndex].focus();

            if (this.isOtpComplete()) {
                this.submitOtp();
            }
        },

        updateWireModel() {
            @this.set('otp', this.otp.join(''));
        },

        isOtpComplete() {
            return this.otp.every(digit => digit !== '');
        },

        submitOtp() {
            if (!this.isOtpComplete() || this.processing) return;
            
            this.processing = true;
            @this.call('submitOtp');
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.flw-otp-input {
    max-width: 400px;
    margin: 0 auto;
    padding: 2rem;
    text-align: center;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.flw-otp-header {
    margin-bottom: 2rem;
}

.flw-otp-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #dbeafe 0%, #3b82f6 100%);
    border-radius: 50%;
    color: #1e40af;
}

.flw-otp-icon svg {
    width: 100%;
    height: 100%;
}

.flw-otp-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.flw-otp-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.5;
}

.flw-otp-subtitle strong {
    color: #111827;
}

.flw-otp-boxes {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.flw-otp-box {
    width: 2.75rem;
    height: 3.25rem;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    border: 2px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.flw-otp-box:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.flw-resend-section {
    margin-top: 1.5rem;
}

.flw-resend-text {
    font-size: 0.875rem;
    color: #6b7280;
}

.flw-countdown {
    color: #9ca3af;
}

.flw-countdown strong {
    color: #374151;
    font-variant-numeric: tabular-nums;
}

.flw-resend-link {
    color: #3b82f6;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    text-decoration: underline;
}

.flw-resend-link:hover {
    color: #1d4ed8;
}

.flw-cancel-link {
    display: block;
    margin: 1.5rem auto 0;
    font-size: 0.875rem;
    color: #6b7280;
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: underline;
}

.flw-cancel-link:hover {
    color: #374151;
}

/* Reuse button styles */
.flw-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.875rem 1.5rem;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    border: none;
}

.flw-btn-primary {
    background: linear-gradient(135deg, #f5a623 0%, #f77f00 100%);
    color: white;
}

.flw-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.flw-btn-full {
    width: 100%;
}

.flw-btn-spinner {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.flw-spinner {
    width: 1.25rem;
    height: 1.25rem;
    animation: flw-spin 1s linear infinite;
}

@keyframes flw-spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.flw-alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 0.75rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

@media (max-width: 400px) {
    .flw-otp-box {
        width: 2.25rem;
        height: 2.75rem;
        font-size: 1.25rem;
    }
}
</style>
@endpush
