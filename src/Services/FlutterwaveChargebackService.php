<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Data\Chargeback\ChargebackData;
use Gowelle\Flutterwave\Data\Chargeback\CreateChargebackRequest;
use Gowelle\Flutterwave\Data\Chargeback\UpdateChargebackRequest;
use Gowelle\Flutterwave\Exceptions\FlutterwaveApiException;
use Gowelle\Flutterwave\FlutterwaveApiProvider;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;
use Illuminate\Support\Str;

final class FlutterwaveChargebackService
{
    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * List chargebacks with optional pagination and date filters.
     *
     * @param  array{page?: int, size?: int, from?: string, to?: string}  $params
     * @return ChargebackData[]
     *
     * @throws FlutterwaveApiException
     */
    public function list(array $params = []): array
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::CHARGEBACK, $this->flutterwaveBaseService->getAccessToken(), $this->buildTraceHeaders());

        $response = $api->listWithParams($params);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to list chargebacks: '.($response->message ?? 'Unknown error'));
        }

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_map(fn (array $item) => ChargebackData::fromApi($item), $response->data);
    }

    /**
     * Create a chargeback.
     *
     * @throws FlutterwaveApiException
     */
    public function create(CreateChargebackRequest $request): ChargebackData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::CHARGEBACK, $this->flutterwaveBaseService->getAccessToken(), $this->buildCreateHeaders());

        $response = $api->createFromDto($request);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to create chargeback: '.($response->message ?? 'Unknown error'));
        }

        return ChargebackData::fromApi($response->data);
    }

    /**
     * Retrieve a chargeback by ID.
     *
     * @throws FlutterwaveApiException
     */
    public function retrieve(string $id): ChargebackData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::CHARGEBACK, $this->flutterwaveBaseService->getAccessToken(), $this->buildTraceHeaders());

        $response = $api->retrieve($id);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to retrieve chargeback: '.($response->message ?? 'Unknown error'));
        }

        return ChargebackData::fromApi($response->data);
    }

    /**
     * Alias of retrieve().
     *
     * @throws FlutterwaveApiException
     */
    public function get(string $id): ChargebackData
    {
        return $this->retrieve($id);
    }

    /**
     * Update a chargeback.
     *
     * @throws FlutterwaveApiException
     */
    public function update(string $id, UpdateChargebackRequest $request): ChargebackData
    {
        $api = app(FlutterwaveApiProvider::class)
            ->useApi(FlutterwaveApi::CHARGEBACK, $this->flutterwaveBaseService->getAccessToken(), $this->buildTraceHeaders());

        $response = $api->updateFromDto($id, $request);

        if (! $response->isSuccessful()) {
            throw new FlutterwaveApiException('Failed to update chargeback: '.($response->message ?? 'Unknown error'));
        }

        return ChargebackData::fromApi($response->data);
    }

    /**
     * Accept a chargeback.
     *
     * @throws FlutterwaveApiException
     */
    public function accept(string $id, ?string $comment = null): ChargebackData
    {
        return $this->update($id, UpdateChargebackRequest::accept($comment));
    }

    /**
     * Decline a chargeback.
     *
     * @throws FlutterwaveApiException
     */
    public function decline(string $id, UpdateChargebackRequest $request): ChargebackData
    {
        if (mb_strtolower($request->status) !== 'declined') {
            throw new \InvalidArgumentException('Decline requests must use a declined status.');
        }

        return $this->update($id, $request);
    }

    /**
     * Build trace-only headers for non-create chargeback operations.
     */
    private function buildTraceHeaders(): array
    {
        return $this->flutterwaveBaseService->getHeaderBuilder()->fromArray([
            'Content-Type' => 'application/json',
            'X-Trace-Id' => Str::uuid()->toString(),
        ]);
    }

    /**
     * Build headers for chargeback creation.
     */
    private function buildCreateHeaders(): array
    {
        return $this->flutterwaveBaseService->getHeaderBuilder()->fromArray([
            'Content-Type' => 'application/json',
            'X-Idempotency-Key' => Str::uuid()->toString(),
            'X-Trace-Id' => Str::uuid()->toString(),
        ]);
    }
}
