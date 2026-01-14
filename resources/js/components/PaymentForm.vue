<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useFlutterwave } from '../composables/useFlutterwave';
import type { PaymentFormProps, DirectChargeResponse, PaymentFormLabels } from '../types';

const props = withDefaults(defineProps<PaymentFormProps>(), {
  currency: 'TZS',
  reference: () => `FLW-${Date.now()}`,
  redirectUrl: '',
  customer: () => ({}),
  meta: () => ({}),
  labels: () => ({}),
});

const defaultLabels: PaymentFormLabels = {
  payment_form: 'Payment Form',
  customer_details: 'Customer Details',
  email: 'Email',
  phone_number: 'Phone Number',
  first_name: 'First Name',
  last_name: 'Last Name',
  card_details: 'Card Details',
  card_number: 'Card Number',
  month: 'Month',
  year: 'Year',
  cvv: 'CVV',
  total: 'Total',
  pay: 'Pay',
  processing: 'Processing...',
  secured_by: 'Secured with 256-bit encryption',
  payment_failed: 'Payment failed',
  redirect_notice: 'You will be redirected to complete payment authorization.',
  continue_authorization: 'Continue to Authorization',
};

const t = computed(() => ({ ...defaultLabels, ...props.labels }));

const emit = defineEmits<{
  (e: 'success', charge: DirectChargeResponse): void;
  (e: 'failed', charge: DirectChargeResponse): void;
  (e: 'error', message: string): void;
  (e: 'requires-pin', chargeId: string): void;
  (e: 'requires-otp', chargeId: string): void;
  (e: 'requires-redirect', url: string, chargeId: string): void;
}>();

const {
  processing,
  error,
  charge: _charge,
  currentAction: _currentAction,
  cardNumber,
  expiryMonth,
  expiryYear,
  cvv,
  cardBrand,
  formattedCardNumber: _formattedCardNumber,
  isFormValid,
  createCharge,
  resetForm: _resetForm,
} = useFlutterwave({ encryptionKey: props.encryptionKey });

// Customer form fields
const email = ref(props.customer?.email || '');
const firstName = ref(props.customer?.name?.first || '');
const lastName = ref(props.customer?.name?.last || '');
const phoneNumber = ref(props.customer?.phone_number || '');

// Format card number as user types
watch(cardNumber, (val) => {
  const formatted = val.replace(/\s/g, '').replace(/(.{4})/g, '$1 ').trim();
  if (formatted !== val) cardNumber.value = formatted.slice(0, 19);
});

// Computed
const canSubmit = computed(() => {
  return (
    isFormValid.value &&
    email.value &&
    firstName.value &&
    lastName.value &&
    phoneNumber.value.length >= 10 &&
    !processing.value
  );
});

const cardBrandIcon = computed(() => {
  const icons: Record<string, string> = {
    visa: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/visa.svg',
    mastercard: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/mastercard.svg',
    amex: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/amex.svg',
    verve: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/generic.svg',
  };
  return icons[cardBrand.value] || '';
});

// Methods
async function handleSubmit() {
  if (!canSubmit.value) return;

  try {
    const result = await createCharge({
      amount: props.amount,
      currency: props.currency,
      reference: props.reference,
      redirect_url: props.redirectUrl,
      meta: props.meta,
      customer: {
        email: email.value,
        first_name: firstName.value,
        last_name: lastName.value,
        phone_number: phoneNumber.value,
      },
    });

    handleResult(result);
  } catch (_e) {
    emit('error', error.value || t.value.payment_failed);
  }
}

function handleResult(result: DirectChargeResponse) {
  if (result.status === 'succeeded') {
    emit('success', result);
    return;
  }

  if (result.status === 'requires_action' && result.next_action) {
    const action = result.next_action.type;

    if (action === 'requires_pin') {
      emit('requires-pin', result.id);
    } else if (action === 'requires_otp') {
      emit('requires-otp', result.id);
    } else if (action === 'redirect_url' && result.next_action.redirect_url) {
      emit('requires-redirect', result.next_action.redirect_url, result.id);
    }
    return;
  }

  if (['failed', 'cancelled', 'timeout'].includes(result.status)) {
    emit('failed', result);
  }
}
</script>

