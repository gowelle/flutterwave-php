<script setup lang="ts">
import { ref, onMounted } from 'vue';
import type { PaymentMethodsProps, SavedPaymentMethod } from '../types';

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

function getCardDisplay(method: SavedPaymentMethod): string {
  const brand = method.card.brand.charAt(0).toUpperCase() + method.card.brand.slice(1);
  return `${brand} •••• ${method.card.last4}`;
}

function isExpired(method: SavedPaymentMethod): boolean {
  const expYear = parseInt(method.card.exp_year, 10);
  const expMonth = parseInt(method.card.exp_month, 10);
  const now = new Date();
  const currentYear = now.getFullYear() % 100;
  const currentMonth = now.getMonth() + 1;
  return expYear < currentYear || (expYear === currentYear && expMonth < currentMonth);
}

const cardIcons: Record<string, string> = {
  visa: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/visa.svg',
  mastercard: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/mastercard.svg',
  amex: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/amex.svg',
  verve: 'https://raw.githubusercontent.com/aaronfagan/svg-credit-card-payment-icons/main/flat-rounded/generic.svg',
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
            <img :src="cardIcons[method.card.brand.toLowerCase()] || cardIcons.visa" :alt="method.card.brand">
          </div>
          <div class="flw-method-info">
            <span class="flw-method-name">{{ getCardDisplay(method) }}</span>
            <span class="flw-method-expiry">
              Expires {{ method.card.exp_month }}/{{ method.card.exp_year }}
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
        Add New Card
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
