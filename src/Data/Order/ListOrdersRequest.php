<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\Order;

use DateTimeInterface;

/**
 * Request DTO for listing orders with query parameters.
 *
 * @see https://developer.flutterwave.com/reference/orders_list
 */
final readonly class ListOrdersRequest
{
    /**
     * @param  OrderStatus|null  $status  Filter by order status
     * @param  DateTimeInterface|null  $from  Start date/time for filtering (ISO 8601)
     * @param  DateTimeInterface|null  $to  End date/time for filtering (ISO 8601)
     * @param  string|null  $customerId  Filter by customer ID
     * @param  string|null  $paymentMethodId  Filter by payment method ID
     * @param  int  $page  Page number (>=1, default: 1)
     * @param  int  $size  Number of results per page (10-50, default: 10)
     */
    public function __construct(
        public ?OrderStatus $status = null,
        public ?DateTimeInterface $from = null,
        public ?DateTimeInterface $to = null,
        public ?string $customerId = null,
        public ?string $paymentMethodId = null,
        public int $page = 1,
        public int $size = 10,
    ) {}

    /**
     * Convert to query parameters array.
     *
     * @return array<string, mixed>
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->status !== null) {
            $params['status'] = $this->status->value;
        }

        if ($this->from !== null) {
            $params['from'] = $this->from->format(DateTimeInterface::ATOM);
        }

        if ($this->to !== null) {
            $params['to'] = $this->to->format(DateTimeInterface::ATOM);
        }

        if ($this->customerId !== null) {
            $params['customer_id'] = $this->customerId;
        }

        if ($this->paymentMethodId !== null) {
            $params['payment_method_id'] = $this->paymentMethodId;
        }

        $params['page'] = max(1, $this->page);
        $params['size'] = min(50, max(10, $this->size));

        return $params;
    }
}
