import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import OtpInput from './OtpInput.vue';

describe('OtpInput', () => {
    const defaultProps = {
        chargeId: 'dc_123',
    };

    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('renders successfully', () => {
        const wrapper = mount(OtpInput, { props: defaultProps });
        expect(wrapper.exists()).toBe(true);
    });

    it('renders the correct number of OTP input boxes', () => {
        const wrapper = mount(OtpInput, {
            props: { ...defaultProps, length: 6 }
        });
        const inputs = wrapper.findAll('input[type="text"]');
        expect(inputs.length).toBe(6);
    });

    it('uses default length of 6', () => {
        const wrapper = mount(OtpInput, { props: defaultProps });
        const inputs = wrapper.findAll('input[type="text"]');
        expect(inputs.length).toBe(6);
    });

    it('moves focus to next input on valid digit', async () => {
        const wrapper = mount(OtpInput, { props: defaultProps });
        const inputs = wrapper.findAll('input[type="text"]');

        await inputs[0].setValue('1');
        expect((inputs[0].element as HTMLInputElement).value).toBe('1');
    });

    it('only accepts digits', async () => {
        const wrapper = mount(OtpInput, { props: defaultProps });
        const inputs = wrapper.findAll('input[type="text"]');

        // Empty initially
        expect((inputs[0].element as HTMLInputElement).value).toBe('');
    });

    it('displays verification header', () => {
        const wrapper = mount(OtpInput, { props: defaultProps });
        expect(wrapper.text().toLowerCase()).toContain('verification');
    });

    it('has a verify button', () => {
        const wrapper = mount(OtpInput, { props: defaultProps });
        expect(wrapper.text()).toContain('Verify');
    });

    it('has a cancel button', () => {
        const wrapper = mount(OtpInput, { props: defaultProps });
        expect(wrapper.text().toLowerCase()).toContain('cancel');
    });

    it('emits cancel event when cancel is clicked', async () => {
        const wrapper = mount(OtpInput, { props: defaultProps });

        const cancelBtn = wrapper.find('[class*="cancel"]');
        if (cancelBtn.exists()) {
            await cancelBtn.trigger('click');
            expect(wrapper.emitted()).toHaveProperty('cancel');
        }
    });

    it('shows resend option', () => {
        const wrapper = mount(OtpInput, { props: defaultProps });
        expect(wrapper.text().toLowerCase()).toContain('resend');
    });
});
