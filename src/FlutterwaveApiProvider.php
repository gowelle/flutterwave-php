<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave;

use Exception;
use Gowelle\Flutterwave\Api\Banks\BankAccountResolveApi;
use Gowelle\Flutterwave\Api\Banks\BankBranchesApi;
use Gowelle\Flutterwave\Api\Banks\BanksApi;
use Gowelle\Flutterwave\Api\Banks\MobileNetworksApi;
use Gowelle\Flutterwave\Api\Charge\ChargeApi;
use Gowelle\Flutterwave\Api\Customer\CustomerApi;
use Gowelle\Flutterwave\Api\Order\OrderApi;
use Gowelle\Flutterwave\Api\PaymentMethods\PaymentMethodsApi;
use Gowelle\Flutterwave\Api\Refund\RefundApi;
use Gowelle\Flutterwave\Api\Transfer\TransferApi;
use Gowelle\Flutterwave\Api\Settlement\SettlementApi;
use Gowelle\Flutterwave\Concerns\RecognizesEnvironment;
use Gowelle\Flutterwave\Credentials\AbstractHeadersConfig;
use Gowelle\Flutterwave\Exceptions\InvalidApiException;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApiContract;
use Gowelle\Flutterwave\Support\RateLimiter;
use Gowelle\Flutterwave\Support\RetryHandler;
use Illuminate\Support\Facades\Validator;

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
                FlutterwaveApi::PAYMENT_METHODS => new PaymentMethodsApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::BANKS => new BanksApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::BANK_BRANCHES => new BankBranchesApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::BANK_ACCOUNT_RESOLVE => new BankAccountResolveApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::MOBILE_NETWORKS => new MobileNetworksApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::ORDER => new OrderApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::REFUND => new RefundApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::TRANSFER => new TransferApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                FlutterwaveApi::SETTLEMENT => new SettlementApi($headersConfig, $accessToken, $this->retryHandler, $this->rateLimiter),
                default => throw new InvalidApiException('Invalid API'),
            };

        } catch (Exception $e) {
            throw new Exception('Invalid headers: '.$e->getMessage());
        }

    }
}
