<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Events;

use Gowelle\Flutterwave\Enums\WebhookEventType;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FlutterwaveWebhookReceived
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * The webhook payload.
     */
    public array $payload;

    /**
     * Create a new event instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the event type.
     */
    public function getEventType(): ?string
    {
        return $this->payload['event'] ?? null;
    }

    /**
     * Get the event type as an enum.
     */
    public function getEventTypeEnum(): ?WebhookEventType
    {
        return WebhookEventType::fromString($this->getEventType());
    }

    /**
     * Get transaction data.
     */
    public function getTransactionData(): ?array
    {
        return $this->payload['data'] ?? null;
    }

    /**
     * Get transaction ID.
     */
    public function getTransactionId(): ?string
    {
        return $this->payload['data']['id'] ?? $this->payload['data']['reference'] ?? null;
    }

    /**
     * Get transaction status.
     */
    public function getStatus(): ?string
    {
        return $this->payload['data']['status'] ?? null;
    }

    /**
     * Check if this is a payment event.
     */
    public function isPaymentEvent(): bool
    {
        $eventType = $this->getEventTypeEnum();

        return $eventType !== null && $eventType->isPaymentEvent();
    }

    /**
     * Check if this is a transfer event.
     */
    public function isTransferEvent(): bool
    {
        $eventType = $this->getEventTypeEnum();

        return $eventType !== null && $eventType->isTransferEvent();
    }

    /**
     * Check if transaction was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->getStatus() === 'successful';
    }
}
