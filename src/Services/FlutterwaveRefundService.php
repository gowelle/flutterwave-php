<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\Refund\CreateRefundRequest;
use Gowelle\Flutterwave\Data\Refund\ListRefundsRequest;
use Gowelle\Flutterwave\Data\RefundData;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveRefundService
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Create a refund
     */
    public function create(CreateRefundRequest $request): RefundData
    {
        $wavable = $this->buildWavable(
            $request->toApiPayload(),
            FlutterwaveApi::REFUND,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::REFUND,
            $wavable,
            $request->toApiPayload()
        );

        return RefundData::fromApi($response->data);
    }

    /**
     * Get a refund by ID
     */
    public function get(string $refundId): RefundData
    {
        $wavable = $this->buildWavable(
            ['id' => $refundId],
            FlutterwaveApi::REFUND,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::REFUND, $wavable, $refundId);

        return RefundData::fromApi($response->data);
    }

    /**
     * List all refunds with optional pagination and filtering
     *
     * @return RefundData[]
     */
    public function list(?ListRefundsRequest $request = null): array
    {
        $request = $request ?? new ListRefundsRequest;

        $wavable = $this->buildWavable(
            $request->toQueryParams(),
            FlutterwaveApi::REFUND,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $headers = $this->flutterwaveBaseService->getHeaderBuilder()->build($wavable);

        $api = app(\Gowelle\Flutterwave\FlutterwaveApiProvider::class)->useApi(
            FlutterwaveApi::REFUND,
            $this->flutterwaveBaseService->getAccessToken(),
            $headers
        );

        $response = $api->listWithParams($request->toQueryParams());

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_map(fn (array $item) => RefundData::fromApi($item), $response->data);
    }
}
