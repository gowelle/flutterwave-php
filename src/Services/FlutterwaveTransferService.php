<?php

declare(strict_types=1);

namespace Gowelle\Flutterwave\Services;

use Gowelle\Flutterwave\Concerns\BuildsWavable;
use Gowelle\Flutterwave\Data\TransferData;
use Gowelle\Flutterwave\Infrastructure\FlutterwaveApi;

final class FlutterwaveTransferService
{
    use BuildsWavable;

    public function __construct(private readonly FlutterwaveBaseService $flutterwaveBaseService) {}

    /**
     * Create a transfer
     */
    public function create(array $data): TransferData
    {
        $wavable = $this->buildWavable(
            $data,
            FlutterwaveApi::TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->create(FlutterwaveApi::TRANSFER, $wavable, $data);

        return TransferData::fromApi($response->data);
    }

    /**
     * Get a transfer by ID
     */
    public function get(string $transferId): TransferData
    {
        $wavable = $this->buildWavable(
            ['id' => $transferId],
            FlutterwaveApi::TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->retrieve(FlutterwaveApi::TRANSFER, $wavable, $transferId);

        return TransferData::fromApi($response->data);
    }

    /**
     * List all transfers
     *
     * @return TransferData[]
     */
    public function list(): array
    {
        $wavable = $this->buildWavable(
            [],
            FlutterwaveApi::TRANSFER,
            $this->flutterwaveBaseService->getConfig()->isProduction()
        );

        $response = $this->flutterwaveBaseService->list(FlutterwaveApi::TRANSFER, $wavable);

        if ($response->data === null || ! \is_array($response->data)) {
            return [];
        }

        return array_map(fn (array $item) => TransferData::fromApi($item), $response->data);
    }
}
