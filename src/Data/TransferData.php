<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

final readonly class TransferData
{
    public function __construct(
        public string $id,
        public string $reference,
        public float $amount,
        public string $currency,
        public string $status,
        public string $accountBank,
        public string $accountNumber,
        public string $beneficiaryName,
        public ?string $narration = null,
        public ?string $createdAt = null,
    ) {}

    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            reference: $data['reference'] ?? '',
            amount: (float) ($data['amount'] ?? 0.0),
            currency: $data['currency'] ?? '',
            status: $data['status'] ?? 'unknown',
            accountBank: $data['account_bank'] ?? '',
            accountNumber: $data['account_number'] ?? '',
            beneficiaryName: $data['beneficiary_name'] ?? '',
            narration: $data['narration'] ?? null,
            createdAt: $data['created_at'] ?? $data['created_datetime'] ?? null,
        );
    }

    public function isSuccessful(): bool
    {
        return \in_array(mb_strtolower($this->status), ['succeeded', 'successful', 'completed'], true);
    }

    public function isPending(): bool
    {
        return \in_array(mb_strtolower($this->status), ['pending', 'processing'], true);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'account_bank' => $this->accountBank,
            'account_number' => $this->accountNumber,
            'beneficiary_name' => $this->beneficiaryName,
            'narration' => $this->narration,
            'created_at' => $this->createdAt,
        ];
    }
}
