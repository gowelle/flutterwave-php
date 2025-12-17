<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data;

use Gowelle\Flutterwave\Enums\DirectChargeStatus;

/**
 * Direct charge data from Flutterwave orchestrator API.
 *
 * Represents a charge created via the /orchestration/direct-charges endpoint,
 * which combines customer, payment method, and charge creation in one request.
 */
final readonly class DirectChargeData
{
    /**
     * @param  array<string, mixed>|null  $customer
     * @param  array<string, mixed>|null  $billingDetails
     * @param  array<string, mixed>|null  $paymentMethodDetails
     * @param  array<string, mixed>|null  $issuerResponse
     * @param  array<string, mixed>|null  $meta
     */
    public function __construct(
        public string $id,
        public float $amount,
        public string $currency,
        public string $reference,
        public DirectChargeStatus $status,
        public NextActionData $nextAction,
        public ?string $customerId = null,
        public ?array $customer = null,
        public ?array $billingDetails = null,
        public ?string $redirectUrl = null,
        public ?array $paymentMethodDetails = null,
        public ?array $issuerResponse = null,
        public ?array $meta = null,
        public ?float $fees = null,
        public ?string $description = null,
        public ?bool $disputed = null,
        public ?bool $settled = null,
        public ?string $settlementId = null,
        public ?string $createdAt = null,
    ) {}

    /**
     * Create from API response
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromApi(array $data): self
    {
        // Parse next_action
        $nextAction = NextActionData::fromApi($data['next_action'] ?? null);

        // Parse status
        $status = DirectChargeStatus::fromApiResponse($data['status'] ?? 'failed');

        // Extract customer data
        $customer = null;
        if (isset($data['customer']) && \is_array($data['customer'])) {
            $customer = $data['customer'];
        }

        // Extract billing details
        $billingDetails = null;
        if (isset($data['billing_details']) && \is_array($data['billing_details'])) {
            $billingDetails = $data['billing_details'];
        }

        // Extract payment method details
        $paymentMethodDetails = null;
        if (isset($data['payment_method_details']) && \is_array($data['payment_method_details'])) {
            $paymentMethodDetails = $data['payment_method_details'];
        }

        // Extract issuer response
        $issuerResponse = null;
        if (isset($data['issuer_response']) && \is_array($data['issuer_response'])) {
            $issuerResponse = $data['issuer_response'];
        }

        // Extract meta
        $meta = null;
        if (isset($data['meta']) && \is_array($data['meta'])) {
            $meta = $data['meta'];
        }

        // Extract redirect URL from next_action or top level
        $redirectUrl = $data['redirect_url'] ?? $nextAction->getRedirectUrl();

        return new self(
            id: (string) $data['id'],
            amount: (float) ($data['amount'] ?? 0.0),
            currency: $data['currency'] ?? 'USD',
            reference: $data['reference'] ?? '',
            status: $status,
            nextAction: $nextAction,
            customerId: isset($data['customer']) && \is_string($data['customer']) ? $data['customer'] : null,
            customer: $customer,
            billingDetails: $billingDetails,
            redirectUrl: $redirectUrl,
            paymentMethodDetails: $paymentMethodDetails,
            issuerResponse: $issuerResponse,
            meta: $meta,
            fees: isset($data['fees']) ? (float) $data['fees'] : null,
            description: $data['description'] ?? null,
            disputed: isset($data['disputed']) ? (bool) $data['disputed'] : null,
            settled: isset($data['settled']) ? (bool) $data['settled'] : null,
            settlementId: $data['settlement_id'] ?? null,
            createdAt: $data['created_at'] ?? $data['created_datetime'] ?? null,
        );
    }

    /**
     * Check if charge is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status->isSuccessful();
    }

    /**
     * Check if charge requires action
     */
    public function requiresAction(): bool
    {
        return $this->status->requiresAction() || $this->nextAction->requiresCustomerInput();
    }

    /**
     * Check if charge is in terminal state
     */
    public function isTerminal(): bool
    {
        return $this->status->isTerminal();
    }

    /**
     * Get redirect URL if available
     */
    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl ?? $this->nextAction->getRedirectUrl();
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
     * Get payment method ID if available
     */
    public function getPaymentMethodId(): ?string
    {
        if ($this->paymentMethodDetails === null) {
            return null;
        }

        return $this->paymentMethodDetails['id'] ?? null;
    }

    /**
     * Get customer email if available
     */
    public function getCustomerEmail(): ?string
    {
        if ($this->customer === null) {
            return null;
        }

        return $this->customer['email'] ?? null;
    }

    /**
     * Get issuer response code if available
     */
    public function getIssuerResponseCode(): ?string
    {
        if ($this->issuerResponse === null) {
            return null;
        }

        return $this->issuerResponse['code'] ?? null;
    }

    /**
     * Get issuer response message if available
     */
    public function getIssuerResponseMessage(): ?string
    {
        if ($this->issuerResponse === null) {
            return null;
        }

        return $this->issuerResponse['message'] ?? null;
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
     * Check if charge is disputed
     */
    public function isDisputed(): bool
    {
        return $this->disputed === true;
    }

    /**
     * Check if charge is settled
     */
    public function isSettled(): bool
    {
        return $this->settled === true;
    }

    /**
     * Get billing email if available
     */
    public function getBillingEmail(): ?string
    {
        if ($this->billingDetails === null) {
            return null;
        }

        return $this->billingDetails['email'] ?? null;
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
            'amount' => $this->amount,
            'currency' => $this->currency,
            'reference' => $this->reference,
            'status' => $this->status->value,
            'next_action' => $this->nextAction->toArray(),
            'customer_id' => $this->customerId,
            'customer' => $this->customer,
            'billing_details' => $this->billingDetails,
            'redirect_url' => $this->redirectUrl,
            'payment_method_details' => $this->paymentMethodDetails,
            'issuer_response' => $this->issuerResponse,
            'meta' => $this->meta,
            'fees' => $this->fees,
            'description' => $this->description,
            'disputed' => $this->disputed,
            'settled' => $this->settled,
            'settlement_id' => $this->settlementId,
            'created_at' => $this->createdAt,
        ];
    }
}
