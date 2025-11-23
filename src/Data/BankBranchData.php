<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

/**
 * Bank Branch Data Transfer Object
 *
 * @property-read string $id Bank branch ID
 * @property-read string $code Bank code
 * @property-read string $name Bank name
 * @property-read string|null $swiftCode SWIFT code
 * @property-read string|null $bic Bank Identification Code (BIC)
 */
final class BankBranchData
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $swiftCode = null,
        public readonly ?string $bic = null,
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
            swiftCode: $data['swift_code'] ?? null,
            bic: $data['bic'] ?? null,
        );
    }

    /**
     * Create collection from API response
     *
     * @return BankBranchData[]
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
            'swift_code' => $this->swiftCode,
            'bic' => $this->bic,
        ];
    }
}
