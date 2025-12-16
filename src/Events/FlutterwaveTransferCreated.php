<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Events;

use Gowelle\Flutterwave\Data\Transfer\TransferData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a transfer is created.
 */
class FlutterwaveTransferCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly TransferData $transfer,
    ) {}
}
