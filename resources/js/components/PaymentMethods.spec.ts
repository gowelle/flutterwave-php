import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import PaymentMethods from './PaymentMethods.vue';

// Mock fetch to prevent API calls
vi.stubGlobal('fetch', vi.fn().mockResolvedValue({
    ok: true,
    json: () => Promise.resolve({ methods: [] }),
}));

describe('PaymentMethods', () => {
    const defaultProps = {
        customerId: 'cust_123',
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders successfully', () => {
        const wrapper = mount(PaymentMethods, { props: defaultProps });
        expect(wrapper.exists()).toBe(true);
    });

    it('has payment methods container', () => {
        const wrapper = mount(PaymentMethods, { props: defaultProps });
        expect(wrapper.find('.flw-payment-methods').exists()).toBe(true);
    });

    it('shows loading state initially', () => {
        const wrapper = mount(PaymentMethods, { props: defaultProps });
        expect(wrapper.text().toLowerCase()).toContain('loading');
    });

    it('has header text', () => {
        const wrapper = mount(PaymentMethods, { props: defaultProps });
        expect(wrapper.text().toLowerCase()).toMatch(/payment.*method|saved.*card/);
    });

    it('accepts customerId prop', () => {
        const wrapper = mount(PaymentMethods, {
            props: { customerId: 'cust_456' }
        });
        expect(wrapper.exists()).toBe(true);
    });

    it('has title text', () => {
        const wrapper = mount(PaymentMethods, { props: defaultProps });
        expect(wrapper.text().toLowerCase()).toContain('payment');
    });
});
