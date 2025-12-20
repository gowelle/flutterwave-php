<?php

declare(strict_types=1);

use Gowelle\Flutterwave\Livewire\OtpInput;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123'])
        ->assertStatus(200);
});

it('can be mounted with charge context', function () {
    Livewire::test(OtpInput::class, [
        'chargeId' => 'dc_456',
        'otpLength' => 4,
        'maskedPhone' => '+255***789',
    ])
        ->assertSet('chargeId', 'dc_456')
        ->assertSet('otpLength', 4)
        ->assertSet('maskedPhone', '+255***789')
        ->assertSet('processing', false);
});

it('uses default otp length of 6', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123'])
        ->assertSet('otpLength', 6);
});

it('starts resend countdown on mount', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123'])
        ->assertSet('resendCountdown', 60)
        ->assertSet('canResend', false);
});

it('ticks countdown timer', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123'])
        ->call('tickCountdown')
        ->assertSet('resendCountdown', 59)
        ->call('tickCountdown')
        ->assertSet('resendCountdown', 58);
});

it('enables resend when countdown reaches zero', function () {
    $component = Livewire::test(OtpInput::class, ['chargeId' => 'dc_123'])
        ->set('resendCountdown', 1)
        ->call('tickCountdown');

    $component->assertSet('resendCountdown', 0)
        ->assertSet('canResend', true);
});

it('validates otp length on submission', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123', 'otpLength' => 6])
        ->set('otp', '123')
        ->call('submitOtp')
        ->assertSet('error', 'Please enter a valid 6-digit OTP.');
});

it('validates otp contains only digits', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123', 'otpLength' => 6])
        ->set('otp', '12ab56')
        ->call('submitOtp')
        ->assertSet('error', 'OTP must contain only numbers.');
});

it('dispatches submit-otp event with valid otp', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123', 'otpLength' => 6])
        ->set('otp', '123456')
        ->call('submitOtp')
        ->assertDispatched('submit-otp', otp: '123456');
});

it('dispatches resend-otp only when canResend is true', function () {
    // Cannot resend when countdown active
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123'])
        ->call('resendOtp')
        ->assertNotDispatched('resend-otp');

    // Can resend when countdown is zero
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123'])
        ->set('canResend', true)
        ->call('resendOtp')
        ->assertDispatched('resend-otp', chargeId: 'dc_123');
});

it('restarts countdown after resending otp', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_123'])
        ->set('canResend', true)
        ->set('resendCountdown', 0)
        ->call('resendOtp')
        ->assertSet('resendCountdown', 60)
        ->assertSet('canResend', false);
});

it('dispatches otp-cancelled event on cancel', function () {
    Livewire::test(OtpInput::class, ['chargeId' => 'dc_789'])
        ->call('cancel')
        ->assertDispatched('otp-cancelled', chargeId: 'dc_789');
});
