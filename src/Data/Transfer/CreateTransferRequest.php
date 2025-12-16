<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

use Gowelle\Flutterwave\Enums\TransferAction;

/**
 * Request DTO for creating transfers via general flow (uses recipient_id and sender_id).
 */
final readonly class CreateTransferRequest
{
    public function __construct(
        public TransferAction $action,
        public string $recipientId,
        public string $senderId,
        public string $reference,
        public ?string $narration = null,
        public ?string $scenarioKey = null,
    ) {}

    /**
     * Convert to API payload
     *
     * @return array<string, mixed>
     */
    public function toApiPayload(): array
    {
        $payload = array_filter([
            'action' => $this->action->value,
            'recipient_id' => $this->recipientId,
            'sender_id' => $this->senderId,
            'reference' => $this->reference,
            'narration' => $this->narration,
        ], fn ($value) => $value !== null);

        // Include scenario_key in payload if specified for testing
        if ($this->scenarioKey !== null) {
            $payload['scenario_key'] = $this->scenarioKey;
        }

        return $payload;
    }
}
