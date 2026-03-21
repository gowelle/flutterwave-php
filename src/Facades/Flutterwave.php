<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Facades;

use Gowelle\Flutterwave\Services\FlutterwaveBanksService;
use Gowelle\Flutterwave\Services\FlutterwaveBaseService;
use Gowelle\Flutterwave\Services\FlutterwaveCustomerService;
use Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService;
use Gowelle\Flutterwave\Services\FlutterwaveMobileNetworkService;
use Gowelle\Flutterwave\Services\FlutterwaveOrderService;
use Gowelle\Flutterwave\Services\FlutterwavePaymentsService;
use Gowelle\Flutterwave\Services\FlutterwaveRefundService;
use Gowelle\Flutterwave\Services\FlutterwaveSettlementService;
use Gowelle\Flutterwave\Services\FlutterwaveTransferService;
use Gowelle\Flutterwave\Services\FlutterwaveWebhookService;
use Illuminate\Support\Facades\Facade;

/**
 * Flutterwave Facade
 *
 * @method static FlutterwavePaymentsService payments()
 * @method static FlutterwaveDirectChargeService directCharge()
 * @method static FlutterwaveBanksService banks()
 * @method static FlutterwaveCustomerService customers()
 * @method static FlutterwaveMobileNetworkService mobileNetworks()
 * @method static FlutterwaveOrderService orders()
 * @method static FlutterwaveRefundService refunds()
 * @method static FlutterwaveTransferService transfers()
 * @method static FlutterwaveSettlementService settlements()
 * @method static FlutterwaveWebhookService webhook()
 * @method static FlutterwaveBaseService api()
 *
 * @see FlutterwavePaymentsService
 * @see FlutterwaveDirectChargeService
 * @see FlutterwaveBanksService
 * @see FlutterwaveCustomerService
 * @see FlutterwaveMobileNetworkService
 * @see FlutterwaveOrderService
 * @see FlutterwaveRefundService
 * @see FlutterwaveTransferService
 * @see FlutterwaveSettlementService
 * @see FlutterwaveWebhookService
 * @see FlutterwaveBaseService
 */
class Flutterwave extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flutterwave';
    }
}
