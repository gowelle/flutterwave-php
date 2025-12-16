<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Webhook event types for Flutterwave webhooks.
 *
 * Represents the different types of webhook events that can be received
 * from Flutterwave's webhook system.
 */
enum WebhookEventType: string
{
    /**
     * Charge completed event
     */
    case CHARGE_COMPLETED = 'charge.completed';

    /**
     * Charge failed event
     */
    case CHARGE_FAILED = 'charge.failed';

    /**
     * Charge successful event
     */
    case CHARGE_SUCCESSFUL = 'charge.successful';

    /**
     * Payment completed event
     */
    case PAYMENT_COMPLETED = 'payment.completed';

    /**
     * Payment failed event
     */
    case PAYMENT_FAILED = 'payment.failed';

    /**
     * Payment successful event
     */
    case PAYMENT_SUCCESSFUL = 'payment.successful';

    /**
     * Transfer completed event
     */
    case TRANSFER_COMPLETED = 'transfer.completed';

    /**
     * Create from webhook event string
     */
    public static function fromString(?string $event): ?self
    {
        if ($event === null) {
            return null;
        }

        return self::tryFrom($event);
    }

    /**
     * Check if event is payment-related (charge.* or payment.*)
     */
    public function isPaymentEvent(): bool
    {
        return $this->isChargeEvent() || str_starts_with($this->value, 'payment.');
    }

    /**
     * Check if event is transfer-related (transfer.*)
     */
    public function isTransferEvent(): bool
    {
        return str_starts_with($this->value, 'transfer.');
    }

    /**
     * Check if event is charge-related (charge.*)
     */
    public function isChargeEvent(): bool
    {
        return str_starts_with($this->value, 'charge.');
    }

    /**
     * Check if event indicates success
     */
    public function isSuccessful(): bool
    {
        return match ($this) {
            self::CHARGE_SUCCESSFUL,
            self::PAYMENT_SUCCESSFUL => true,
            default => false,
        };
    }
}
