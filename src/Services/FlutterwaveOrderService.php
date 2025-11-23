<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\OrderData;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveOrderService
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Create an order
     *
     * @param  array{order_reference: string, amount: float, currency: string, customer: array, items: array, idempotency_key?: string, trace_id?: string, scenario_key?: string}  $data
     */
    public function create(array $data): OrderData
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return OrderData::fromApi($this->flutterwaveBaseService->create(FlutterwaveApi::ORDER, $wavable, $data)->data);
    }

    /**
     * Get all orders
     *
     * @return OrderData[]
     */
    public function list(array $data = []): array
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());
        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::ORDER, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return OrderData::collection($response->data);
    }

    /**
     * Get a specific order
     */
    public function get(string $id): OrderData
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return OrderData::fromApi($this->flutterwaveBaseService->retrieve(FlutterwaveApi::ORDER, $wavable, $id)->data);
    }

    /**
     * Update an order
     *
     * @param  array{id: string, order_reference?: string, amount?: float, status?: string, idempotency_key?: string, trace_id?: string, scenario_key?: string}  $data
     */
    public function update(array $data): OrderData
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return OrderData::fromApi($this->flutterwaveBaseService->update(FlutterwaveApi::ORDER, $wavable, $data['id'], $data)->data);
    }
}
