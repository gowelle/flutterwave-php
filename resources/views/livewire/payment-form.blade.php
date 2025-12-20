<div
    x-data="flutterwavePaymentForm({
        encryptionKey: '{{ $this->getEncryptionKey() }}',
    })"
    class="flw-payment-form"
>
    {{-- Error Alert --}}
    @if ($error)
        <div class="flw-alert flw-alert-error" role="alert">
            <svg class="flw-alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ $error }}</span>
        </div>
    @endif

    {{-- Redirect Notice --}}
    @if ($actionRedirectUrl)
        <div class="flw-redirect-notice">
            <p>{{ __('flutterwave::messages.redirect_notice') }}</p>
            <a href="{{ $actionRedirectUrl }}" class="flw-btn flw-btn-primary">
                {{ __('flutterwave::messages.continue_authorization') }}
            </a>
        </div>
    @else
        <form @submit.prevent="submitPayment" class="flw-form">
            {{-- Customer Details --}}
            <div class="flw-form-section">
                <h3 class="flw-form-section-title">{{ __('flutterwave::messages.customer_details') }}</h3>
                
                <div class="flw-form-grid">
                    <div class="flw-form-group">
                        <label for="email" class="flw-label">{{ __('flutterwave::messages.email') }}</label>
                        <input
                            type="email"
                            id="email"
                            wire:model="email"
                            class="flw-input"
                            placeholder="customer@example.com"
                            required
                        >
                        @error('email') <span class="flw-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="flw-form-group">
                        <label for="phoneNumber" class="flw-label">{{ __('flutterwave::messages.phone_number') }}</label>
                        <input
                            type="tel"
                            id="phoneNumber"
                            wire:model="phoneNumber"
                            class="flw-input"
                            placeholder="+255123456789"
                            required
                        >
                        @error('phoneNumber') <span class="flw-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="flw-form-group">
                        <label for="firstName" class="flw-label">{{ __('flutterwave::messages.first_name') }}</label>
                        <input
                            type="text"
                            id="firstName"
                            wire:model="firstName"
                            class="flw-input"
                            placeholder="John"
                            required
                        >
                        @error('firstName') <span class="flw-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="flw-form-group">
                        <label for="lastName" class="flw-label">{{ __('flutterwave::messages.last_name') }}</label>
                        <input
                            type="text"
                            id="lastName"
                            wire:model="lastName"
                            class="flw-input"
                            placeholder="Doe"
                            required
                        >
                        @error('lastName') <span class="flw-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- Card Details --}}
            <div class="flw-form-section">
                <h3 class="flw-form-section-title">
                    <svg class="flw-section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    </svg>
                    {{ __('flutterwave::messages.card_details') }}
                </h3>

                <div class="flw-form-group">
                    <label for="cardNumber" class="flw-label">{{ __('flutterwave::messages.card_number') }}</label>
                    <div class="flw-input-with-icon">
                        <input
                            type="text"
                            id="cardNumber"
                            x-model="cardNumber"
                            @input="formatCardNumber"
                            class="flw-input"
                            placeholder="1234 5678 9012 3456"
                            maxlength="19"
                            autocomplete="cc-number"
                            required
                        >
                        <div class="flw-card-brand" x-show="cardBrand">
                            <img :src="getCardBrandIcon()" :alt="cardBrand" class="flw-card-brand-icon">
                        </div>
                    </div>
                </div>

                <div class="flw-form-grid flw-form-grid-3">
                    <div class="flw-form-group">
                        <label for="expiryMonth" class="flw-label">{{ __('flutterwave::messages.month') }}</label>
                        <select
                            id="expiryMonth"
                            x-model="expiryMonth"
                            class="flw-input"
                            required
                        >
                            <option value="">MM</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="flw-form-group">
                        <label for="expiryYear" class="flw-label">{{ __('flutterwave::messages.year') }}</label>
                        <select
                            id="expiryYear"
                            x-model="expiryYear"
                            class="flw-input"
                            required
                        >
                            <option value="">YY</option>
                            @for ($i = date('Y'); $i <= date('Y') + 15; $i++)
                                <option value="{{ substr($i, -2) }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="flw-form-group">
                        <label for="cvv" class="flw-label">{{ __('flutterwave::messages.cvv') }}</label>
                        <input
                            type="password"
                            id="cvv"
                            x-model="cvv"
                            @input="cvv = $event.target.value.replace(/\D/g, '').slice(0, 4)"
                            class="flw-input"
                            placeholder="***"
                            maxlength="4"
                            autocomplete="cc-csc"
                            required
                        >
                    </div>
                </div>
            </div>

            {{-- Payment Amount --}}
            @if ($amount > 0)
                <div class="flw-payment-summary">
                    <div class="flw-payment-amount">
                        <span class="flw-amount-label">{{ __('flutterwave::messages.total') }}</span>
                        <span class="flw-amount-value">{{ $currency }} {{ number_format($amount, 2) }}</span>
                    </div>
                </div>
            @endif

            {{-- Submit Button --}}
            <button
                type="submit"
                class="flw-btn flw-btn-primary flw-btn-full"
                :disabled="processing || !isFormValid()"
                x-bind:class="{ 'flw-btn-loading': processing }"
            >
                <span x-show="!processing">
                    {{ __('flutterwave::messages.pay') }} {{ $currency }} {{ number_format($amount, 2) }}
                </span>
                <span x-show="processing" class="flw-btn-spinner">
                    <svg class="flw-spinner" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="32" stroke-dashoffset="12"></circle>
                    </svg>
                    {{ __('flutterwave::messages.processing') }}
                </span>
            </button>

            {{-- Security Notice --}}
            <div class="flw-security-notice">
                <svg class="flw-security-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span>{{ __('flutterwave::messages.secured_by') }}</span>
            </div>
        </form>
    @endif
