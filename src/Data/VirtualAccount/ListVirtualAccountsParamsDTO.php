<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\VirtualAccount;

/**
 * List Virtual Accounts Query Parameters DTO
 *
 * Represents query parameters for filtering and paginating virtual accounts.
 */
final class ListVirtualAccountsParamsDTO
{
    /**
     * @param  ?string  $from  Start date in ISO 8601 format (Y-m-d\TH:i:s\Z)
     * @param  ?string  $to  End date in ISO 8601 format (Y-m-d\TH:i:s\Z)
     * @param  ?int  $page  Page number (min: 1)
     * @param  ?int  $size  Page size (min: 10, max: 50)
     * @param  ?string  $reference  Filter by transaction reference
     */
    public function __construct(
        public ?string $from = null,
        public ?string $to = null,
        public ?int $page = null,
        public ?int $size = null,
        public ?string $reference = null,
    ) {}

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            from: $data['from'] ?? null,
            to: $data['to'] ?? null,
            page: isset($data['page']) ? (int) $data['page'] : null,
            size: isset($data['size']) ? (int) $data['size'] : null,
            reference: $data['reference'] ?? null,
        );
    }

    /**
     * Convert to API query parameters array
     */
    public function toArray(): array
    {
        $params = [];

        if ($this->from !== null) {
            $params['from'] = $this->from;
        }

        if ($this->to !== null) {
            $params['to'] = $this->to;
        }

        if ($this->page !== null) {
            $params['page'] = $this->page;
        }

        if ($this->size !== null) {
            $params['size'] = $this->size;
        }

        if ($this->reference !== null) {
            $params['reference'] = $this->reference;
        }

        return $params;
    }
}
