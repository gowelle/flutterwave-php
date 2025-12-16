<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Events;

use Gowelle\Flutterwave\Data\AuthorizationData;
use Gowelle\Flutterwave\Data\DirectChargeData;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Flutterwave Charge Updated Event
 *
 * Dispatched when a direct charge authorization is submitted via the
 * FlutterwaveDirectChargeService. Applications can listen to this event
 * to update ChargeSession records or perform other business logic.
 */
class FlutterwaveChargeUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly DirectChargeData $chargeData,
        public readonly AuthorizationData $authorizationData,
    ) {}
}
