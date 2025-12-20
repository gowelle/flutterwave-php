import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import PaymentStatus from './PaymentStatus.vue';

// Mock fetch to prevent API calls
vi.stubGlobal('fetch', vi.fn().mockRejectedValue(new Error('Network error')));

describe('PaymentStatus', () => {
    const defaultProps = {
        chargeId: 'dc_123',
    };

    beforeEach(() => {
        vi.clearAllMocks();
        vi.useFakeTimers();
    });

    it('renders successfully', () => {
        const wrapper = mount(PaymentStatus, { props: defaultProps });
        expect(wrapper.exists()).toBe(true);
    });

    it('displays the default processing message', () => {
        const wrapper = mount(PaymentStatus, { props: defaultProps });
        expect(wrapper.text()).toContain('Processing your payment...');
    });

    it('has status container', () => {
        const wrapper = mount(PaymentStatus, { props: defaultProps });
        expect(wrapper.find('.flw-status-container').exists()).toBe(true);
    });

    it('shows loading spinner by default', () => {
        const wrapper = mount(PaymentStatus, { props: defaultProps });
        // Should have spinning icon class
        expect(wrapper.find('.flw-spin').exists()).toBe(true);
    });

    it('accepts startPolling prop', () => {
        const wrapper = mount(PaymentStatus, {
            props: { ...defaultProps, startPolling: true }
        });
        expect(wrapper.exists()).toBe(true);
    });

    it('accepts pollInterval prop', () => {
        const wrapper = mount(PaymentStatus, {
            props: { ...defaultProps, pollInterval: 5000 }
        });
        expect(wrapper.exists()).toBe(true);
    });

    it('accepts maxPolls prop', () => {
        const wrapper = mount(PaymentStatus, {
            props: { ...defaultProps, maxPolls: 30 }
        });
        expect(wrapper.exists()).toBe(true);
    });
});
