<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Transfer scenario keys for Flutterwave testing.
 *
 * Use these scenario keys with the X-Scenario-Key header to simulate
 * different transfer outcomes in the staging environment.
 *
 * @see https://developer.flutterwave.com/docs/testing#transfers
 */
enum TransferScenario: string
{
    /**
     * Mock a successful transfer
     */
    case SUCCESSFUL = 'scenario:successful';

    /**
     * Mock a failed transfer due to account resolution failure
     */
    case ACCOUNT_RESOLVED_FAILED = 'scenario:account_resolved_failed';

    /**
     * Mock a failed transfer due to amount being below limit
     */
    case AMOUNT_BELOW_LIMIT_ERROR = 'scenario:amount_below_limit_error';

    /**
     * Mock a failed transfer due to amount exceeding limit
     */
    case AMOUNT_EXCEED_LIMIT_ERROR = 'scenario:amount_exceed_limit_error';

    /**
     * Mock a failed transfer due to bank being blocked
     */
    case BLOCKED_BANK = 'scenario:blocked_bank';

    /**
     * Mock a failed transfer due to currency amount being below limit
     */
    case CURRENCY_AMOUNT_BELOW_LIMIT = 'scenario:currency_amount_below_limit';

    /**
     * Mock a failed transfer due to currency amount exceeding limit
     */
    case CURRENCY_AMOUNT_EXCEED_LIMIT = 'scenario:currency_amount_exceed_limit';

    /**
     * Mock a failed transfer due to daily limit error
     */
    case DAY_LIMIT_ERROR = 'scenario:day_limit_error';

    /**
     * Mock a failed transfer due to daily transfer limit being exceeded
     */
    case DAY_TRANSFER_LIMIT_EXCEEDED = 'scenario:day_transfer_limit_exceeded';

    /**
     * Mock a failed transfer due to transfers being disabled
     */
    case DISABLED_TRANSFER = 'scenario:disabled_transfer';

    /**
     * Mock a failed transfer due to duplicate reference
     */
    case DUPLICATE_REFERENCE = 'scenario:duplicate_reference';

    /**
     * Mock a failed transfer due to file being too large
     */
    case FILE_TOO_LARGE = 'scenario:file_too_large';

    /**
     * Mock a failed transfer due to insufficient balance
     */
    case INSUFFICIENT_BALANCE = 'scenario:insufficient_balance';

    /**
     * Mock a failed transfer due to invalid amount
     */
    case INVALID_AMOUNT = 'scenario:invalid_amount';

    /**
     * Mock a failed transfer due to invalid amount validation
     */
    case INVALID_AMOUNT_VALIDATION = 'scenario:invalid_amount_validation';

    /**
     * Mock a failed transfer due to invalid bulk data
     */
    case INVALID_BULK_DATA = 'scenario:invalid_bulk_data';

    /**
     * Mock a failed transfer due to invalid currency
     */
    case INVALID_CURRENCY = 'scenario:invalid_currency';

    /**
     * Mock a failed transfer due to invalid payouts
     */
    case INVALID_PAYOUTS = 'scenario:invalid_payouts';

    /**
     * Mock a failed transfer due to invalid reference
     */
    case INVALID_REFERENCE = 'scenario:invalid_reference';

    /**
     * Mock a failed transfer due to invalid reference length
     */
    case INVALID_REFERENCE_LENGTH = 'scenario:invalid_reference_length';

    /**
     * Mock a failed transfer due to invalid wallet currency
     */
    case INVALID_WALLET_CURRENCY = 'scenario:invalid_wallet_currency';

    /**
     * Mock a failed transfer due to monthly limit error
     */
    case MONTH_LIMIT_ERROR = 'scenario:month_limit_error';

    /**
     * Mock a failed transfer due to monthly transfer limit being exceeded
     */
    case MONTH_TRANSFER_LIMIT_EXCEEDED = 'scenario:month_transfer_limit_exceeded';

    /**
     * Mock a failed transfer due to no account found
     */
    case NO_ACCOUNT_FOUND = 'scenario:no_account_found';

    /**
     * Mock a failed transfer due to payout creation error
     */
    case PAYOUT_CREATION_ERROR = 'scenario:payout_creation_error';

    /**
     * Mock a failed transfer due to unavailable transfer option
     */
    case UNAVAILABLE_TRANSFER_OPTION = 'scenario:unavailable_transfer_option';

    /**
     * Get the scenario key string value
     */
    public function key(): string
    {
        return $this->value;
    }
}

