<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\RefundData;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveRefundService
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Create a refund
     *
     * @param  array<string, mixed>  $data  Refund data
     */
    public function create(array $data): RefundData
    {
        $wavable = $this->buildWavable(
            $data,
            FlutterwaveApi::REFUND,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->create(FlutterwaveApi::REFUND, $wavable, $data);

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
     * List all refunds
     *
     * @return RefundData[]
     */
    public function list(): array
    {
        $wavable = $this->buildWavable(
            [],
            FlutterwaveApi::REFUND,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::REFUND, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_map(fn (array $item) => RefundData::fromApi($item), $response->data);
    }
}
