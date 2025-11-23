<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Data\BankAccountResolveData;
use Gowelle\Flutterwave\Data\BankBranchData;
use Gowelle\Flutterwave\Data\BankData;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;

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
     * @return BankAccountResolveData
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
}
