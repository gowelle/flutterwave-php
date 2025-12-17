<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave;

use Exception;
use Gowelle\Flutterwave\Api\Banks\BankAccountResolveApi;
use Gowelle\Flutterwave\Api\Banks\BankBranchesApi;
use Gowelle\Flutterwave\Api\Banks\BanksApi;
use Gowelle\Flutterwave\Api\Banks\MobileNetworksApi;
use Gowelle\Flutterwave\Api\Charge\ChargeApi;
use Gowelle\Flutterwave\Api\Charge\DirectChargeApi;
use Gowelle\Flutterwave\Api\Customer\CustomerApi;
use Gowelle\Flutterwave\Api\Order\OrderApi;
use Gowelle\Flutterwave\Api\PaymentMethods\PaymentMethodsApi;
use Gowelle\Flutterwave\Api\Refund\RefundApi;
use Gowelle\Flutterwave\Api\Settlement\SettlementApi;
use Gowelle\Flutterwave\Api\Transfer\DirectTransferApi;
use Gowelle\Flutterwave\Api\Transfer\RateApi;
use Gowelle\Flutterwave\Api\Transfer\RecipientApi;
use Gowelle\Flutterwave\Api\Transfer\SenderApi;
use Gowelle\Flutterwave\Api\Transfer\TransferApi;
use Gowelle\Flutterwave\Api\VirtualAccount\VirtualAccountApi;
use Gowelle\Flutterwave\Api\Wallets\WalletAccountResolveApi;
use Gowelle\Flutterwave\Api\Wallets\WalletBalanceApi;
use Gowelle\Flutterwave\Api\Wallets\WalletBalancesApi;
use Gowelle\Flutterwave\Api\Wallets\WalletStatementApi;
use Gowelle\Flutterwave\Concerns\RecognizesEnvironment;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApiContract;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FlutterwaveApiProvider
{
    use RecognizesEnvironment;

    public function __construct(
        private readonly RetryHandler $retryHandler,
        private readonly RateLimiter $rateLimiter,
    ) {}

    /**
     * Use the API
     *
     * @param  string  $accessToken
     */
    public function useApi(FlutterwaveApi $api, $accessToken, array $headers = []): FlutterwaveApiContract
    {
        try {

            $normalizedHeaders = Validator::validate($headers, [
                'Content-Type' => 'required|string',
                'X-Idempotency-Key' => 'required|string',
                'X-Trace-Id' => 'required|string',
                'X-Scenario-Key' => 'nullable|string',
            ]);

            $headersConfig = AbstractHeadersConfig::fromArray($normalizedHeaders);

            return match ($api) {
                FlutterwaveApi::CUSTOMER => new CustomerApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::CHARGE => new ChargeApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::DIRECT_CHARGE => new DirectChargeApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::PAYMENT_METHODS => new PaymentMethodsApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::BANKS => new BanksApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::BANK_BRANCHES => new BankBranchesApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::BANK_ACCOUNT_RESOLVE => new BankAccountResolveApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::MOBILE_NETWORKS => new MobileNetworksApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::ORDER => new OrderApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::DIRECT_ORDER => new OrderApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::REFUND => new RefundApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::TRANSFER => new TransferApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::DIRECT_TRANSFER => new DirectTransferApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::TRANSFER_RECIPIENTS => new RecipientApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::TRANSFER_SENDERS => new SenderApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::TRANSFER_RATES => new RateApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::SETTLEMENT => new SettlementApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::VIRTUAL_ACCOUNT => new VirtualAccountApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::WALLET_ACCOUNT_RESOLVE => new WalletAccountResolveApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::WALLET_STATEMENT => new WalletStatementApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::WALLET_BALANCE => new WalletBalanceApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::WALLET_BALANCES => new WalletBalancesApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
            };

        } catch (ValidationException $e) {
            throw new Exception("Invalid API headers configuration: {$e->getMessage()}", 0, $e);
        } catch (Exception $e) {
            throw new Exception("Failed to initialize Flutterwave API: {$e->getMessage()}", 0, $e);
        }

    }
}
