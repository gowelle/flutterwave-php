<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

use Gowelle\Flutterwave\Enums\TransferAction;
use Gowelle\Flutterwave\Enums\TransferStatus;
use Gowelle\Flutterwave\Enums\TransferType;

/**
 * Response DTO for transfer data from Flutterwave API.
 */
final readonly class TransferData
{
    /**
     * @param array<string, mixed>|null $recipient
     * @param array<string, mixed>|null $amount
     * @param array<string, mixed>|null $meta
     */
    public function __construct(
        public string $id,
        public TransferType $type,
        public TransferAction $action,
        public string $reference,
        public TransferStatus $status,
        public string $sourceCurrency,
        public string $destinationCurrency,
        public ?array $amount = null,
        public ?array $recipient = null,
        public ?string $narration = null,
        public ?array $meta = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * Create from Flutterwave API response
     *
     * @param array<string, mixed> $data
     */
    public static function fromApi(array $data): self
    {
        $type = TransferType::tryFrom($data['type'] ?? 'bank') ?? TransferType::BANK;
        $action = TransferAction::tryFrom($data['action'] ?? 'instant') ?? TransferAction::INSTANT;
        $status = TransferStatus::fromApiResponse($data['status'] ?? 'NEW');

        return new self(
            id: (string) $data['id'],
            type: $type,
            action: $action,
            reference: $data['reference'] ?? '',
            status: $status,
            sourceCurrency: $data['source_currency'] ?? '',
            destinationCurrency: $data['destination_currency'] ?? '',
            amount: isset($data['amount']) && \is_array($data['amount']) ? $data['amount'] : null,
            recipient: isset($data['recipient']) && \is_array($data['recipient']) ? $data['recipient'] : null,
            narration: $data['narration'] ?? null,
            meta: isset($data['meta']) && \is_array($data['meta']) ? $data['meta'] : null,
            createdAt: $data['created_datetime'] ?? $data['created_at'] ?? null,
        );
    }

    /**
     * Get the transfer amount value
     */
    public function getAmountValue(): ?float
    {
        return isset($this->amount['value']) ? (float) $this->amount['value'] : null;
    }

    /**
     * Check if transfer is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }

    /**
     * Check if transfer is pending
     */
    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    /**
     * Check if transfer is in terminal state
     */
    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'action' => $this->action->value,
            'reference' => $this->reference,
            'status' => $this->status->value,
            'source_currency' => $this->sourceCurrency,
            'destination_currency' => $this->destinationCurrency,
            'amount' => $this->amount,
            'recipient' => $this->recipient,
            'narration' => $this->narration,
            'meta' => $this->meta,
            'created_at' => $this->createdAt,
        ];
    }
}