<template>
  <div class="flw-payment-form">
    <!-- Error Alert -->
    <div v-if="error" class="flw-alert flw-alert-error">
      <svg class="flw-alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span>{{ error }}</span>
    </div>

    <form class="flw-form" @submit.prevent="handleSubmit">
      <!-- Customer Details -->
      <div class="flw-form-section">
        <h3 class="flw-form-section-title">
          {{ t.customer_details }}
        </h3>
        <div class="flw-form-grid">
          <div class="flw-form-group">
            <label for="email" class="flw-label">{{ t.email }}</label>
            <input id="email" v-model="email" type="email" class="flw-input" placeholder="customer@example.com"
              required>
          </div>
          <div class="flw-form-group">
            <label for="phone" class="flw-label">{{ t.phone_number }}</label>
            <input id="phone" v-model="phoneNumber" type="tel" class="flw-input" placeholder="+255123456789" required>
          </div>
          <div class="flw-form-group">
            <label for="firstName" class="flw-label">{{ t.first_name }}</label>
            <input id="firstName" v-model="firstName" type="text" class="flw-input" placeholder="John" required>
          </div>
          <div class="flw-form-group">
            <label for="lastName" class="flw-label">{{ t.last_name }}</label>
            <input id="lastName" v-model="lastName" type="text" class="flw-input" placeholder="Doe" required>
          </div>
        </div>
      </div>

      <!-- Card Details -->
      <div class="flw-form-section">
        <h3 class="flw-form-section-title">
          {{ t.card_details }}
        </h3>
        <div class="flw-form-group">
          <label for="cardNumber" class="flw-label">{{ t.card_number }}</label>
          <div class="flw-input-with-icon">
            <input id="cardNumber" v-model="cardNumber" type="text" class="flw-input" placeholder="1234 5678 9012 3456"
              maxlength="19" autocomplete="cc-number" required>
            <img v-if="cardBrandIcon" :src="cardBrandIcon" :alt="cardBrand" class="flw-card-brand-icon">
          </div>
        </div>
        <div class="flw-form-grid flw-form-grid-3">
          <div class="flw-form-group">
            <label for="expiryMonth" class="flw-label">{{ t.month }}</label>
            <select id="expiryMonth" v-model="expiryMonth" class="flw-input" required>
              <option value="">
                MM
              </option>
              <option v-for="m in 12" :key="m" :value="String(m).padStart(2, '0')">
                {{ String(m).padStart(2, '0') }}
              </option>
            </select>
          </div>
          <div class="flw-form-group">
            <label for="expiryYear" class="flw-label">{{ t.year }}</label>
            <select id="expiryYear" v-model="expiryYear" class="flw-input" required>
              <option value="">
                YY
              </option>
              <option v-for="y in 16" :key="y" :value="String(new Date().getFullYear() + y - 1).slice(-2)">
                {{ new Date().getFullYear() + y - 1 }}
              </option>
            </select>
          </div>
          <div class="flw-form-group">
            <label for="cvv" class="flw-label">{{ t.cvv }}</label>
            <input id="cvv" v-model="cvv" type="password" class="flw-input" placeholder="***" maxlength="4"
              autocomplete="cc-csc" required>
          </div>
        </div>
      </div>

      <!-- Amount -->
      <div class="flw-payment-summary">
        <span class="flw-amount-label">{{ t.total }}</span>
        <span class="flw-amount-value">{{ currency }} {{ amount.toLocaleString() }}</span>
      </div>

      <!-- Submit -->
      <button type="submit" class="flw-btn flw-btn-primary flw-btn-full" :disabled="!canSubmit"
        :class="{ 'flw-btn-loading': processing }">
        <span v-if="!processing">{{ t.pay }} {{ currency }} {{ amount.toLocaleString() }}</span>
        <span v-else class="flw-btn-spinner">{{ t.processing }}</span>
      </button>

      <div class="flw-security-notice">
        <svg class="flw-security-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
        </svg>
        <span>{{ t.secured_by }}</span>
      </div>
    </form>
  </div>
</template>

<style scoped>
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
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
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
  transition: border-color 0.15s;
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

.flw-card-brand-icon {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  height: 1.5rem;
  width: auto;
}

.flw-payment-summary {
  display: flex;
  justify-content: space-between;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}

.flw-amount-label {
  color: #6b7280;
  font-weight: 500;
}

.flw-amount-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
}

.flw-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.875rem 1.5rem;
  border-radius: 0.5rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.15s;
}

.flw-btn-primary {
  background: linear-gradient(135deg, #f5a623, #f77f00);
  color: white;
}

.flw-btn-primary:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(245, 166, 35, 0.4);
}

.flw-btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.flw-btn-full {
  width: 100%;
}

.flw-btn-loading {
  pointer-events: none;
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

  .flw-form-grid,
  .flw-form-grid-3 {
    grid-template-columns: 1fr;
  }
}

@media (prefers-color-scheme: dark) {
  .flw-payment-form {
    color: #f3f4f6;
  }

  .flw-form-section-title {
    color: #d1d5db;
  }

  .flw-label {
    color: #d1d5db;
  }

  .flw-input {
    background: #374151;
    border-color: #4b5563;
    color: #f3f4f6;
  }

  .flw-input:focus {
    border-color: #f5a623;
  }

  .flw-payment-summary {
    background: #374151;
  }

  .flw-amount-label {
    color: #9ca3af;
  }

  .flw-amount-value {
    color: #f3f4f6;
  }

  .flw-security-notice {
    color: #9ca3af;
  }

  .flw-alert-error {
    background: #451a1a;
    border-color: #7f1d1d;
    color: #fca5a5;
  }
}
</style>
