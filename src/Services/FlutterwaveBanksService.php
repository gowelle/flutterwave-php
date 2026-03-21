<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Api\Banks\BankAccountResolveApi;
use Gowelle\Flutterwave\Api\Banks\BankBranchesApi;
use Gowelle\Flutterwave\Api\Banks\BanksApi;
use Gowelle\Flutterwave\Api\VirtualAccount\VirtualAccountApi;
use Gowelle\Flutterwave\Data\BankAccountResolveData;
use Gowelle\Flutterwave\Data\BankBranchData;
use Gowelle\Flutterwave\Data\BankData;
use Gowelle\Flutterwave\Data\Banks\BankAccountResolveRequest;
use Gowelle\Flutterwave\Data\VirtualAccount\CreateVirtualAccountRequestDTO;
use Gowelle\Flutterwave\Data\VirtualAccount\ListVirtualAccountsParamsDTO;
use Gowelle\Flutterwave\Data\VirtualAccount\UpdateVirtualAccountRequestDTO;
use Gowelle\Flutterwave\Data\VirtualAccount\VirtualAccountData;
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

        /** @var BanksApi $api */
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

        /** @var BankBranchesApi $api */
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

        /** @var BankAccountResolveApi $api */
        $response = $api->resolve($bankCode, $accountNumber, $currency);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to resolve bank account: '.($response->message ?? 'Unknown error'));
        }

        return BankAccountResolveData::fromApiResponse($response->data);
    }

    /**
     * Resolve bank account details from DTO
     *
     * Type-safe alternative to resolveAccount() using BankAccountResolveRequest DTO.
     *
     * @throws FlutterwaveApiException
     */
    public function resolveFromDto(BankAccountResolveRequest $request): BankAccountResolveData
    {
        $payload = $request->toApiPayload();

        return $this->resolveAccount(
            $payload['bank_code'],
            $payload['account_number'],
            $payload['currency'],
        );
    }

    /**
     * Create a virtual account
     *
     * @throws FlutterwaveApiException
     */
    public function createVirtualAccount(CreateVirtualAccountRequestDTO $request): VirtualAccountData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var VirtualAccountApi $api */
        $response = $api->create($request->toArray());

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to create virtual account: '.($response->message ?? 'Unknown error'));
        }

        return VirtualAccountData::fromApi($response->data);
    }

    /**
     * Retrieve a virtual account by ID
     *
     * @throws FlutterwaveApiException
     */
    public function retrieveVirtualAccount(string $id): VirtualAccountData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var VirtualAccountApi $api */
        $response = $api->retrieve($id);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to retrieve virtual account: '.($response->message ?? 'Unknown error'));
        }

        return VirtualAccountData::fromApi($response->data);
    }

    /**
     * List all virtual accounts
     *
     * @return VirtualAccountData[]
     *
     * @throws FlutterwaveApiException
     */
    public function listVirtualAccounts(): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var VirtualAccountApi $api */
        $response = $api->list();

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to list virtual accounts: '.($response->message ?? 'Unknown error'));
        }

        return VirtualAccountData::collection($response->data);
    }

    /**
     * List virtual accounts with query parameters
     *
     * @return VirtualAccountData[]
     *
     * @throws FlutterwaveApiException
     */
    public function listVirtualAccountsWithParams(ListVirtualAccountsParamsDTO $params): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var VirtualAccountApi $api */
        $response = $api->listWithParams($params->toArray());

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to list virtual accounts: '.($response->message ?? 'Unknown error'));
        }

        return VirtualAccountData::collection($response->data);
    }

    /**
     * Update a virtual account
     *
     * @throws FlutterwaveApiException
     */
    public function updateVirtualAccount(string $id, UpdateVirtualAccountRequestDTO $request): VirtualAccountData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->flutterwaveBaseService->getHeaderBuilder()->build());

        /** @var VirtualAccountApi $api */
        $response = $api->update($id, $request->toArray());

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to update virtual account: '.($response->message ?? 'Unknown error'));
        }

        return VirtualAccountData::fromApi($response->data);
    }
}
