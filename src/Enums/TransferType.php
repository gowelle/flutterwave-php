<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Transfer type for Flutterwave transfers.
 *
 * Specifies the transfer destination type.
 */
enum TransferType: string
{
    /**
     * Bank account transfer
     */
    case BANK = 'bank';

    /**
     * Mobile money wallet transfer
     */
    case MOBILE_MONEY = 'mobile_money';

    /**
     * Flutterwave wallet-to-wallet transfer
     */
    case WALLET = 'wallet';
}