</div>

@push('scripts')
<script>
function flutterwavePaymentForm(config) {
    return {
        encryptionKey: config.encryptionKey,
        cardNumber: '',
        expiryMonth: '',
        expiryYear: '',
        cvv: '',
        cardBrand: '',
        processing: false,

        formatCardNumber() {
            let value = this.cardNumber.replace(/\s/g, '').replace(/\D/g, '');
            value = value.slice(0, 16);
            this.cardNumber = value.replace(/(.{4})/g, '$1 ').trim();
            this.detectCardBrand();
        },

        detectCardBrand() {
            const number = this.cardNumber.replace(/\s/g, '');
            if (/^4/.test(number)) {
                this.cardBrand = 'visa';
            } else if (/^5[1-5]/.test(number) || /^2[2-7]/.test(number)) {
                this.cardBrand = 'mastercard';
            } else if (/^506[01]/.test(number) || /^507[89]/.test(number) || /^6500/.test(number)) {
                this.cardBrand = 'verve';
            } else if (/^3[47]/.test(number)) {
                this.cardBrand = 'amex';
            } else {
                this.cardBrand = '';
            }
        },

        getCardBrandIcon() {
            const icons = {
                visa: 'https://cdn.jsdelivr.net/gh/greenvisionmedia/responsive-images@main/img/visa.svg',
                mastercard: 'https://cdn.jsdelivr.net/gh/greenvisionmedia/responsive-images@main/img/mastercard.svg',
                verve: 'https://cdn.jsdelivr.net/gh/greenvisionmedia/responsive-images@main/img/verve.svg',
                amex: 'https://cdn.jsdelivr.net/gh/greenvisionmedia/responsive-images@main/img/amex.svg',
            };
            return icons[this.cardBrand] || '';
        },

        isFormValid() {
            return this.cardNumber.replace(/\s/g, '').length >= 15 &&
                   this.expiryMonth !== '' &&
                   this.expiryYear !== '' &&
                   this.cvv.length >= 3;
        },

        async submitPayment() {
            if (!this.isFormValid() || this.processing) return;
            
            this.processing = true;

            try {
                const encryptedData = await this.encryptCardData();
                @this.call('submitPayment', encryptedData);
            } catch (error) {
                console.error('Encryption error:', error);
                this.processing = false;
            }
        },

        async encryptCardData() {
            const nonce = this.generateNonce(12);
            const encoder = new TextEncoder();
            
            const keyBuffer = encoder.encode(this.encryptionKey);
            const cryptoKey = await crypto.subtle.importKey(
                'raw',
                keyBuffer.slice(0, 32),
                { name: 'AES-GCM' },
                false,
                ['encrypt']
            );

            const iv = encoder.encode(nonce);
            const cardNum = this.cardNumber.replace(/\s/g, '');
            
            const encrypted = {
                nonce: nonce,
                encrypted_card_number: await this.encrypt(cryptoKey, iv, cardNum),
                encrypted_cvv: await this.encrypt(cryptoKey, iv, this.cvv),
                encrypted_expiry_month: await this.encrypt(cryptoKey, iv, this.expiryMonth),
                encrypted_expiry_year: await this.encrypt(cryptoKey, iv, this.expiryYear),
            };

            return encrypted;
        },

        async encrypt(key, iv, data) {
            const encoder = new TextEncoder();
            const encrypted = await crypto.subtle.encrypt(
                { name: 'AES-GCM', iv: iv },
                key,
                encoder.encode(data)
            );
            return btoa(String.fromCharCode(...new Uint8Array(encrypted)));
        },

        generateNonce(length) {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        }
    };
}
</script>
@endpush

@push('styles')
<style>
.flw-payment-form {
    max-width: 480px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.flw-alert {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.flw-alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.flw-alert-icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

.flw-form-section {
    margin-bottom: 1.5rem;
}

.flw-form-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.flw-section-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.flw-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.flw-form-grid-3 {
    grid-template-columns: 1fr 1fr 1fr;
}

.flw-form-group {
    margin-bottom: 1rem;
}

.flw-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.375rem;
}

.flw-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1.5px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    transition: border-color 0.15s, box-shadow 0.15s;
    background: white;
}

.flw-input:focus {
    outline: none;
    border-color: #f5a623;
    box-shadow: 0 0 0 3px rgba(245, 166, 35, 0.1);
}

.flw-input-with-icon {
    position: relative;
}

.flw-card-brand {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
}

.flw-card-brand-icon {
    height: 1.5rem;
    width: auto;
}

.flw-error {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: #dc2626;
}

.flw-payment-summary {
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
}

.flw-payment-amount {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.flw-amount-label {
    font-weight: 500;
    color: #6b7280;
}

.flw-amount-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

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

.flw-btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #e6951f 0%, #e67300 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 166, 35, 0.4);
}

.flw-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.flw-btn-full {
    width: 100%;
}

.flw-btn-loading {
    pointer-events: none;
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

.flw-security-notice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.flw-security-icon {
    width: 1rem;
    height: 1rem;
}

.flw-redirect-notice {
    text-align: center;
    padding: 2rem;
}

.flw-redirect-notice p {
    margin-bottom: 1rem;
    color: #374151;
}

@media (max-width: 480px) {
    .flw-form-grid,
    .flw-form-grid-3 {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush
