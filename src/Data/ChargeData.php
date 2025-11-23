<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

final readonly class ChargeData
{
    /**
     * @param  array<string, mixed>|null  $billingDetails
     * @param  array<string, mixed>|null  $paymentMethodDetails
     * @param  array<string, mixed>|null  $nextAction
     * @param  array<string, mixed>|null  $fees
     * @param  array<string, mixed>|null  $meta
     * @param  array<string, mixed>|null  $processorResponse
     */
    public function __construct(
        public string $id,
        public string $reference,
        public float $amount,
        public string $currency,
        public string $status,
        public ?string $redirectUrl = null,
        public ?string $customerId = null,
        public ?array $billingDetails = null,
        public ?array $paymentMethodDetails = null,
        public ?array $nextAction = null,
        public ?array $fees = null,
        public ?array $meta = null,
        public ?string $description = null,
        public bool $disputed = false,
        public bool $settled = false,
        public bool $refunded = false,
        public ?array $settlementId = null,
        public ?array $processorResponse = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * Create from API response
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        $billingDetails = null;
        if (isset($data['billing_details']) && \is_array($data['billing_details'])) {
            $billingDetails = $data['billing_details'];
        }

        $paymentMethodDetails = null;
        if (isset($data['payment_method_details']) && \is_array($data['payment_method_details'])) {
            $paymentMethodDetails = $data['payment_method_details'];
        }

        $nextAction = null;
        if (isset($data['next_action']) && \is_array($data['next_action'])) {
            $nextAction = $data['next_action'];
        }

        $fees = null;
        if (isset($data['fees']) && \is_array($data['fees'])) {
            $fees = $data['fees'];
        }

        $meta = null;
        if (isset($data['meta']) && \is_array($data['meta'])) {
            $meta = $data['meta'];
        }

        $processorResponse = null;
        if (isset($data['processor_response'])) {
            if (\is_array($data['processor_response'])) {
                $processorResponse = $data['processor_response'];
            } elseif (\is_string($data['processor_response'])) {
                $processorResponse = ['message' => $data['processor_response']];
            }
        }

        $settlementId = null;
        if (isset($data['settlement_id'])) {
            if (\is_array($data['settlement_id'])) {
                $settlementId = $data['settlement_id'];
            } elseif (\is_string($data['settlement_id'])) {
                $settlementId = [$data['settlement_id']];
            }
        }

        // Extract redirect_url from next_action if not directly available
        $redirectUrl = $data['redirect_url'] ?? null;
        if ($redirectUrl === null && isset($nextAction['redirect_url']['url'])) {
            $redirectUrl = $nextAction['redirect_url']['url'];
        }

        return new self(
            id: (string) $data['id'],
            reference: $data['reference'] ?? '',
            amount: (float) ($data['amount'] ?? 0.0),
            currency: $data['currency'] ?? '',
            status: $data['status'] ?? 'unknown',
            redirectUrl: $redirectUrl,
            customerId: isset($data['customer_id']) ? (string) $data['customer_id'] : null,
            billingDetails: $billingDetails,
            paymentMethodDetails: $paymentMethodDetails,
            nextAction: $nextAction,
            fees: $fees,
            meta: $meta,
            description: $data['description'] ?? null,
            disputed: (bool) ($data['disputed'] ?? false),
            settled: (bool) ($data['settled'] ?? false),
            refunded: (bool) ($data['refunded'] ?? false),
            settlementId: $settlementId,
            processorResponse: $processorResponse,
            createdAt: $data['created_datetime'] ?? $data['created_at'] ?? null,
        );
    }

    /**
     * Check if charge is successful
     */
    public function isSuccessful(): bool
    {
        return \in_array(mb_strtolower($this->status), ['succeeded', 'successful', 'success', 'completed'], true);
    }

    /**
     * Check if charge is pending
     */
    public function isPending(): bool
    {
        return \in_array(mb_strtolower($this->status), ['pending', 'processing', 'initiated'], true);
    }

    /**
     * Check if charge failed
     */
    public function isFailed(): bool
    {
        return \in_array(mb_strtolower($this->status), ['failed', 'cancelled', 'expired'], true);
    }

    /**
     * Get customer email if available
     */
    public function getCustomerEmail(): ?string
    {
        if ($this->billingDetails === null) {
            return null;
        }

        return $this->billingDetails['email'] ?? null;
    }

    /**
     * Get customer name if available
     */
    public function getCustomerName(): ?string
    {
        if ($this->billingDetails === null) {
            return null;
        }

        if (isset($this->billingDetails['name'])) {
            if (\is_string($this->billingDetails['name'])) {
                return $this->billingDetails['name'];
            }

            if (\is_array($this->billingDetails['name'])) {
                $parts = array_filter([
                    $this->billingDetails['name']['first'] ?? '',
                    $this->billingDetails['name']['middle'] ?? null,
                    $this->billingDetails['name']['last'] ?? '',
                ]);

                return implode(' ', $parts) ?: null;
            }
        }

        return null;
    }

    /**
     * Get redirect URL from next_action if available
     */
    public function getRedirectUrl(): ?string
    {
        if ($this->redirectUrl !== null) {
            return $this->redirectUrl;
        }

        if ($this->nextAction !== null && isset($this->nextAction['redirect_url']['url'])) {
            return $this->nextAction['redirect_url']['url'];
        }

        return null;
    }

    /**
     * Get payment method type if available
     */
    public function getPaymentMethodType(): ?string
    {
        if ($this->paymentMethodDetails === null) {
            return null;
        }

        return $this->paymentMethodDetails['type'] ?? null;
    }

    /**
     * Get metadata value by key
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        if ($this->meta === null) {
            return $default;
        }

        return $this->meta[$key] ?? $default;
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
            'reference' => $this->reference,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'redirect_url' => $this->getRedirectUrl(),
            'customer_id' => $this->customerId,
            'billing_details' => $this->billingDetails,
            'payment_method_details' => $this->paymentMethodDetails,
            'next_action' => $this->nextAction,
            'fees' => $this->fees,
            'meta' => $this->meta,
            'description' => $this->description,
            'disputed' => $this->disputed,
            'settled' => $this->settled,
            'refunded' => $this->refunded,
            'settlement_id' => $this->settlementId,
            'processor_response' => $this->processorResponse,
            'created_datetime' => $this->createdAt,
        ];
    }
}
