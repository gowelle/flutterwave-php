<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Enums;

/**
 * Transfer action types for Flutterwave transfers.
 *
 * Specifies how the transfer should be processed.
 */
enum TransferAction: string
{
    /**
     * Execute transfer immediately
     */
    case INSTANT = 'instant';

    /**
     * Queue transfer for later processing
     */
    case DEFERRED = 'deferred';

    /**
     * Schedule transfer for a specific time
     */
    case SCHEDULED = 'scheduled';
}
