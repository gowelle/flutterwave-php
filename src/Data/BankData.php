<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

/**
 * Bank Data Transfer Object
 *
 * @property-read string $id Bank ID
 * @property-read string $code Bank code
 * @property-read string $name Bank name
 */
final class BankData
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            code: $data['code'] ?? '',
            name: $data['name'] ?? '',
        );
    }

    /**
     * Create collection from API response
     *
     * @return BankData[]
     */
    public static function collection(array $items): array
    {
        return array_map(fn (array $item) => self::fromApiResponse($item), $items);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}
