<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Wallet;

/**
 * Wallet Statement Data Transfer Object
 *
 * @property-read WalletStatementCursor $cursor Pagination cursor
 * @property-read array<int, array<string, mixed>> $transactions Transaction list
 */
final class WalletStatementData
{
    public function __construct(
        public readonly WalletStatementCursor $cursor,
        public readonly array $transactions,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            cursor: WalletStatementCursor::fromApiResponse($data['cursor'] ?? []),
            transactions: $data['transactions'] ?? [],
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'cursor' => $this->cursor->toArray(),
            'transactions' => $this->transactions,
        ];
    }
}

/**
 * Wallet Statement Cursor Data Transfer Object
 *
 * @property-read string|null $next Next page cursor
 * @property-read string|null $previous Previous page cursor
 * @property-read int $limit Page limit
 * @property-read int $total Total count
 * @property-read bool $hasMoreItems Whether there are more items
 */
final class WalletStatementCursor
{
    public function __construct(
        public readonly ?string $next,
        public readonly ?string $previous,
        public readonly int $limit,
        public readonly int $total,
        public readonly bool $hasMoreItems,
    ) {}

    /**
     * Create from API response
     */
    public static function fromApiResponse(array $data): self
    {
        return new self(
            next: $data['next'] ?? null,
            previous: $data['previous'] ?? null,
            limit: (int) ($data['limit'] ?? 10),
            total: (int) ($data['total'] ?? 0),
            hasMoreItems: (bool) ($data['has_more_items'] ?? false),
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'next' => $this->next,
            'previous' => $this->previous,
            'limit' => $this->limit,
            'total' => $this->total,
            'has_more_items' => $this->hasMoreItems,
        ];
    }
}
