<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Infrastructure;

enum FlutterwaveApi: string
{
    case CUSTOMER = 'customer';
    case CHARGE = 'charge';
    case DIRECT_CHARGE = 'direct-charge';
    case PAYMENT_METHODS = 'payment-methods';
    case BANKS = 'banks';
    case BANK_BRANCHES = 'bank-branches';
    case BANK_ACCOUNT_RESOLVE = 'bank-account-resolve';
    case MOBILE_NETWORKS = 'mobile-networks';
    case ORDER = 'order';
    case REFUND = 'refund';
    case TRANSFER = 'transfer';
    case SETTLEMENT = 'settlement';

    public function getEndpoint(): string
    {
        return match ($this) {
            self::CUSTOMER => '/customers',
            self::CHARGE => '/charges',
            self::DIRECT_CHARGE => '/orchestration/direct-charges',
            self::PAYMENT_METHODS => '/payment-methods',
            self::BANKS => '/banks',
            self::BANK_BRANCHES => '/banks',
            self::BANK_ACCOUNT_RESOLVE => '/banks/account-resolve',
            self::MOBILE_NETWORKS => '/mobile-networks',
            self::ORDER => '/orders',
            self::REFUND => '/refunds',
            self::TRANSFER => '/transfers',
            self::SETTLEMENT => '/settlements',
        };
    }
}
