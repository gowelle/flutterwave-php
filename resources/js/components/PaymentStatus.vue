<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import type { PaymentStatusProps, DirectChargeResponse, PaymentStatusLabels } from '../types';

const props = withDefaults(defineProps<PaymentStatusProps>(), {
  startPolling: false,
  pollInterval: 3000,
  maxPolls: 60,
  labels: () => ({}),
});

const defaultLabels: PaymentStatusLabels = {
  processing_payment: 'Processing your payment...',
  awaiting_authorization: 'Awaiting authorization...',
  payment_successful: 'Payment successful!',
  payment_failed: 'Payment failed.',
  payment_cancelled: 'Payment was cancelled.',
  payment_timeout: 'Payment authorization timed out.',
  processing: 'Processing...',
  amount: 'Amount',
  reference: 'Reference',
  checking_status: 'Checking payment status...',
  continue: 'Continue',
  try_again: 'Try Again',
};

const t = computed(() => ({ ...defaultLabels, ...props.labels }));

const emit = defineEmits<{
  (e: 'success', charge: DirectChargeResponse): void;
  (e: 'failed', charge: DirectChargeResponse): void;
  (e: 'timeout'): void;
}>();

const status = ref<string>('pending');
const statusMessage = ref(defaultLabels.processing_payment);
const charge = ref<DirectChargeResponse | null>(null);
const polling = ref(props.startPolling);
const pollCount = ref(0);
let pollInterval: ReturnType<typeof setInterval> | null = null;

const isTerminal = computed(() => ['succeeded', 'failed', 'cancelled', 'timeout'].includes(status.value));
const statusColor = computed(() => {
  if (status.value === 'succeeded') return 'text-green-500';
  if (['failed', 'cancelled', 'timeout'].includes(status.value)) return 'text-red-500';
  return 'text-yellow-500';
});

onMounted(() => {
  checkStatus();
  if (polling.value) startPolling();
});

onUnmounted(() => stopPolling());

watch(polling, (val) => {
  if (val) {
    startPolling();
  } else {
    stopPolling();
  }
});

async function checkStatus() {
  try {
    const response = await fetch(`/api/flutterwave/charges/${props.chargeId}/status`, {
      headers: { 'Accept': 'application/json' },
    });
    if (!response.ok) throw new Error('Failed to check status');
    const data = await response.json();
    updateFromCharge(data.charge);
  } catch {
    statusMessage.value = 'Unable to check payment status.';
  }
}

function updateFromCharge(chargeData: DirectChargeResponse) {
  charge.value = chargeData;
  status.value = chargeData.status;

  statusMessage.value = {
    pending: t.value.processing_payment,
    requires_action: t.value.awaiting_authorization,
    succeeded: t.value.payment_successful,
    failed: chargeData.issuer_response?.message || t.value.payment_failed,
    cancelled: t.value.payment_cancelled,
    timeout: t.value.payment_timeout,
  }[chargeData.status] || t.value.processing;

  if (chargeData.status === 'succeeded') {
    stopPolling();
    emit('success', chargeData);
  } else if (isTerminal.value) {
    stopPolling();
    emit('failed', chargeData);
  }
}

function startPolling() {
  if (pollInterval) return;
  pollInterval = setInterval(() => {
    if (isTerminal.value) { stopPolling(); return; }
    pollCount.value++;
    if (pollCount.value >= props.maxPolls) {
      stopPolling();
      emit('timeout');
      return;
    }
    checkStatus();
  }, props.pollInterval);
}

function stopPolling() {
  if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
  polling.value = false;
}
</script>

<template>
  <div class="flw-payment-status">
    <div class="flw-status-container">
      <div
        class="flw-status-icon"
        :class="statusColor"
      >
        <svg
          v-if="status === 'succeeded'"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <svg
          v-else-if="isTerminal"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <svg
          v-else
          class="flw-spin"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
          />
        </svg>
      </div>

      <h3 class="flw-status-title">
        {{ statusMessage }}
      </h3>

      <div
        v-if="charge && status === 'succeeded'"
        class="flw-payment-details"
      >
        <div class="flw-detail-row">
          <span>{{ t.amount }}</span>
          <span class="flw-detail-value">{{ charge.currency }} {{ charge.amount.toLocaleString() }}</span>
        </div>
        <div
          v-if="charge.reference"
          class="flw-detail-row"
        >
          <span>{{ t.reference }}</span>
          <span class="flw-detail-value">{{ charge.reference }}</span>
        </div>
      </div>

      <div
        v-if="polling"
        class="flw-polling-indicator"
      >
        <div class="flw-polling-dots">
          <span /><span /><span />
        </div>
        <p>{{ t.checking_status }}</p>
      </div>

      <div class="flw-status-actions">
        <slot
          name="actions"
          :status="status"
          :charge="charge"
        >
          <button
            v-if="status === 'succeeded'"
            class="flw-btn flw-btn-success flw-btn-full"
          >
            {{ t.continue }}
          </button>
          <button
            v-else-if="isTerminal"
            class="flw-btn flw-btn-primary flw-btn-full"
          >
            {{ t.try_again }}
          </button>
        </slot>
      </div>
    </div>
  </div>
</template>

<style scoped>
.flw-payment-status {
  max-width: 400px;
  margin: 0 auto;
  padding: 2rem;
  text-align: center;
}

.flw-status-container {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.flw-status-icon {
  width: 5rem;
  height: 5rem;
  margin: 0 auto 1.5rem;
}

.flw-status-icon svg {
  width: 100%;
  height: 100%;
}

.text-green-500 {
  color: #10b981;
}

.text-red-500 {
  color: #ef4444;
}

.text-yellow-500 {
  color: #f59e0b;
}

.flw-status-title {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.flw-payment-details {
  background: #f9fafb;
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
  text-align: left;
}

.flw-detail-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  color: #6b7280;
}

.flw-detail-value {
  font-weight: 600;
  color: #111827;
}

.flw-polling-indicator {
  margin-bottom: 1.5rem;
}

.flw-polling-dots {
  display: flex;
  justify-content: center;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
}

.flw-polling-dots span {
  width: 0.5rem;
  height: 0.5rem;
  background: #f5a623;
  border-radius: 50%;
  animation: bounce 1.4s infinite ease-in-out;
}

.flw-polling-dots span:nth-child(1) {
  animation-delay: -0.32s;
}

.flw-polling-dots span:nth-child(2) {
  animation-delay: -0.16s;
}

@keyframes bounce {

  0%,
  80%,
  100% {
    transform: scale(0);
  }

  40% {
    transform: scale(1);
  }
}

.flw-btn {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.flw-btn-primary {
  background: linear-gradient(135deg, #f5a623, #f77f00);
  color: white;
}

.flw-btn-success {
  background: #10b981;
  color: white;
}

.flw-btn-full {
  width: 100%;
}

.flw-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (prefers-color-scheme: dark) {
  .flw-status-container {
    background: #1f2937;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
  }

  .flw-status-title {
    color: #f3f4f6;
  }

  .flw-payment-details {
    background: #374151;
  }

  .flw-detail-row {
    color: #9ca3af;
  }

  .flw-detail-value {
    color: #f3f4f6;
  }

  .flw-polling-indicator p {
    color: #9ca3af;
  }
}
</style>
