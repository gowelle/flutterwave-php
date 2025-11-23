<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Next action types for Flutterwave direct charge authorization flow.
 *
 * Represents the different types of actions a customer must complete
 * to authorize a payment transaction.
 */
enum NextActionType: string
{
    /**
     * Customer must enter their card PIN
     */
    case REQUIRES_PIN = 'requires_pin';

    /**
     * Customer must enter an OTP (soft token) sent to their phone/email
     */
    case REQUIRES_OTP = 'requires_otp';

    /**
     * Customer must provide additional verification fields (e.g., AVS billing address)
     */
    case REQUIRES_ADDITIONAL_FIELDS = 'requires_additional_fields';

    /**
     * Customer must be redirected to complete authentication (e.g., 3DS, mobile money approval)
     */
    case REDIRECT_URL = 'redirect_url';

    /**
     * Customer must complete offline action (e.g., USSD code, bank transfer)
     */
    case PAYMENT_INSTRUCTION = 'payment_instruction';

    /**
     * No further action required - payment processing
     */
    case NONE = 'none';

    /**
     * Create from Flutterwave API response
     */
    public static function fromApiResponse(?array $nextAction): self
    {
        if ($nextAction === null || empty($nextAction['type'])) {
            return self::NONE;
        }

        $type = $nextAction['type'];

        return self::tryFrom($type) ?? self::NONE;
    }

    /**
     * Check if this action type requires customer input
     */
    public function requiresCustomerInput(): bool
    {
        return match ($this) {
            self::REQUIRES_PIN,
            self::REQUIRES_OTP,
            self::REQUIRES_ADDITIONAL_FIELDS => true,
            self::REDIRECT_URL,
            self::PAYMENT_INSTRUCTION,
            self::NONE => false,
        };
    }

    /**
     * Check if this action type requires redirect
     */
    public function requiresRedirect(): bool
    {
        return $this === self::REDIRECT_URL;
    }

    /**
     * Check if this action type is asynchronous (payment completes outside the flow)
     */
    public function isAsynchronous(): bool
    {
        return match ($this) {
            self::REDIRECT_URL,
            self::PAYMENT_INSTRUCTION => true,
            default => false,
        };
    }
}
