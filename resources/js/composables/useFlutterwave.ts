import { ref, computed, type Ref } from 'vue';
import { encryptCardData, encryptPin, detectCardBrand, formatCardNumber } from '../utils/encryption';
import type {
    DirectChargeResponse,
    DirectChargeStatus,
    NextActionType,
    EncryptedCardData,
    PinAuthorizationData,
} from '../types';

export interface UseFlutterwaveOptions {
    encryptionKey: string;
    apiEndpoint?: string;
}

export interface CardFormState {
    cardNumber: Ref<string>;
    expiryMonth: Ref<string>;
    expiryYear: Ref<string>;
    cvv: Ref<string>;
    cardBrand: Ref<string>;
    formattedCardNumber: Ref<string>;
}

export function useFlutterwave(options: UseFlutterwaveOptions) {
    const { encryptionKey, apiEndpoint = '/api/flutterwave' } = options;

    // State
    const processing = ref(false);
    const error = ref<string | null>(null);
    const charge = ref<DirectChargeResponse | null>(null);
    const currentAction = ref<NextActionType | null>(null);

    // Card form state
    const cardNumber = ref('');
    const expiryMonth = ref('');
    const expiryYear = ref('');
    const cvv = ref('');

    // Computed
    const cardBrand = computed(() => detectCardBrand(cardNumber.value));
    const formattedCardNumber = computed(() => formatCardNumber(cardNumber.value));

    const isFormValid = computed(() => {
        const num = cardNumber.value.replace(/\s/g, '');
        return (
            num.length >= 15 &&
            expiryMonth.value !== '' &&
            expiryYear.value !== '' &&
            cvv.value.length >= 3
        );
    });

    const requiresAction = computed(() => {
        if (!charge.value) return false;
        return charge.value.status === 'requires_action';
    });

    const isSuccessful = computed(() => charge.value?.status === 'succeeded');
    const isFailed = computed(() => ['failed', 'cancelled', 'timeout'].includes(charge.value?.status || ''));

    // Methods
    async function getEncryptedCardData(): Promise<EncryptedCardData> {
        return encryptCardData(
            encryptionKey,
            cardNumber.value.replace(/\s/g, ''),
            cvv.value,
            expiryMonth.value,
            expiryYear.value
        );
    }

    async function getEncryptedPin(pin: string): Promise<PinAuthorizationData> {
        return encryptPin(encryptionKey, pin);
    }

    async function createCharge(payload: {
        amount: number;
        currency: string;
        reference: string;
        customer: { email: string; first_name: string; last_name: string; phone_number: string };
        redirect_url?: string;
        meta?: Record<string, unknown>;
    }): Promise<DirectChargeResponse> {
        processing.value = true;
        error.value = null;

        try {
            const encryptedCard = await getEncryptedCardData();

            const response = await fetch(`${apiEndpoint}/charges`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                body: JSON.stringify({
                    amount: payload.amount,
                    currency: payload.currency,
                    reference: payload.reference,
                    redirect_url: payload.redirect_url,
                    meta: payload.meta,
                    customer: {
                        email: payload.customer.email,
                        name: {
                            first: payload.customer.first_name,
                            last: payload.customer.last_name,
                        },
                        phone_number: payload.customer.phone_number,
                    },
                    payment_method: {
                        type: 'card',
                        card: encryptedCard,
                    },
                }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Payment failed');
            }

            const data = await response.json();
            charge.value = data.charge;
            handleChargeResult(data.charge);

            return data.charge;
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'An unexpected error occurred';
            throw e;
        } finally {
            processing.value = false;
        }
    }

    async function submitPin(chargeId: string, pin: string): Promise<DirectChargeResponse> {
        processing.value = true;
        error.value = null;

        try {
            const encryptedPin = await getEncryptedPin(pin);

            const response = await fetch(`${apiEndpoint}/charges/${chargeId}/authorize`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                body: JSON.stringify({
                    type: 'pin',
                    ...encryptedPin,
                }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'PIN verification failed');
            }

            const data = await response.json();
            charge.value = data.charge;
            handleChargeResult(data.charge);

            return data.charge;
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'PIN verification failed';
            throw e;
        } finally {
            processing.value = false;
        }
    }

    async function submitOtp(chargeId: string, otp: string): Promise<DirectChargeResponse> {
        processing.value = true;
        error.value = null;

        try {
            const response = await fetch(`${apiEndpoint}/charges/${chargeId}/authorize`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCSRFToken(),
                },
                body: JSON.stringify({ type: 'otp', code: otp }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'OTP verification failed');
            }

            const data = await response.json();
            charge.value = data.charge;
            handleChargeResult(data.charge);

            return data.charge;
        } catch (e) {
            error.value = e instanceof Error ? e.message : 'OTP verification failed';
            throw e;
        } finally {
            processing.value = false;
        }
    }

    async function checkStatus(chargeId: string): Promise<DirectChargeResponse> {
        const response = await fetch(`${apiEndpoint}/charges/${chargeId}/status`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRFToken() },
        });

        if (!response.ok) throw new Error('Failed to check status');

        const data = await response.json();
        charge.value = data.charge;
        return data.charge;
    }

    function handleChargeResult(chargeData: DirectChargeResponse) {
        if (chargeData.status === 'requires_action' && chargeData.next_action) {
            currentAction.value = chargeData.next_action.type;
        } else {
            currentAction.value = null;
        }
    }

    function resetForm() {
        cardNumber.value = '';
        expiryMonth.value = '';
        expiryYear.value = '';
        cvv.value = '';
        charge.value = null;
        error.value = null;
        currentAction.value = null;
    }

    function getCSRFToken(): string {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta?.getAttribute('content') || '';
    }

    return {
        // State
        processing,
        error,
        charge,
        currentAction,
        // Card form
        cardNumber,
        expiryMonth,
        expiryYear,
        cvv,
        cardBrand,
        formattedCardNumber,
        isFormValid,
        // Computed
        requiresAction,
        isSuccessful,
        isFailed,
        // Methods
        getEncryptedCardData,
        getEncryptedPin,
        createCharge,
        submitPin,
        submitOtp,
        checkStatus,
        resetForm,
    };
}
