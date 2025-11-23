<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Builders;

final class ChargeRequestBuilder
{
    private array $data = [];

    private function __construct(string $reference)
    {
        $this->data['reference'] = $reference;
    }

    /**
     * Create a new charge request builder
     */
    public static function for(string $reference): self
    {
        return new self($reference);
    }

    /**
     * Set the amount and currency
     */
    public function amount(float $amount, ?string $currency = null): self
    {
        $this->data['amount'] = $amount;
        $this->data['currency'] = $currency ?? config('flutterwave.default_currency', 'TZS');

        return $this;
    }

    /**
     * Set customer information
     */
    public function customer(string $email, string $name, ?string $phoneNumber = null): self
    {
        $this->data['customer'] = [
            'email' => $email,
            'name' => $name,
        ];

        if ($phoneNumber) {
            $this->data['customer']['phone_number'] = $phoneNumber;
        }

        return $this;
    }

    /**
     * Set redirect URL
     */
    public function redirectUrl(string $url): self
    {
        $this->data['redirect_url'] = $url;

        return $this;
    }

    /**
     * Set payment options
     */
    public function paymentOptions(string $options): self
    {
        $this->data['payment_options'] = $options;

        return $this;
    }

    /**
     * Set metadata
     */
    public function meta(array $meta): self
    {
        $this->data['meta'] = $meta;

        return $this;
    }

    /**
     * Set customizations
     */
    public function customizations(string $title, ?string $description = null, ?string $logo = null): self
    {
        $this->data['customizations'] = [
            'title' => $title,
        ];

        if ($description) {
            $this->data['customizations']['description'] = $description;
        }

        if ($logo) {
            $this->data['customizations']['logo'] = $logo;
        }

        return $this;
    }

    /**
     * Build and return the request array
     */
    public function build(): array
    {
        return $this->data;
    }
}
