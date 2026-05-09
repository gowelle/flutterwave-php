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
use Illuminate\Support\Str;

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
            ->useApi(FlutterwaveApi::BANKS, $this->flutterwaveBaseService->getAccessToken(), $this->buildBankHeaders());

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
            ->useApi(FlutterwaveApi::BANK_BRANCHES, $this->flutterwaveBaseService->getAccessToken(), $this->buildBankHeaders());

        /** @var BankBranchesApi $api */
        $response = $api->retrieveByBankId($bankId);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to retrieve bank branches: '.($response->message ?? 'Unknown error'));
        }

        return BankBranchData::collection($response->data);
    }

    /**
     * Resolve NGN bank account details.
     *
     * @throws FlutterwaveApiException
     */
    public function resolveAccount(string $bankCode, string $accountNumber, ?string $scenarioKey = null): BankAccountResolveData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::BANK_ACCOUNT_RESOLVE, $this->flutterwaveBaseService->getAccessToken(), $this->buildResolveHeaders($scenarioKey));

        /** @var BankAccountResolveApi $api */
        $response = $api->resolve($bankCode, $accountNumber);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to resolve bank account: '.($response->message ?? 'Unknown error'));
        }

        return BankAccountResolveData::fromApiResponse($response->data);
    }

    /**
     * Resolve USD bank account details for Nigerian bank accounts.
     *
     * @throws FlutterwaveApiException
     */
    public function resolveUsdNgAccount(string $bankCode, string $accountNumber, ?string $scenarioKey = null): BankAccountResolveData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::BANK_ACCOUNT_RESOLVE, $this->flutterwaveBaseService->getAccessToken(), $this->buildResolveHeaders($scenarioKey));

        /** @var BankAccountResolveApi $api */
        $response = $api->resolveUsdNg($bankCode, $accountNumber);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to resolve bank account: '.($response->message ?? 'Unknown error'));
        }

        return BankAccountResolveData::fromApiResponse($response->data);
    }

    /**
     * Resolve GBP corporate bank account details.
     *
     * @throws FlutterwaveApiException
     */
    public function resolveGbpCorporateAccount(
        string $bankCode,
        string $accountNumber,
        string $businessName,
        ?string $scenarioKey = null,
    ): BankAccountResolveData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::BANK_ACCOUNT_RESOLVE, $this->flutterwaveBaseService->getAccessToken(), $this->buildResolveHeaders($scenarioKey));

        /** @var BankAccountResolveApi $api */
        $response = $api->resolveGbpCorporate($bankCode, $accountNumber, $businessName);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to resolve bank account: '.($response->message ?? 'Unknown error'));
        }

        return BankAccountResolveData::fromApiResponse($response->data);
    }

    /**
     * Resolve GBP individual bank account details.
     *
     * @throws FlutterwaveApiException
     */
    public function resolveGbpIndividualAccount(
        string $bankCode,
        string $accountNumber,
        string $firstName,
        string $lastName,
        ?string $middleName = null,
        ?string $scenarioKey = null,
    ): BankAccountResolveData {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::BANK_ACCOUNT_RESOLVE, $this->flutterwaveBaseService->getAccessToken(), $this->buildResolveHeaders($scenarioKey));

        /** @var BankAccountResolveApi $api */
        $response = $api->resolveGbpIndividual($bankCode, $accountNumber, $firstName, $lastName, $middleName);

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
        return $this->resolve($request);
    }

    /**
     * Resolve bank account details from a currency-aware DTO payload.
     *
     * @throws FlutterwaveApiException
     */
    public function resolve(BankAccountResolveRequest $request): BankAccountResolveData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::BANK_ACCOUNT_RESOLVE, $this->flutterwaveBaseService->getAccessToken(), $this->buildResolveHeaders());

        /** @var BankAccountResolveApi $api */
        $response = $api->resolveFromDto($request);

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
    public function createVirtualAccount(CreateVirtualAccountRequestDTO $request, ?string $scenarioKey = null): VirtualAccountData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::VIRTUAL_ACCOUNT, $this->flutterwaveBaseService->getAccessToken(), $this->buildVirtualAccountHeaders($scenarioKey));

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

    /**
     * Build safe headers for account lookup operations.
     */
    private function buildBankHeaders(): array
    {
        return $this->flutterwaveBaseService->getHeaderBuilder()->fromArray([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => Str::uuid()->toString(),
        ]);
    }

    /**
     * Build headers for bank account resolve operations.
     */
    private function buildResolveHeaders(?string $scenarioKey = null): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Trace-Id' => Str::uuid()->toString(),
        ];

        if ($scenarioKey !== null) {
            $headers['X-Scenario-Key'] = $scenarioKey;
        }

        return $this->flutterwaveBaseService->getHeaderBuilder()->fromArray($headers);
    }

    /**
     * Build headers for virtual account creation.
     */
    private function buildVirtualAccountHeaders(?string $scenarioKey = null): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Idempotency-Key' => Str::uuid()->toString(),
            'X-Trace-Id' => Str::uuid()->toString(),
        ];

        if ($scenarioKey !== null) {
            $headers['X-Scenario-Key'] = $scenarioKey;
        }

        return $this->flutterwaveBaseService->getHeaderBuilder()->fromArray($headers);
    }
}
