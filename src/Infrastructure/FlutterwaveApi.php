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
    case DIRECT_TRANSFER = 'direct-transfer';
    case TRANSFER_RECIPIENTS = 'transfer-recipients';
    case TRANSFER_SENDERS = 'transfer-senders';
    case TRANSFER_RATES = 'transfer-rates';
    case SETTLEMENT = 'settlement';
    case VIRTUAL_ACCOUNT = 'virtual-account';

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
            self::DIRECT_TRANSFER => '/direct-transfers',
            self::TRANSFER_RECIPIENTS => '/transfers/recipients',
            self::TRANSFER_SENDERS => '/transfers/senders',
            self::TRANSFER_RATES => '/transfers/rates',
            self::SETTLEMENT => '/settlements',
            self::VIRTUAL_ACCOUNT => '/virtual-accounts',
        };
    }
}
