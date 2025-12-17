<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Data\PaymentMethods;

use Gowelle\Flutterwave\Data\PaymentMethodData;
use Gowelle\Flutterwave\Infrastructure\PaymentMethodType;
use InvalidArgumentException;

final class PaymentMethodFactory
{
    /**
     * Create a payment method from API response data based on its type
     *
     * @param  array  $data  API response data containing payment method information
     *
     * @throws InvalidArgumentException
     */
    public static function create(array $data): ?PaymentMethodData
    {
        if (! isset($data['type'])) {
            throw new InvalidArgumentException('Payment method type is required');
        }

        $type = PaymentMethodType::from($data['type']);

        return match ($type) {
            PaymentMethodType::CARD => CardPaymentMethod::fromApi($data),
            PaymentMethodType::BANK_ACCOUNT => BankAccountPaymentMethod::fromApi($data),
            PaymentMethodType::MOBILE_MONEY => MobileMoneyPaymentMethod::fromApi($data),
            PaymentMethodType::OPAY => OpayPaymentMethod::fromApi($data),
            PaymentMethodType::APPLEPAY => ApplePayPaymentMethod::fromApi($data),
            PaymentMethodType::GOOGLEPAY => GooglePayPaymentMethod::fromApi($data),
            PaymentMethodType::USSD => UssdPaymentMethod::fromApi($data),
        };
    }
}
