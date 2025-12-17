<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Infrastructure;

use Illuminate\Support\Facades\Validator;

enum PaymentMethodType: string
{
    case CARD = 'card';
    case BANK_ACCOUNT = 'bank_account';
    case MOBILE_MONEY = 'mobile_money';
    case OPAY = 'opay';
    case APPLEPAY = 'applepay';
    case GOOGLEPAY = 'googlepay';
    case USSD = 'ussd';

    public function validateCreateData(array $data): array
    {
        return match ($this) {
            self::CARD => $this->validateCardData($data),
            self::BANK_ACCOUNT => $this->validateBankAccountData($data),
            self::MOBILE_MONEY => $this->validateMobileMoneyData($data),
            self::OPAY => $this->validateOpayData($data),
            self::APPLEPAY => $this->validateApplePayData($data),
            self::GOOGLEPAY => $this->validateGooglePayData($data),
            self::USSD => $this->validateUssdData($data),
        };
    }

    /**
     * Validate card data
     *
     * @throws ValidationException
     */
    public function validateCardData(array $data): array
    {
        $validator = Validator::make($data, [
            'customer_id' => 'required|string',
            'type' => 'required|string|in:card',
            'card' => 'required|array',
            'card.nonce' => 'required|string|min:12|max:12',
            'card.encrypted_expiry_month' => 'required|string',
            'card.encrypted_expiry_year' => 'required|string',
            'card.encrypted_card_number' => 'required|string',
            'card.encrypted_cvv' => 'nullable|string',
            'card.billing_address' => 'required|array',
            'card.billing_address.city' => 'required|string',
            'card.billing_address.country' => 'required|string|size:2',
            'card.billing_address.line1' => 'required|string',
            'card.billing_address.line2' => 'nullable|string',
            'card.billing_address.state' => 'required|string',
            'card.billing_address.postal_code' => 'required|string',
        ]);

        return $validator->validate();
    }

    public function validateBankAccountData(array $data): array
    {
        $validator = Validator::make($data, [
            'customer_id' => 'required|string',
            'type' => 'required|string|in:bank_account',
        ]);

        return $validator->validate();
    }

    public function validateMobileMoneyData(array $data): array
    {
        $validator = Validator::make($data, [
            'customer_id' => 'required|string',
            'type' => 'required|string|in:mobile_money',
            'mobile_money.network' => 'required|string',
            'mobile_money.country_code' => 'required|string|max:3',
            'mobile_money.phone_number' => 'required|string',
        ]);

        return $validator->validate();
    }

    public function validateOpayData(array $data): array
    {
        $validator = Validator::make($data, [
            'customer_id' => 'required|string',
            'type' => 'required|string|in:opay',
        ]);

        return $validator->validate();
    }

    public function validateApplePayData(array $data): array
    {
        $validator = Validator::make($data, [
            'customer_id' => 'required|string',
            'type' => 'required|string|in:applepay',
            'applepay.card_holder_name' => 'nullable|string',
        ]);

        return $validator->validate();
    }

    public function validateGooglePayData(array $data): array
    {
        $validator = Validator::make($data, [
            'customer_id' => 'required|string',
            'type' => 'required|string|in:googlepay',
            'googlepay.card_holder_name' => 'nullable|string',
        ]);

        return $validator->validate();
    }

    public function validateUssdData(array $data): array
    {
        $validator = Validator::make($data, [
            'customer_id' => 'required|string',
            'type' => 'required|string|in:ussd',
            'ussd.account_bank' => 'required|string|min:3',
        ]);

        return $validator->validate();
    }
}
