<script setup lang="ts">
import { ref, onMounted } from 'vue';
import type { PaymentMethodsProps, SavedPaymentMethod, PaymentMethodType } from '../types';

const props = withDefaults(defineProps<PaymentMethodsProps>(), {
  currency: 'TZS',
});

const emit = defineEmits<{
  (e: 'select', methodId: string): void;
  (e: 'add-new'): void;
}>();

const methods = ref<SavedPaymentMethod[]>([]);
const selectedMethodId = ref<string | null>(null);
const loading = ref(true);
const error = ref('');

onMounted(() => loadMethods());

async function loadMethods() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetch(`/api/flutterwave/customers/${props.customerId}/payment-methods?currency=${props.currency}`, {
      headers: { 'Accept': 'application/json' },
    });
    if (!response.ok) throw new Error('Failed to load methods');
    const data = await response.json();
    methods.value = data.methods || [];
  } catch {
    error.value = 'Unable to load payment methods.';
    methods.value = [];
  } finally {
    loading.value = false;
  }
}

function selectMethod(methodId: string) {
  selectedMethodId.value = methodId;
  emit('select', methodId);
}

function getMethodDisplay(method: SavedPaymentMethod): string {
  switch (method.type) {
    case 'card':
      if (method.card) {
        const brand = method.card.brand.charAt(0).toUpperCase() + method.card.brand.slice(1);
        return `${brand} •••• ${method.card.last4}`;
      }
      return 'Card';
    case 'mobile_money':
      if (method.mobile_money) {
        return `${method.mobile_money.network} ${method.mobile_money.phone_number}`;
      }
      return 'Mobile Money';
    case 'bank_account':
      if (method.bank_account) {
        return `${method.bank_account.bank_name} •••• ${method.bank_account.account_number_last4}`;
      }
      return 'Bank Account';
    case 'ussd':
      if (method.ussd) {
        return `USSD - ${method.ussd.bank_name}`;
      }
      return 'USSD';
    case 'applepay':
      if (method.applepay) {
        return `Apple Pay •••• ${method.applepay.last4}`;
      }
      return 'Apple Pay';
    case 'googlepay':
      if (method.googlepay) {
        return `Google Pay •••• ${method.googlepay.last4}`;
      }
      return 'Google Pay';
    case 'opay':
      return 'OPay Wallet';
    default:
      return 'Payment Method';
  }
}

function getMethodSubtitle(method: SavedPaymentMethod): string {
  switch (method.type) {
    case 'card':
      if (method.card) {
        return `Expires ${method.card.exp_month}/${method.card.exp_year}`;
      }
      return '';
    case 'mobile_money':
      if (method.mobile_money) {
        return method.mobile_money.country_code;
      }
      return '';
    case 'bank_account':
      return 'Bank Transfer';
    case 'ussd':
      if (method.ussd) {
        return `Code: ${method.ussd.bank_code}`;
      }
      return '';
    case 'applepay':
      if (method.applepay) {
        return `Expires ${method.applepay.exp_month}/${method.applepay.exp_year}`;
      }
      return '';
    case 'googlepay':
      if (method.googlepay) {
        return `Expires ${method.googlepay.exp_month}/${method.googlepay.exp_year}`;
      }
      return '';
    case 'opay':
      return 'Digital Wallet';
    default:
      return '';
  }
}

function isExpired(method: SavedPaymentMethod): boolean {
  const cardDetails = method.card || method.applepay || method.googlepay;
  if (!cardDetails) return false;

  const expYear = parseInt(cardDetails.exp_year, 10);
  const expMonth = parseInt(cardDetails.exp_month, 10);
  const now = new Date();
  const currentYear = now.getFullYear() % 100;
  const currentMonth = now.getMonth() + 1;
  return expYear < currentYear || (expYear === currentYear && expMonth < currentMonth);
}

// Card brand icons
const cardIcons: Record<string, string> = {
  visa: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/visa.svg',
  mastercard: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/mastercard.svg',
  amex: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/amex.svg',
  verve: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/generic.svg',
};

// Get the appropriate icon URL for a payment method
function getMethodIcon(method: SavedPaymentMethod): string {
  switch (method.type) {
    case 'card':
      if (method.card) {
        return cardIcons[method.card.brand.toLowerCase()] || cardIcons.visa;
      }
      return cardIcons.visa;
    default:
      return ''; // Will use inline SVG icons instead
  }
}

// Check if method uses inline SVG icon
function usesInlineIcon(type: PaymentMethodType): boolean {
  return ['mobile_money', 'bank_account', 'ussd', 'opay', 'applepay', 'googlepay'].includes(type);
}

// Labels for method types
const methodTypeLabels: Record<PaymentMethodType, string> = {
  card: 'Card',
  mobile_money: 'Mobile Money',
  bank_account: 'Bank Account',
  ussd: 'USSD',
  applepay: 'Apple Pay',
  googlepay: 'Google Pay',
  opay: 'OPay',
};
</script>

