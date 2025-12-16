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
 * @see \Gowelle\Flutterwave\Services\FlutterwavePaymentsService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveDirectChargeService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveBanksService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveCustomerService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveMobileNetworkService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveOrderService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveRefundService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveTransferService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveSettlementService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveWebhookService
 * @see \Gowelle\Flutterwave\Services\FlutterwaveBaseService
 */
class Flutterwave extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'flutterwave';
    }
}
