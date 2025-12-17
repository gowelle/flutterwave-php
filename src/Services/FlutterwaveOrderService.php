<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\Order\CreateOrchestratorOrderRequest;
use Gowelle\Flutterwave\Data\Order\CreateOrderRequest;
use Gowelle\Flutterwave\Data\Order\ListOrdersRequest;
use Gowelle\Flutterwave\Data\Order\UpdateOrderRequest;
use Gowelle\Flutterwave\Data\OrderData;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveOrderService
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Create an order using existing customer and payment method IDs.
     *
     * @param  array{amount: float, currency: string, reference: string, customer_id: string, payment_method_id: string, meta?: array, redirect_url?: string, authorization?: array}  $data
     */
    public function create(array $data): OrderData
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return OrderData::fromApi($this->flutterwaveBaseService->create(FlutterwaveApi::ORDER, $wavable, $data)->data);
    }

    /**
     * Create an order from DTO.
     */
    public function createFromDto(CreateOrderRequest $request): OrderData
    {
        return $this->create($request->toApiPayload());
    }

    /**
     * Create an order using the orchestrator endpoint with full customer/payment method objects.
     */
    public function createWithOrchestrator(CreateOrchestratorOrderRequest $request): OrderData
    {
        $data = $request->toApiPayload();
        $wavable = $this->buildWavable($data, FlutterwaveApi::DIRECT_ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());

        $response = $this->flutterwaveBaseService->create(
            FlutterwaveApi::DIRECT_ORDER,
            $wavable,
            $data
        );

        return OrderData::fromApi($response->data);
    }

    /**
     * List all orders.
     *
     * @param  array<string, mixed>  $params  Query parameters
     * @return OrderData[]
     */
    public function list(array $params = []): array
    {
        $wavable = $this->buildWavable($params, FlutterwaveApi::ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());
        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::ORDER, $wavable, $params);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return OrderData::collection($response->data);
    }

    /**
     * List orders with filters using DTO.
     *
     * @return OrderData[]
     */
    public function listWithFilters(ListOrdersRequest $request): array
    {
        return $this->list($request->toQueryParams());
    }

    /**
     * Get a specific order.
     */
    public function get(string $id): OrderData
    {
        $wavable = $this->buildWavable(['id' => $id], FlutterwaveApi::ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return OrderData::fromApi($this->flutterwaveBaseService->retrieve(FlutterwaveApi::ORDER, $wavable, $id)->data);
    }

    /**
     * Update an order.
     *
     * @param  array{meta?: array, action?: string}  $data
     */
    public function update(string $id, array $data): OrderData
    {
        $wavable = $this->buildWavable($data, FlutterwaveApi::ORDER, $this->flutterwaveBaseService->getConfig()->isProduction());

        return OrderData::fromApi($this->flutterwaveBaseService->update(FlutterwaveApi::ORDER, $wavable, $id, $data)->data);
    }

    /**
     * Update an order from DTO.
     */
    public function updateFromDto(string $id, UpdateOrderRequest $request): OrderData
    {
        return $this->update($id, $request->toApiPayload());
    }

    /**
     * Void an order.
     *
     * @param  array<string, mixed>|null  $meta  Optional metadata
     */
    public function void(string $id, ?array $meta = null): OrderData
    {
        return $this->updateFromDto($id, UpdateOrderRequest::void($meta));
    }

    /**
     * Capture an authorized order.
     *
     * @param  array<string, mixed>|null  $meta  Optional metadata
     */
    public function capture(string $id, ?array $meta = null): OrderData
    {
        return $this->updateFromDto($id, UpdateOrderRequest::capture($meta));
    }
}
