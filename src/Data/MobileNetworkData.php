<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use Illuminate\Support\Collection;

/**
 * Mobile Network Data Transfer Object
 *
 * @property-read string $id Mobile network ID
 * @property-read string $network Mobile network identifier
 * @property-read string $name Mobile network operator name
 */
final class MobileNetworkData
{
    public function __construct(
        public readonly string $id,
        public readonly string $network,
        public readonly string $name,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            id: $data['id'] ?? '',
            network: $data['network'] ?? '',
            name: $data['name'] ?? '',
        );
    }

    /**
     * Create collection from API response
     *
     * @return Collection<MobileNetworkData>
     */
    public static function collection(array $items): Collection
    {
        if (empty($items) && \count($items) === 0) {
            return collect();
        }

        $networksCollection = collect($items);

        return $networksCollection->unique('network');
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'network' => $this->network,
            'name' => $this->name,
        ];
    }
}
