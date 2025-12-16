<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Wallet;

/**
 * Wallet Statement Request Data Transfer Object
 *
 * @property-read string $currency Currency code (required)
 * @property-read int|null $size Page size (10-50, default 10)
 * @property-read string|null $from Start date (ISO 8601)
 * @property-read string|null $to End date (ISO 8601)
 * @property-read string|null $next Next page cursor
 * @property-read string|null $previous Previous page cursor
 */
final class WalletStatementRequest
{
    public function __construct(
        public readonly string $currency,
        public readonly ?int $size = null,
        public readonly ?string $from = null,
        public readonly ?string $to = null,
        public readonly ?string $next = null,
        public readonly ?string $previous = null,
    ) {}

    /**
     * Convert to array for query parameters
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $params = [
            'currency' => $this->currency,
        ];

        if ($this->size !== null) {
            $params['size'] = $this->size;
        }

        if ($this->from !== null) {
            $params['from'] = $this->from;
        }

        if ($this->to !== null) {
            $params['to'] = $this->to;
        }

        if ($this->next !== null) {
            $params['next'] = $this->next;
        }

        if ($this->previous !== null) {
            $params['previous'] = $this->previous;
        }

        return $params;
    }
}
