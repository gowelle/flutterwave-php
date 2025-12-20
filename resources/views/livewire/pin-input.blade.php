<div
    x-data="flutterwavePinInput({
        encryptionKey: '{{ $this->getEncryptionKey() }}',
        pinLength: {{ $pinLength }},
    })"
    class="flw-pin-input"
>
    <div class="flw-pin-header">
        <div class="flw-pin-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h3 class="flw-pin-title">Enter Your Card PIN</h3>
        <p class="flw-pin-subtitle">Please enter your {{ $pinLength }}-digit card PIN to authorize this payment</p>
    </div>

    {{-- Error Alert --}}
    @if ($error)
        <div class="flw-alert flw-alert-error" role="alert">
            <span>{{ $error }}</span>
        </div>
    @endif

    {{-- PIN Input Boxes --}}
    <div class="flw-pin-boxes">
        @for ($i = 0; $i < $pinLength; $i++)
            <input
                type="password"
                class="flw-pin-box"
                maxlength="1"
                x-ref="pin{{ $i }}"
                @input="handlePinInput($event, {{ $i }})"
                @keydown="handleKeyDown($event, {{ $i }})"
                @paste="handlePaste($event)"
                :disabled="processing"
                inputmode="numeric"
                pattern="[0-9]*"
            >
        @endfor
    </div>

    {{-- Keypad (for mobile) --}}
    <div class="flw-keypad" x-show="showKeypad">
        @for ($i = 1; $i <= 9; $i++)
            <button type="button" class="flw-keypad-btn" @click="handleKeypadPress('{{ $i }}')" :disabled="processing">
                {{ $i }}
            </button>
        @endfor
        <button type="button" class="flw-keypad-btn" @click="clearPin()" :disabled="processing">
            <svg class="flw-keypad-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"></path>
            </svg>
        </button>
        <button type="button" class="flw-keypad-btn" @click="handleKeypadPress('0')" :disabled="processing">
            0
        </button>
        <button type="button" class="flw-keypad-btn flw-keypad-btn-submit" @click="submitPin()" :disabled="processing || !isPinComplete()">
            <svg class="flw-keypad-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </button>
    </div>

    {{-- Submit Button --}}
    <button
        type="button"
        class="flw-btn flw-btn-primary flw-btn-full"
        @click="submitPin()"
        :disabled="processing || !isPinComplete()"
        x-bind:class="{ 'flw-btn-loading': processing }"
    >
        <span x-show="!processing">Confirm PIN</span>
        <span x-show="processing" class="flw-btn-spinner">
            <svg class="flw-spinner" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="32" stroke-dashoffset="12"></circle>
            </svg>
            Verifying...
        </span>
    </button>

    {{-- Cancel Link --}}
    <button type="button" wire:click="cancel" class="flw-cancel-link">
        Cancel Payment
    </button>
</div>

@push('scripts')
<script>
function flutterwavePinInput(config) {
    return {
        encryptionKey: config.encryptionKey,
        pinLength: config.pinLength,
        pin: Array(config.pinLength).fill(''),
        processing: false,
        showKeypad: window.innerWidth <= 768,

        handlePinInput(event, index) {
            const value = event.target.value.replace(/\D/g, '');
            event.target.value = value;
            this.pin[index] = value;

            if (value && index < this.pinLength - 1) {
                this.$refs['pin' + (index + 1)].focus();
            }

            if (this.isPinComplete()) {
                this.submitPin();
            }
        },

        handleKeyDown(event, index) {
            if (event.key === 'Backspace' && !this.pin[index] && index > 0) {
                this.$refs['pin' + (index - 1)].focus();
            }
        },

        handlePaste(event) {
            event.preventDefault();
            const paste = (event.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/\D/g, '').slice(0, this.pinLength);
            
            digits.split('').forEach((digit, i) => {
                this.pin[i] = digit;
                this.$refs['pin' + i].value = digit;
            });

            const focusIndex = Math.min(digits.length, this.pinLength - 1);
            this.$refs['pin' + focusIndex].focus();

            if (this.isPinComplete()) {
                this.submitPin();
            }
        },

        handleKeypadPress(digit) {
            const emptyIndex = this.pin.findIndex(p => p === '');
            if (emptyIndex !== -1) {
                this.pin[emptyIndex] = digit;
                this.$refs['pin' + emptyIndex].value = digit;
                
                if (this.isPinComplete()) {
                    this.submitPin();
                }
            }
        },

        clearPin() {
            this.pin = Array(this.pinLength).fill('');
            for (let i = 0; i < this.pinLength; i++) {
                this.$refs['pin' + i].value = '';
            }
            this.$refs['pin0'].focus();
        },

        isPinComplete() {
            return this.pin.every(digit => digit !== '');
        },

        async submitPin() {
            if (!this.isPinComplete() || this.processing) return;

            this.processing = true;

            try {
                const encryptedData = await this.encryptPin();
                @this.call('submitPin', encryptedData);
            } catch (error) {
                console.error('PIN encryption error:', error);
                this.processing = false;
            }
        },

        async encryptPin() {
            const nonce = this.generateNonce(12);
            const pinValue = this.pin.join('');
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
            const encrypted = await crypto.subtle.encrypt(
                { name: 'AES-GCM', iv: iv },
                cryptoKey,
                encoder.encode(pinValue)
            );

            return {
                nonce: nonce,
                encrypted_pin: btoa(String.fromCharCode(...new Uint8Array(encrypted))),
            };
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
.flw-pin-input {
    max-width: 360px;
    margin: 0 auto;
    padding: 2rem;
    text-align: center;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.flw-pin-header {
    margin-bottom: 2rem;
}

.flw-pin-icon {
    width: 4rem;
    height: 4rem;
    margin: 0 auto 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #fef3c7 0%, #fbbf24 100%);
    border-radius: 50%;
    color: #92400e;
}

.flw-pin-icon svg {
    width: 100%;
    height: 100%;
}

.flw-pin-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.flw-pin-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}

.flw-pin-boxes {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.flw-pin-box {
    width: 3rem;
    height: 3.5rem;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 700;
    border: 2px solid #d1d5db;
    border-radius: 0.5rem;
    background: white;
    transition: border-color 0.15s, box-shadow 0.15s;
}

.flw-pin-box:focus {
    outline: none;
    border-color: #f5a623;
    box-shadow: 0 0 0 3px rgba(245, 166, 35, 0.2);
}

.flw-keypad {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    max-width: 240px;
    margin: 0 auto 1.5rem;
}

.flw-keypad-btn {
    height: 3.5rem;
    font-size: 1.25rem;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: white;
    cursor: pointer;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.flw-keypad-btn:hover:not(:disabled) {
    background: #f3f4f6;
}

.flw-keypad-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.flw-keypad-btn-submit {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.flw-keypad-btn-submit:hover:not(:disabled) {
    background: #059669;
}

.flw-keypad-icon {
    width: 1.5rem;
    height: 1.5rem;
}

.flw-cancel-link {
    display: block;
    margin-top: 1rem;
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

/* Reuse button styles from payment-form */
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
</style>
@endpush
