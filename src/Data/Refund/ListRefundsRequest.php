<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Refund;

use DateTimeInterface;

/**
 * Request DTO for listing refunds with pagination and filtering via the Flutterwave API.
 *
 * @see https://developer.flutterwave.com/reference/refunds_list
 */
final readonly class ListRefundsRequest
{
    public function __construct(
        public int $page = 1,
        public int $size = 10,
        public ?DateTimeInterface $from = null,
        public ?DateTimeInterface $to = null,
    ) {}

    /**
     * Convert to query parameters
     *
     * @return array<string, mixed>
     */
    public function toQueryParams(): array
    {
        $params = [
            'page' => $this->page,
            'size' => $this->size,
        ];

        if ($this->from !== null) {
            $params['from'] = $this->from->format('c'); // ISO 8601 format
        }

        if ($this->to !== null) {
            $params['to'] = $this->to->format('c'); // ISO 8601 format
        }

        return $params;
    }
}
