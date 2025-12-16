<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Transfer;

/**
 * Response DTO for transfer sender data.
 */
final readonly class SenderData
{
    /**
     * @param array<string, mixed>|null $name
     */
    public function __construct(
        public string $id,
        public ?array $name = null,
        public ?string $email = null,
        public ?string $phoneNumber = null,
        public ?string $country = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * Create from Flutterwave API response
     *
     * @param array<string, mixed> $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            name: isset($data['name']) && \is_array($data['name']) ? $data['name'] : null,
            email: $data['email'] ?? null,
            phoneNumber: $data['phone_number'] ?? null,
            country: $data['country'] ?? null,
            createdAt: $data['created_datetime'] ?? $data['created_at'] ?? null,
        );
    }

    /**
     * Get full name
     */
    public function getFullName(): string
    {
        if ($this->name === null) {
            return '';
        }

        return trim(($this->name['first'] ?? '') . ' ' . ($this->name['last'] ?? ''));
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
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phoneNumber,
            'country' => $this->country,
            'created_at' => $this->createdAt,
        ];
    }
}
