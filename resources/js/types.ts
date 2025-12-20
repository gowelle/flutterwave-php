/**
 * Flutterwave TypeScript Type Definitions
 * Matches PHP DTOs for type-safe integration
 */

// Enums
export type DirectChargeStatus = 'pending' | 'requires_action' | 'succeeded' | 'failed' | 'cancelled' | 'timeout';

export type NextActionType =
  | 'requires_pin'
  | 'requires_otp'
  | 'requires_additional_fields'
  | 'redirect_url'
  | 'payment_instruction'
  | 'none';

export type RefundReason = 'requested_by_customer' | 'duplicate' | 'fraudulent' | 'other';

export type RefundStatus = 'pending' | 'succeeded' | 'failed';

// Customer
export interface CustomerName {
  first: string;
  last: string;
  middle?: string;
}

export interface Customer {
  id?: string;
  email: string;
  name: CustomerName;
  phone_number: string;
}

// Card Data (for encryption)
export interface CardData {
  card_number: string;
  cvv: string;
  expiry_month: string;
  expiry_year: string;
}

// Encrypted Card Data
export interface EncryptedCardData {
  nonce: string;
  encrypted_card_number: string;
  encrypted_cvv: string;
  encrypted_expiry_month: string;
  encrypted_expiry_year: string;
}

// Payment Method
export interface PaymentMethod {
  type: 'card' | 'mobile_money' | 'bank_transfer';
  card?: EncryptedCardData;
}

export interface SavedPaymentMethod {
  id: string;
  type: 'card';
  card: {
    brand: string;
    last4: string;
    exp_month: string;
    exp_year: string;
  };
}

// Next Action
export interface NextAction {
  type: NextActionType;
  data?: Record<string, unknown>;
  redirect_url?: string;
}

// Direct Charge
export interface DirectChargeRequest {
  amount: number;
  currency: string;
  reference: string;
  redirect_url?: string;
  meta?: Record<string, unknown>;
  customer: Customer | { email: string; name: CustomerName; phone_number: string };
  payment_method: PaymentMethod;
}

export interface DirectChargeResponse {
  id: string;
  amount: number;
  currency: string;
  reference: string;
  status: DirectChargeStatus;
  next_action: NextAction;
  customer_id?: string;
  customer?: Customer;
  billing_details?: Record<string, unknown>;
  redirect_url?: string;
  payment_method_details?: Record<string, unknown>;
  issuer_response?: {
    code?: string;
    message?: string;
  };
  meta?: Record<string, unknown>;
  fees?: number;
  description?: string;
  disputed?: boolean;
  settled?: boolean;
  settlement_id?: string;
  created_at?: string;
}

// PIN Authorization
export interface PinAuthorizationData {
  nonce: string;
  encrypted_pin: string;
}

// OTP Authorization
export interface OtpAuthorizationData {
  code: string;
}

// AVS Authorization
export interface AvsAuthorizationData {
  line1: string;
  city: string;
  state: string;
  country: string;
  postal_code: string;
}

// Component Props
export interface PaymentFormLabels {
  payment_form: string;
  customer_details: string;
  email: string;
  phone_number: string;
  first_name: string;
  last_name: string;
  card_details: string;
  card_number: string;
  month: string;
  year: string;
  cvv: string;
  total: string;
  pay: string;
  processing: string;
  secured_by: string;
  payment_failed: string;
  redirect_notice: string;
  continue_authorization: string;
}

export interface PaymentFormProps {
  amount: number;
  currency?: string;
  reference?: string;
  redirectUrl?: string;
  customer?: Partial<Customer>;
  meta?: Record<string, unknown>;
  encryptionKey: string;
  labels?: Partial<PaymentFormLabels>;
}

export interface PinInputLabels {
  enter_card_pin: string;
  enter_pin_message: string;
  confirm_pin: string;
  verifying: string;
  cancel_payment: string;
}

export interface PinInputProps {
  chargeId: string;
  pinLength?: number;
  encryptionKey: string;
  labels?: Partial<PinInputLabels>;
}

export interface OtpInputLabels {
  enter_verification_code: string;
  sent_code_message: string;
  verify_code: string;
  verifying: string;
  didnt_receive_code: string;
  resend_in: string;
  resend_code: string;
  cancel_payment: string;
}

export interface OtpInputProps {
  chargeId: string;
  otpLength?: number;
  maskedPhone?: string;
  labels?: Partial<OtpInputLabels>;
}

export interface PaymentStatusLabels {
  processing_payment: string;
  awaiting_authorization: string;
  payment_successful: string;
  payment_failed: string;
  payment_cancelled: string;
  payment_timeout: string;
  processing: string;
  amount: string;
  reference: string;
  checking_status: string;
  continue: string;
  try_again: string;
}

export interface PaymentStatusProps {
  chargeId: string;
  startPolling?: boolean;
  pollInterval?: number;
  maxPolls?: number;
  labels?: Partial<PaymentStatusLabels>;
}

export interface PaymentMethodsProps {
  customerId: string;
  currency?: string;
}

// Component Events
export interface PaymentFormEmits {
  (e: 'payment-success', charge: DirectChargeResponse): void;
  (e: 'payment-failed', charge: DirectChargeResponse): void;
  (e: 'payment-error', error: string): void;
  (e: 'requires-pin', chargeId: string): void;
  (e: 'requires-otp', chargeId: string): void;
  (e: 'requires-redirect', url: string, chargeId: string): void;
  (e: 'requires-avs', chargeId: string, fields: Record<string, unknown>): void;
}

export interface PinInputEmits {
  (e: 'submit', data: PinAuthorizationData): void;
  (e: 'cancel'): void;
}

export interface OtpInputEmits {
  (e: 'submit', otp: string): void;
  (e: 'resend'): void;
  (e: 'cancel'): void;
}

export interface PaymentStatusEmits {
  (e: 'success', charge: DirectChargeResponse): void;
  (e: 'failed', charge: DirectChargeResponse): void;
  (e: 'timeout'): void;
}

export interface PaymentMethodsEmits {
  (e: 'select', methodId: string): void;
  (e: 'add-new'): void;
}
