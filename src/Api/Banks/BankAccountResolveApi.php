<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Api\Banks;

use Gowelle\Flutterwave\Data\ApiResponse;
use Gowelle\Flutterwave\Data\Banks\BankAccountResolveRequest;
use Gowelle\Flutterwave\FlutterwaveBaseApi;
use Illuminate\Support\Facades\Validator;

class BankAccountResolveApi extends FlutterwaveBaseApi
{
    /**
     * The endpoint for the bank account resolve API
     */
    protected string $endpoint = '/banks/account-resolve';

    /**
     * Resolve NGN bank account details.
     */
    public function resolve(string $bankCode, string $accountNumber): ApiResponse
    {
        return $this->resolveFromDto(BankAccountResolveRequest::forNgn($bankCode, $accountNumber));
    }

    /**
     * Resolve USD bank account details for Nigerian bank accounts.
     */
    public function resolveUsdNg(string $bankCode, string $accountNumber): ApiResponse
    {
        return $this->resolveFromDto(BankAccountResolveRequest::forUsdNg($bankCode, $accountNumber));
    }

    /**
     * Resolve GBP corporate bank account details.
     */
    public function resolveGbpCorporate(string $bankCode, string $accountNumber, string $businessName): ApiResponse
    {
        return $this->resolveFromDto(
            BankAccountResolveRequest::forGbpCorporate($bankCode, $accountNumber, $businessName)
        );
    }

    /**
     * Resolve GBP individual bank account details.
     */
    public function resolveGbpIndividual(
        string $bankCode,
        string $accountNumber,
        string $firstName,
        string $lastName,
        ?string $middleName = null,
    ): ApiResponse {
        return $this->resolveFromDto(BankAccountResolveRequest::forGbpIndividual(
            $bankCode,
            $accountNumber,
            $firstName,
            $lastName,
            $middleName,
        ));
    }

    /**
     * Resolve bank account details from DTO
     */
    public function resolveFromDto(BankAccountResolveRequest $request): ApiResponse
    {
        $payload = $request->toApiPayload();
        $validatedData = $this->validateResolveData($payload);

        return $this->postToUrl($this->getBaseApiUrl().$this->endpoint, $validatedData);
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function create(array $data): ApiResponse
    {
        $this->notImplemented('create');
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function update(string $id, array $data): ApiResponse
    {
        $this->notImplemented('update');
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function retrieve(string $id): ApiResponse
    {
        $this->notImplemented('retrieve');
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function list(): ApiResponse
    {
        $this->notImplemented('list');
    }

    /**
     * Not implemented for bank account resolve API
     *
     * @throws Exception
     */
    public function search(array $data): ApiResponse
    {
        $this->notImplemented('search');
    }

    /**
     * Validate bank account resolve data
     *
     * @param  array{account: array<string, mixed>}  $data
     * @return array{account: array<string, mixed>}
     */
    protected function validateResolveData(array $data): array
    {
        $validator = Validator::make($data, [
            'account' => 'required|array|min:1',
            'account.code' => 'required|string',
            'account.number' => 'required|string',
        ]);

        return $validator->validate();
    }
}
