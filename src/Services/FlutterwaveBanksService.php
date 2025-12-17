<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Data\BankAccountResolveData;
use Gowelle\Flutterwave\Data\BankBranchData;
use Gowelle\Flutterwave\Data\BankData;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveBanksService
{
    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Get banks by country
     *
     * @param  string  $country  country code
     * @return BankData[]
     *
     * @throws FlutterwaveApiException
     */
    public function get(string $country): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::BANKS, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\Banks\BanksApi $api */
        $response = $api->retrieveByCountry($country);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to retrieve banks: '.($response->message ?? 'Unknown error'));
        }

        return BankData::collection($response->data);
    }

    /**
     * Get bank branches by bank ID
     *
     * @return BankBranchData[]
     *
     * @throws FlutterwaveApiException
     */
    public function branches(string $bankId): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::BANK_BRANCHES, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\Banks\BankBranchesApi $api */
        $response = $api->retrieveByBankId($bankId);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to retrieve bank branches: '.($response->message ?? 'Unknown error'));
        }

        return BankBranchData::collection($response->data);
    }

    /**
     * Resolve bank account details
     *
     *
     * @throws FlutterwaveApiException
     */
    public function resolveAccount(string $bankCode, string $accountNumber, ?string $currency = null): BankAccountResolveData
    {
        $currency = $currency ?? config('flutterwave.default_currency', 'TZS');

        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::BANK_ACCOUNT_RESOLVE, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\Banks\BankAccountResolveApi $api */
        $response = $api->resolve($bankCode, $accountNumber, $currency);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to resolve bank account: '.($response->message ?? 'Unknown error'));
        }

        return BankAccountResolveData::fromApiResponse($response->data);
    }

    /**
     * Create a virtual account
     *
     * @throws FlutterwaveApiException
     */
    public function createVirtualAccount(\Gowelle\Flutterwave\Data\VirtualAccount\CreateVirtualAccountRequestDTO $request): \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\VirtualAccount\VirtualAccountApi $api */
        $response = $api->create($request->toArray());

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to create virtual account: '.($response->message ?? 'Unknown error'));
        }

        return \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::fromApi($response->data);
    }

    /**
     * Retrieve a virtual account by ID
     *
     * @throws FlutterwaveApiException
     */
    public function retrieveVirtualAccount(string $id): \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\VirtualAccount\VirtualAccountApi $api */
        $response = $api->retrieve($id);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to retrieve virtual account: '.($response->message ?? 'Unknown error'));
        }

        return \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::fromApi($response->data);
    }

    /**
     * List all virtual accounts
     *
     * @return \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData[]
     *
     * @throws FlutterwaveApiException
     */
    public function listVirtualAccounts(): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\VirtualAccount\VirtualAccountApi $api */
        $response = $api->list();

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to list virtual accounts: '.($response->message ?? 'Unknown error'));
        }

        return \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::collection($response->data);
    }

    /**
     * List virtual accounts with query parameters
     *
     * @return \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData[]
     *
     * @throws FlutterwaveApiException
     */
    public function listVirtualAccountsWithParams(\Gowelle\Flutterwave\Data\VirtualAccount\ListVirtualAccountsParamsDTO $params): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\VirtualAccount\VirtualAccountApi $api */
        $response = $api->listWithParams($params->toArray());

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to list virtual accounts: '.($response->message ?? 'Unknown error'));
        }

        return \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::collection($response->data);
    }

    /**
     * Update a virtual account
     *
     * @throws FlutterwaveApiException
     */
    public function updateVirtualAccount(string $id, \Gowelle\Flutterwave\Data\VirtualAccount\UpdateVirtualAccountRequestDTO $request): \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var \Gowelle\Flutterwave\Api\VirtualAccount\VirtualAccountApi $api */
        $response = $api->update($id, $request->toArray());

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to update virtual account: '.($response->message ?? 'Unknown error'));
        }

        return \Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData::fromApi($response->data);
    }
}
