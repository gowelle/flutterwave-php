<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Data\Wallet\WalletAccountResolveData;
use Gowelle\Flutterwave\Data\Wallet\WalletBalanceData;
use Gowelle\Flutterwave\Data\Wallet\WalletStatementData;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveWalletService
{
    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Resolve wallet account details
     *
     * @throws FlutterwaveApiException
     */
    public function resolveAccount(string $provider, string $identifier): WalletAccountResolveData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::WALLET_ACCOUNT_RESOLVE, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\Wallets\WalletAccountResolveApi $api */
        $response = $api->resolve($provider, $identifier);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to resolve wallet account: '.($response->message ?? 'Unknown error'));
        }

        /** @var array<string, mixed> $data */
        $data = \is_array($response->data) ? $response->data : [];

        return WalletAccountResolveData::fromApiResponse($data);
    }

    /**
     * Retrieve wallet statement
     *
     * @param  array<string, mixed>  $params
     *
     * @throws FlutterwaveApiException
     */
    public function getStatement(array $params = []): WalletStatementData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::WALLET_STATEMENT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\Wallets\WalletStatementApi $api */
        $response = $api->getStatement($params);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to retrieve wallet statement: '.($response->message ?? 'Unknown error'));
        }

        /** @var array<string, mixed> $data */
        $data = \is_array($response->data) ? $response->data : [];

        return WalletStatementData::fromApiResponse($data);
    }

    /**
     * Fetch a currency's wallet balance
     *
     * @throws FlutterwaveApiException
     */
    public function getBalance(string $currency): WalletBalanceData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::WALLET_BALANCE, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\Wallets\WalletBalanceApi $api */
        $response = $api->getBalance($currency);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to fetch wallet balance: '.($response->message ?? 'Unknown error'));
        }

        /** @var array<string, mixed> $data */
        $data = \is_array($response->data) ? $response->data : [];

        return WalletBalanceData::fromApiResponse($data);
    }

    /**
     * Fetch wallet balances for multiple currencies
     *
     * @return WalletBalanceData[]
     *
     * @throws FlutterwaveApiException
     */
    public function getBalances(): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::WALLET_BALANCES, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\Wallets\WalletBalancesApi $api */
        $response = $api->getBalances();

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to fetch wallet balances: '.($response->message ?? 'Unknown error'));
        }

        /** @var array<int, array<string, mixed>> $data */
        $data = \is_array($response->data) ? $response->data : [];

        return WalletBalanceData::collection($data);
    }
}
