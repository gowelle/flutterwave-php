<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Events;

use Gowelle\Flutterwave\Data\DirectChargeData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Flutterwave Charge Created Event
 *
 * Dispatched when a direct charge is successfully created via the
 * FlutterwaveDirectChargeService. Applications can listen to this event
 * to create ChargeSession records or perform other business logic.
 */
class FlutterwaveChargeCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly DirectChargeData $chargeData,
        public readonly array $requestData,
    ) {}
}

