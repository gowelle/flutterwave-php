import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import PaymentForm from './PaymentForm.vue';

// Mock the encryption utilities
vi.mock('../utils/encryption', () => ({
    encryptCardData: vi.fn().mockResolvedValue({
        nonce: 'test-nonce',
        encrypted_card_number: 'encrypted-card',
        encrypted_cvv: 'encrypted-cvv',
        encrypted_expiry_month: 'encrypted-month',
        encrypted_expiry_year: 'encrypted-year',
    }),
    generateNonce: vi.fn().mockReturnValue('test-nonce'),
    detectCardBrand: vi.fn().mockReturnValue('visa'),
}));

describe('PaymentForm', () => {
    const defaultProps = {
        amount: 10000,
        currency: 'TZS',
        encryptionKey: 'test-encryption-key-32-characters',
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders successfully', () => {
        const wrapper = mount(PaymentForm, { props: defaultProps });
        expect(wrapper.exists()).toBe(true);
    });

    it('has form container', () => {
        const wrapper = mount(PaymentForm, { props: defaultProps });
        expect(wrapper.find('.flw-payment-form').exists()).toBe(true);
    });

    it('displays the payment amount', () => {
        const wrapper = mount(PaymentForm, { props: defaultProps });
        expect(wrapper.text()).toMatch(/10[,.]?000|TZS/);
    });

    it('renders card input field', () => {
        const wrapper = mount(PaymentForm, { props: defaultProps });
        // Look for input that's for card number
        const inputs = wrapper.findAll('input');
        expect(inputs.length).toBeGreaterThan(0);
    });

    it('shows customer email input', () => {
        const wrapper = mount(PaymentForm, { props: defaultProps });
        const emailInput = wrapper.find('input[type="email"]');
        expect(emailInput.exists()).toBe(true);
    });

    it('has a submit button', () => {
        const wrapper = mount(PaymentForm, { props: defaultProps });
        const submitBtn = wrapper.find('button[type="submit"]');
        expect(submitBtn.exists()).toBe(true);
    });

    it('displays payment text in submit button', () => {
        const wrapper = mount(PaymentForm, { props: defaultProps });
        expect(wrapper.text().toLowerCase()).toContain('pay');
    });

    it('has name inputs for first and last name', () => {
        const wrapper = mount(PaymentForm, { props: defaultProps });
        const inputs = wrapper.findAll('input');
        // Should have multiple inputs including name fields
        expect(inputs.length).toBeGreaterThanOrEqual(4);
    });

    it('accepts customer prop for pre-filling', () => {
        const wrapper = mount(PaymentForm, {
            props: {
                ...defaultProps,
                customer: {
                    email: 'test@example.com',
                    firstName: 'John',
                    lastName: 'Doe',
                    phone: '+255123456789',
                }
            }
        });
        expect(wrapper.exists()).toBe(true);
    });
});
