import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import PinInput from './PinInput.vue';

// Mock encryption utility
vi.mock('../utils/encryption', () => ({
    encryptPin: vi.fn().mockResolvedValue({
        nonce: 'test-nonce',
        encrypted_pin: 'encrypted-pin-value',
    }),
}));

describe('PinInput', () => {
    const defaultProps = {
        chargeId: 'dc_123',
        encryptionKey: 'test-encryption-key',
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders successfully', () => {
        const wrapper = mount(PinInput, { props: defaultProps });
        expect(wrapper.exists()).toBe(true);
    });

    it('renders the correct number of PIN input boxes', () => {
        const wrapper = mount(PinInput, {
            props: { ...defaultProps, pinLength: 4 }
        });
        const inputs = wrapper.findAll('input[type="password"]');
        expect(inputs.length).toBe(4);
    });

    it('uses default length of 4', () => {
        const wrapper = mount(PinInput, { props: defaultProps });
        const inputs = wrapper.findAll('input[type="password"]');
        expect(inputs.length).toBe(4);
    });

    it('masks PIN input values with password type', () => {
        const wrapper = mount(PinInput, { props: defaultProps });
        const inputs = wrapper.findAll('input[type="password"]');
        expect(inputs.length).toBeGreaterThan(0);
        expect(inputs.every(input => (input.element as HTMLInputElement).type === 'password')).toBe(true);
    });

    it('accepts only digits in input', async () => {
        const wrapper = mount(PinInput, { props: defaultProps });
        const inputs = wrapper.findAll('input[type="password"]');

        // Simulate number input
        await inputs[0].setValue('5');
        expect((inputs[0].element as HTMLInputElement).value).toBe('5');
    });

    it('displays title text', () => {
        const wrapper = mount(PinInput, { props: defaultProps });
        expect(wrapper.text()).toContain('Enter Your Card PIN');
    });

    it('has a confirm button', () => {
        const wrapper = mount(PinInput, { props: defaultProps });
        expect(wrapper.text()).toContain('Confirm PIN');
    });

    it('has a cancel button', () => {
        const wrapper = mount(PinInput, { props: defaultProps });
        expect(wrapper.text()).toContain('Cancel');
    });

    it('emits cancel event when cancel button is clicked', async () => {
        const wrapper = mount(PinInput, { props: defaultProps });

        const cancelBtn = wrapper.find('.flw-cancel-link');
        await cancelBtn.trigger('click');
        expect(wrapper.emitted()).toHaveProperty('cancel');
    });
});