<template>
  <div class="flw-payment-methods">
    <div v-if="loading" class="flw-loading">
      <svg class="flw-spinner" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" stroke-dasharray="32"
          stroke-dashoffset="12" />
      </svg>
      <span>Loading payment methods...</span>
    </div>

    <div v-else-if="error" class="flw-alert flw-alert-error">
      {{ error }}
    </div>

    <template v-else>
      <div class="flw-methods-list">
        <button v-for="method in methods" :key="method.id" type="button" class="flw-method-card"
          :class="{ 'flw-method-selected': selectedMethodId === method.id, 'flw-method-expired': isExpired(method) }"
          @click="selectMethod(method.id)">
          <div class="flw-method-icon">
            <!-- Card-based payment methods use external images -->
            <img v-if="!usesInlineIcon(method.type)" :src="getMethodIcon(method)" :alt="methodTypeLabels[method.type]">

            <!-- Mobile Money icon -->
            <svg v-else-if="method.type === 'mobile_money'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <rect x="5" y="2" width="14" height="20" rx="2" />
              <line x1="12" y1="18" x2="12" y2="18.01" stroke-linecap="round" />
            </svg>

            <!-- Bank Account icon -->
            <svg v-else-if="method.type === 'bank_account'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3" />
            </svg>

            <!-- USSD icon -->
            <svg v-else-if="method.type === 'ussd'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <rect x="2" y="4" width="20" height="16" rx="2" />
              <path
                d="M6 8h.01M10 8h.01M14 8h.01M18 8h.01M6 12h.01M10 12h.01M14 12h.01M18 12h.01M6 16h.01M10 16h.01M14 16h.01M18 16h.01"
                stroke-linecap="round" />
            </svg>

            <!-- OPay icon -->
            <svg v-else-if="method.type === 'opay'" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2">
              <rect x="2" y="5" width="20" height="14" rx="2" />
              <circle cx="12" cy="12" r="3" />
              <path d="M6 12h.01M18 12h.01" stroke-linecap="round" />
            </svg>

            <!-- Apple Pay icon -->
            <svg v-else-if="method.type === 'applepay'" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.53 4.08zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z" />
            </svg>

            <!-- Google Pay icon -->
            <svg v-else-if="method.type === 'googlepay'" viewBox="0 0 24 24" fill="currentColor">
              <path
                d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z" />
            </svg>
          </div>
          <div class="flw-method-info">
            <span class="flw-method-name">{{ getMethodDisplay(method) }}</span>
            <span class="flw-method-expiry">
              {{ getMethodSubtitle(method) }}
              <span v-if="isExpired(method)" class="flw-expired-badge">Expired</span>
            </span>
          </div>
          <svg v-if="selectedMethodId === method.id" class="flw-check-icon" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </button>

        <p v-if="methods.length === 0" class="flw-no-methods">
          No saved payment methods
        </p>
      </div>

      <button type="button" class="flw-add-new-btn" @click="emit('add-new')">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add New Payment Method
      </button>
    </template>
  </div>
</template>

<style scoped>
.flw-payment-methods {
  max-width: 400px;
}

.flw-loading {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  color: #6b7280;
}

.flw-spinner {
  width: 1.25rem;
  height: 1.25rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.flw-methods-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.flw-method-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 0.5rem;
  background: white;
  cursor: pointer;
  text-align: left;
  width: 100%;
}

.flw-method-card:hover {
  border-color: #d1d5db;
}

.flw-method-selected {
  border-color: #f5a623;
  background: #fffbeb;
}

.flw-method-expired {
  opacity: 0.6;
}

.flw-method-icon {
  width: 2.5rem;
}

.flw-method-icon img {
  width: 100%;
  height: auto;
}

.flw-method-info {
  flex: 1;
}

.flw-method-name {
  display: block;
  font-weight: 600;
  color: #111827;
}

.flw-method-expiry {
  display: block;
  font-size: 0.75rem;
  color: #6b7280;
}

.flw-expired-badge {
  background: #fef2f2;
  color: #dc2626;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.625rem;
  margin-left: 0.25rem;
}

.flw-check-icon {
  width: 1.5rem;
  height: 1.5rem;
  color: #f5a623;
}

.flw-add-new-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.75rem;
  border: 2px dashed #d1d5db;
  border-radius: 0.5rem;
  background: transparent;
  color: #6b7280;
  cursor: pointer;
  font-weight: 500;
}

.flw-add-new-btn:hover {
  border-color: #9ca3af;
  color: #374151;
}

.flw-add-new-btn svg {
  width: 1.25rem;
  height: 1.25rem;
}

.flw-no-methods {
  text-align: center;
  color: #6b7280;
  padding: 1rem;
}

.flw-alert-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.75rem;
  border-radius: 0.5rem;
}
</style>
