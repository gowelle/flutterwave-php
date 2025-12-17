<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use Gowelle\Flutterwave\Data\Order\OrderStatus;

/**
 * Data Transfer Object for Order API responses.
 *
 * @see https://developer.flutterwave.com/reference/orders_get
 */
final readonly class OrderData
{
    /**
     * @param  string  $id  Order ID
     * @param  float  $amount  Order amount
     * @param  string  $currency  ISO 4217 currency code
     * @param  string  $reference  Unique transaction reference
     * @param  string  $customerId  Customer ID
     * @param  string|null  $status  Order status
     * @param  array<int, array{type: string, amount: float}>|null  $fees  Associated fees
     * @param  array<string, mixed>|null  $billingDetails  Billing details
     * @param  array<string, mixed>|null  $meta  Metadata
     * @param  array<string, mixed>|null  $nextAction  Next action details (redirect, etc.)
     * @param  array<string, mixed>|null  $paymentMethodDetails  Payment method info
     * @param  string|null  $redirectUrl  Redirect URL
     * @param  array<string, mixed>|null  $processorResponse  Processor response
     * @param  string|null  $description  Order description
     * @param  string|null  $createdDatetime  Creation timestamp
     */
    public function __construct(
        public string $id,
        public float $amount,
        public string $currency,
        public string $reference,
        public string $customerId,
        public ?string $status = null,
        public ?array $fees = null,
        public ?array $billingDetails = null,
        public ?array $meta = null,
        public ?array $nextAction = null,
        public ?array $paymentMethodDetails = null,
        public ?string $redirectUrl = null,
        public ?array $processorResponse = null,
        public ?string $description = null,
        public ?string $createdDatetime = null,
    ) {}

    /**
     * Create from API response.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            amount: (float) ($data['amount'] ?? 0),
            currency: (string) ($data['currency'] ?? ''),
            reference: (string) ($data['reference'] ?? ''),
            customerId: (string) ($data['customer_id'] ?? ''),
            status: $data['status'] ?? null,
            fees: isset($data['fees']) && \is_array($data['fees']) ? $data['fees'] : null,
            billingDetails: isset($data['billing_details']) && \is_array($data['billing_details']) ? $data['billing_details'] : null,
            meta: isset($data['meta']) && \is_array($data['meta']) ? $data['meta'] : null,
            nextAction: isset($data['next_action']) && \is_array($data['next_action']) ? $data['next_action'] : null,
            paymentMethodDetails: isset($data['payment_method_details']) && \is_array($data['payment_method_details']) ? $data['payment_method_details'] : null,
            redirectUrl: $data['redirect_url'] ?? null,
            processorResponse: isset($data['processor_response']) && \is_array($data['processor_response']) ? $data['processor_response'] : null,
            description: $data['description'] ?? null,
            createdDatetime: $data['created_datetime'] ?? null,
        );
    }

    /**
     * Create collection from API response.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return OrderData[]
     */
    public static function collection(array $items): array
    {
        return array_map(fn (array $item) => self::fromApi($item), $items);
    }

    /**
     * Get order status as enum (if valid).
     */
    public function getStatusEnum(): ?OrderStatus
    {
        if ($this->status === null) {
            return null;
        }

        return OrderStatus::tryFrom($this->status);
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if order is authorized (awaiting capture).
     */
    public function isAuthorized(): bool
    {
        return $this->status === 'authorized';
    }

    /**
     * Check if order is voided.
     */
    public function isVoided(): bool
    {
        return $this->status === 'voided';
    }

    /**
     * Check if order failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if order requires a next action (e.g., redirect).
     */
    public function requiresAction(): bool
    {
        return $this->nextAction !== null && ! empty($this->nextAction);
    }

    /**
     * Get redirect URL from next action if available.
     */
    public function getNextActionRedirectUrl(): ?string
    {
        if (! $this->requiresAction()) {
            return null;
        }

        return $this->nextAction['redirect_url']['url'] ?? null;
    }

    /**
     * Convert to array for general serialization.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'customer_id' => $this->customerId,
            'status' => $this->status,
            'fees' => $this->fees,
            'billing_details' => $this->billingDetails,
            'meta' => $this->meta,
            'next_action' => $this->nextAction,
            'payment_method_details' => $this->paymentMethodDetails,
            'redirect_url' => $this->redirectUrl,
            'processor_response' => $this->processorResponse,
            'description' => $this->description,
            'created_datetime' => $this->createdDatetime,
        ], fn ($value) => $value !== null);
    }
}
