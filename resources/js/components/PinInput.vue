<script setup lang="ts">
import { ref, computed } from 'vue';
import { encryptPin } from '../utils/encryption';
import type { PinInputProps, PinInputLabels } from '../types';

const props = withDefaults(defineProps<PinInputProps>(), {
  pinLength: 4,
  labels: () => ({}),
});

const defaultLabels: PinInputLabels = {
  enter_card_pin: 'Enter Your Card PIN',
  enter_pin_message: 'Please enter your :length-digit card PIN',
  confirm_pin: 'Confirm PIN',
  verifying: 'Verifying...',
  cancel_payment: 'Cancel Payment',
};

const t = computed(() => ({ ...defaultLabels, ...props.labels }));

const emit = defineEmits<{
  (e: 'submit', data: { nonce: string; encrypted_pin: string }): void;
  (e: 'cancel'): void;
}>();

const pin = ref<string[]>(Array(props.pinLength).fill(''));
const processing = ref(false);
const error = ref('');
const inputRefs = ref<HTMLInputElement[]>([]);

const isPinComplete = computed(() => pin.value.every(d => d !== ''));

function handleInput(index: number, event: Event) {
  const target = event.target as HTMLInputElement;
  const value = target.value.replace(/\D/g, '');
  pin.value[index] = value;

  if (value && index < props.pinLength - 1) {
    inputRefs.value[index + 1]?.focus();
  }

  if (isPinComplete.value) submitPin();
}

function handleKeyDown(index: number, event: KeyboardEvent) {
  if (event.key === 'Backspace' && !pin.value[index] && index > 0) {
    inputRefs.value[index - 1]?.focus();
  }
}

function handlePaste(event: ClipboardEvent) {
  event.preventDefault();
  const paste = event.clipboardData?.getData('text') || '';
  const digits = paste.replace(/\D/g, '').slice(0, props.pinLength).split('');

  digits.forEach((digit, i) => {
    pin.value[i] = digit;
    if (inputRefs.value[i]) inputRefs.value[i].value = digit;
  });

  inputRefs.value[Math.min(digits.length, props.pinLength - 1)]?.focus();
  if (isPinComplete.value) submitPin();
}

async function submitPin() {
  if (!isPinComplete.value || processing.value) return;

  processing.value = true;
  error.value = '';

  try {
    const pinValue = pin.value.join('');
    const encrypted = await encryptPin(props.encryptionKey, pinValue);
    emit('submit', encrypted);
  } catch (e) {
    error.value = 'PIN encryption failed';
    processing.value = false;
  }
}

function clearPin() {
  pin.value = Array(props.pinLength).fill('');
  inputRefs.value.forEach(input => { if (input) input.value = ''; });
  inputRefs.value[0]?.focus();
}
</script>

<template>
  <div class="flw-pin-input">
    <div class="flw-pin-header">
      <div class="flw-pin-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
      </div>
      <h3 class="flw-pin-title">{{ t.enter_card_pin }}</h3>
      <p class="flw-pin-subtitle">{{ t.enter_pin_message.replace(':length', String(pinLength)) }}</p>
    </div>

    <div v-if="error" class="flw-alert flw-alert-error">{{ error }}</div>

    <div class="flw-pin-boxes">
      <input v-for="(_, i) in pinLength" :key="i" :ref="(el) => { if (el) inputRefs[i] = el as HTMLInputElement }"
        type="password" class="flw-pin-box" maxlength="1" inputmode="numeric" pattern="[0-9]*" :disabled="processing"
        @input="handleInput(i, $event)" @keydown="handleKeyDown(i, $event)" @paste="handlePaste">
    </div>

    <button type="button" class="flw-btn flw-btn-primary flw-btn-full" :disabled="!isPinComplete || processing"
      @click="submitPin">
      <span v-if="!processing">{{ t.confirm_pin }}</span>
      <span v-else>{{ t.verifying }}</span>
    </button>

    <button type="button" class="flw-cancel-link" @click="emit('cancel')">{{ t.cancel_payment }}</button>
  </div>
</template>

<style scoped>
.flw-pin-input {
  max-width: 360px;
  margin: 0 auto;
  padding: 2rem;
  text-align: center;
}

.flw-pin-header {
  margin-bottom: 2rem;
}

.flw-pin-icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1rem;
  padding: 1rem;
  background: linear-gradient(135deg, #fef3c7, #fbbf24);
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
}

.flw-pin-box:focus {
  outline: none;
  border-color: #f5a623;
  box-shadow: 0 0 0 3px rgba(245, 166, 35, 0.2);
}

.flw-btn {
  padding: 0.875rem 1.5rem;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.flw-btn-primary {
  background: linear-gradient(135deg, #f5a623, #f77f00);
  color: white;
}

.flw-btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.flw-btn-full {
  width: 100%;
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

.flw-alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.75rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}
</style>
