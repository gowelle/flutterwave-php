<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\SettlementData;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveSettlementService
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Get a settlement by ID
     */
    public function get(string $settlementId): SettlementData
    {
        $wavable = $this->buildWavable(
            ['id' => $settlementId],
            FlutterwaveApi::SETTLEMENT,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::SETTLEMENT, $wavable, $settlementId);

        return SettlementData::fromApi($response->data);
    }

    /**
     * List all settlements
     *
     * @return SettlementData[]
     */
    public function list(): array
    {
        $wavable = $this->buildWavable(
            [],
            FlutterwaveApi::SETTLEMENT,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::SETTLEMENT, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return SettlementData::collection($response->data);
    }
}
