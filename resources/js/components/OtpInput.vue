<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import type { OtpInputProps, OtpInputLabels } from '../types';

const props = withDefaults(defineProps<OtpInputProps>(), {
  otpLength: 6,
  maskedPhone: '',
  labels: () => ({}),
});

const defaultLabels: OtpInputLabels = {
  enter_verification_code: 'Enter Verification Code',
  sent_code_message: "We've sent a :length-digit code to your phone",
  verify_code: 'Verify Code',
  verifying: 'Verifying...',
  didnt_receive_code: "Didn't receive the code?",
  resend_in: 'Resend in :seconds s',
  resend_code: 'Resend Code',
  cancel_payment: 'Cancel Payment',
};

const t = computed(() => ({ ...defaultLabels, ...props.labels }));

const emit = defineEmits<{
  (e: 'submit', otp: string): void;
  (e: 'resend'): void;
  (e: 'cancel'): void;
}>();

const otp = ref<string[]>(Array(props.otpLength).fill(''));
const processing = ref(false);
const error = ref('');
const countdown = ref(60);
const inputRefs = ref<HTMLInputElement[]>([]);
let countdownInterval: ReturnType<typeof setInterval> | null = null;

const isOtpComplete = computed(() => otp.value.every(d => d !== ''));
const canResend = computed(() => countdown.value <= 0);

onMounted(() => startCountdown());
onUnmounted(() => { if (countdownInterval) clearInterval(countdownInterval); });

function startCountdown() {
  countdown.value = 60;
  if (countdownInterval) clearInterval(countdownInterval);
  countdownInterval = setInterval(() => {
    if (countdown.value > 0) countdown.value--;
  }, 1000);
}

function handleInput(index: number, event: Event) {
  const target = event.target as HTMLInputElement;
  const value = target.value.replace(/\D/g, '');
  otp.value[index] = value;
  
  if (value && index < props.otpLength - 1) {
    inputRefs.value[index + 1]?.focus();
  }
  
  if (isOtpComplete.value) submitOtp();
}

function handleKeyDown(index: number, event: KeyboardEvent) {
  if (event.key === 'Backspace' && !otp.value[index] && index > 0) {
    inputRefs.value[index - 1]?.focus();
  }
}

function handlePaste(event: ClipboardEvent) {
  event.preventDefault();
  const paste = event.clipboardData?.getData('text') || '';
  const digits = paste.replace(/\D/g, '').slice(0, props.otpLength).split('');
  
  digits.forEach((digit, i) => {
    otp.value[i] = digit;
    if (inputRefs.value[i]) inputRefs.value[i].value = digit;
  });
  
  if (isOtpComplete.value) submitOtp();
}

function submitOtp() {
  if (!isOtpComplete.value || processing.value) return;
  processing.value = true;
  emit('submit', otp.value.join(''));
}

function resendOtp() {
  if (!canResend.value) return;
  emit('resend');
  startCountdown();
}
</script>

<template>
  <div class="flw-otp-input">
    <div class="flw-otp-header">
      <div class="flw-otp-icon">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
      </div>
      <h3 class="flw-otp-title">{{ t.enter_verification_code }}</h3>
      <p class="flw-otp-subtitle">
        {{ t.sent_code_message.replace(':length', String(otpLength)) }}
        <strong v-if="maskedPhone">{{ maskedPhone }}</strong>
      </p>
    </div>

    <div v-if="error" class="flw-alert flw-alert-error">{{ error }}</div>

    <div class="flw-otp-boxes">
      <input
        v-for="(_, i) in otpLength"
        :key="i"
        :ref="(el) => { if (el) inputRefs[i] = el as HTMLInputElement }"
        type="text"
        class="flw-otp-box"
        maxlength="1"
        inputmode="numeric"
        pattern="[0-9]*"
        autocomplete="one-time-code"
        :disabled="processing"
        @input="handleInput(i, $event)"
        @keydown="handleKeyDown(i, $event)"
        @paste="handlePaste"
      >
    </div>

    <button type="button" class="flw-btn flw-btn-primary flw-btn-full" :disabled="!isOtpComplete || processing" @click="submitOtp">
      <span v-if="!processing">{{ t.verify_code }}</span>
      <span v-else>{{ t.verifying }}</span>
    </button>

    <div class="flw-resend-section">
      <p>
        {{ t.didnt_receive_code }}
        <span v-if="countdown > 0" class="flw-countdown">{{ t.resend_in.replace(':seconds', String(countdown)) }}</span>
        <button v-else type="button" class="flw-resend-link" @click="resendOtp">{{ t.resend_code }}</button>
      </p>
    </div>

    <button type="button" class="flw-cancel-link" @click="emit('cancel')">{{ t.cancel_payment }}</button>
  </div>
</template>

<style scoped>
.flw-otp-input { max-width: 400px; margin: 0 auto; padding: 2rem; text-align: center; }
.flw-otp-header { margin-bottom: 2rem; }
.flw-otp-icon { width: 4rem; height: 4rem; margin: 0 auto 1rem; padding: 1rem; background: linear-gradient(135deg, #dbeafe, #3b82f6); border-radius: 50%; color: #1e40af; }
.flw-otp-icon svg { width: 100%; height: 100%; }
.flw-otp-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
.flw-otp-subtitle { font-size: 0.875rem; color: #6b7280; }
.flw-otp-boxes { display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 1.5rem; }
.flw-otp-box { width: 2.75rem; height: 3.25rem; text-align: center; font-size: 1.5rem; font-weight: 700; border: 2px solid #d1d5db; border-radius: 0.5rem; }
.flw-otp-box:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
.flw-resend-section { margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280; }
.flw-countdown { color: #9ca3af; }
.flw-countdown strong { color: #374151; }
.flw-resend-link { color: #3b82f6; background: none; border: none; cursor: pointer; font-weight: 600; text-decoration: underline; }
.flw-btn { padding: 0.875rem 1.5rem; border-radius: 0.5rem; font-size: 1rem; font-weight: 600; border: none; cursor: pointer; }
.flw-btn-primary { background: linear-gradient(135deg, #f5a623, #f77f00); color: white; }
.flw-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.flw-btn-full { width: 100%; }
.flw-cancel-link { display: block; margin-top: 1.5rem; font-size: 0.875rem; color: #6b7280; background: none; border: none; cursor: pointer; text-decoration: underline; }
.flw-alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.75rem; border-radius: 0.5rem; margin-bottom: 1rem; }
</style>
